package com.micusina.customer.data

import com.micusina.customer.data.model.CartItem
import com.micusina.customer.data.model.CheckoutRequest
import com.micusina.customer.data.model.Food
import com.micusina.customer.data.model.LoginRequest
import com.micusina.customer.data.model.Order
import com.micusina.customer.data.model.QuantityRequest
import com.micusina.customer.data.model.RegistrationRequest
import com.micusina.customer.data.model.RegistrationStarted
import com.micusina.customer.data.model.Reservation
import com.micusina.customer.data.model.ReservationCreated
import com.micusina.customer.data.model.ReservationRequest
import com.micusina.customer.data.model.User
import com.micusina.customer.data.model.VerifyRegistrationRequest
import com.micusina.customer.data.remote.ApiException
import com.micusina.customer.data.remote.AuthTokenHolder
import com.micusina.customer.data.remote.MiCusinaApi
import com.micusina.customer.data.remote.apiCall
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import kotlinx.serialization.json.Json

sealed interface SessionState {
    data object Restoring : SessionState
    data class SignedOut(val notice: String? = null) : SessionState
    data class SignedIn(val user: User) : SessionState
}

sealed interface LoginResult {
    data object Success : LoginResult
    data class TwoFactorRequired(val message: String) : LoginResult
}

class MiCusinaRepository(
    private val api: MiCusinaApi,
    private val json: Json,
    private val tokens: AuthTokenHolder,
    private val store: SessionStore,
    private val scope: CoroutineScope,
    private val deviceName: String,
) {
    private val _session = MutableStateFlow<SessionState>(SessionState.Restoring)
    val session: StateFlow<SessionState> = _session.asStateFlow()

    private val _cart = MutableStateFlow<List<CartItem>>(emptyList())

    /** The signed-in customer's cart, shared by the menu, cart badge and checkout. */
    val cart: StateFlow<List<CartItem>> = _cart.asStateFlow()

    val currentUser: User? get() = (session.value as? SessionState.SignedIn)?.user

    init {
        scope.launch { tokens.expired.collect { endSession(SESSION_EXPIRED) } }
        scope.launch { restore() }
    }

    // ---- Session ----

    private suspend fun restore() {
        val saved = store.read()
        if (saved == null) {
            _session.value = SessionState.SignedOut()
            return
        }
        tokens.token = saved.token
        // Show the cached profile right away so the app opens offline; refresh it below.
        if (saved.user != null && saved.user.isCustomer) {
            _session.value = SessionState.SignedIn(saved.user)
        }
        try {
            val user = call { api.me().user }
            if (!user.isCustomer) {
                endSession(STAFF_ACCOUNT)
                return
            }
            store.saveUser(user)
            _session.value = SessionState.SignedIn(user)
            refreshCartInBackground()
        } catch (e: ApiException) {
            when {
                e.statusCode == 401 -> endSession(SESSION_EXPIRED)
                _session.value !is SessionState.SignedIn -> endSession(e.message)
                else -> refreshCartInBackground()
            }
        }
    }

    suspend fun login(email: String, password: String, twoFactorCode: String?): LoginResult {
        val response = call {
            api.login(LoginRequest(email, password, deviceName, twoFactorCode?.takeIf { it.isNotBlank() }))
        }
        if (response.twoFactorRequired) {
            return LoginResult.TwoFactorRequired(
                response.message ?: "Enter the 6-digit code from your authenticator app.",
            )
        }
        val token = response.token
        val user = response.user
        if (token == null || user == null) throw ApiException(response.message ?: "Unable to sign in. Please try again.")
        if (!user.isCustomer) {
            revokeInBackground(token)
            throw ApiException(STAFF_ACCOUNT)
        }
        startSession(token, user)
        return LoginResult.Success
    }

    suspend fun startRegistration(request: RegistrationRequest): RegistrationStarted =
        call { api.sendRegistrationVerification(request) }

    suspend fun completeRegistration(registrationId: String, emailCode: String) {
        val response = call { api.verifyRegistration(VerifyRegistrationRequest(registrationId, emailCode, deviceName)) }
        startSession(response.token, response.user)
    }

    suspend fun refreshProfile() {
        val user = call { api.me().user }
        store.saveUser(user)
        if (_session.value is SessionState.SignedIn) _session.value = SessionState.SignedIn(user)
    }

    /** Signs out locally right away and revokes the token on the server in the background. */
    suspend fun logout() {
        val token = tokens.token
        endSession(null)
        if (token != null) revokeInBackground(token)
    }

    private suspend fun startSession(token: String, user: User) {
        tokens.token = token
        store.save(token, user)
        _cart.value = emptyList()
        _session.value = SessionState.SignedIn(user)
        refreshCartInBackground()
    }

    private suspend fun endSession(notice: String?) {
        if (tokens.token == null && _session.value is SessionState.SignedOut) return
        tokens.token = null
        store.clear()
        _cart.value = emptyList()
        _session.value = SessionState.SignedOut(notice)
    }

    private fun revokeInBackground(token: String) {
        scope.launch { runCatching { api.logout("Bearer $token") } }
    }

    // ---- Menu & cart ----

    suspend fun foods(): List<Food> = call { api.foods().foods }

    suspend fun refreshCart() {
        _cart.value = call { api.cart().items }
    }

    private fun refreshCartInBackground() {
        scope.launch {
            try {
                refreshCart()
            } catch (_: ApiException) {
                // Screens that need the cart refresh it themselves and show errors.
            }
        }
    }

    suspend fun addToCart(foodId: Int, quantity: Int) {
        val item = call { api.addToCart(foodId, QuantityRequest(quantity)) }.item
        if (item != null) upsertCartItem(item) else refreshCart()
    }

    suspend fun updateCartQuantity(cartId: Int, quantity: Int) {
        val item = call { api.updateCart(cartId, QuantityRequest(quantity)) }.item
        if (item != null) upsertCartItem(item) else refreshCart()
    }

    suspend fun removeFromCart(cartId: Int) {
        call { api.removeFromCart(cartId) }
        _cart.update { items -> items.filterNot { it.id == cartId } }
    }

    private fun upsertCartItem(item: CartItem) {
        _cart.update { items ->
            if (items.any { it.id == item.id }) items.map { if (it.id == item.id) item else it } else listOf(item) + items
        }
    }

    suspend fun checkout(request: CheckoutRequest): List<Order> {
        val orders = call { api.checkout(request) }.orders
        _cart.value = emptyList()
        // The server saves the delivery phone and address to the profile; keep ours in sync.
        try {
            refreshProfile()
        } catch (_: ApiException) {
            // The order went through; a stale profile is harmless.
        }
        return orders
    }

    // ---- Orders ----

    suspend fun orders(): List<Order> = call { api.orders().orders }

    suspend fun cancelOrder(orderId: Int): String =
        call { api.cancelOrder(orderId) }.message ?: "Order cancelled."

    // ---- Reservations ----

    suspend fun reservations(): List<Reservation> = call { api.reservations().reservations }

    suspend fun createReservation(request: ReservationRequest): ReservationCreated =
        call { api.createReservation(request) }

    suspend fun cancelReservation(reservationId: Int): String =
        call { api.cancelReservation(reservationId) }.message ?: "Reservation cancelled."

    private suspend fun <T> call(block: suspend () -> T): T = apiCall(json, block)

    companion object {
        const val SESSION_EXPIRED = "Your session has expired. Please sign in again."
        const val STAFF_ACCOUNT =
            "This app is for Mi Cusina customers. Staff and admin accounts should use the Mi Cusina dashboard."
    }
}
