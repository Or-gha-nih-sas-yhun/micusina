package com.micusina.customer.data.remote

import com.micusina.customer.data.model.AuthResponse
import com.micusina.customer.data.model.CartEnvelope
import com.micusina.customer.data.model.CartItemEnvelope
import com.micusina.customer.data.model.CheckoutRequest
import com.micusina.customer.data.model.CheckoutResponse
import com.micusina.customer.data.model.FoodsEnvelope
import com.micusina.customer.data.model.LoginRequest
import com.micusina.customer.data.model.LoginResponse
import com.micusina.customer.data.model.MessageResponse
import com.micusina.customer.data.model.OrdersEnvelope
import com.micusina.customer.data.model.QuantityRequest
import com.micusina.customer.data.model.RegistrationRequest
import com.micusina.customer.data.model.RegistrationStarted
import com.micusina.customer.data.model.ReservationCreated
import com.micusina.customer.data.model.ReservationRequest
import com.micusina.customer.data.model.ReservationsEnvelope
import com.micusina.customer.data.model.UserEnvelope
import com.micusina.customer.data.model.VerifyRegistrationRequest
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.PATCH
import retrofit2.http.POST
import retrofit2.http.Path

/** Customer endpoints of routes/api.php (`/api/mobile`). Staff routes are intentionally absent. */
interface MiCusinaApi {
    /** Returns 202 with `two_factor_required` when an authenticator code is needed. */
    @POST("login")
    suspend fun login(@Body body: LoginRequest): LoginResponse

    @POST("register/send-verification")
    suspend fun sendRegistrationVerification(@Body body: RegistrationRequest): RegistrationStarted

    @POST("register/verify")
    suspend fun verifyRegistration(@Body body: VerifyRegistrationRequest): AuthResponse

    /** [authorization] revokes a specific token instead of the current session's one. */
    @POST("logout")
    suspend fun logout(@Header("Authorization") authorization: String? = null): MessageResponse

    @GET("me")
    suspend fun me(): UserEnvelope

    @GET("foods")
    suspend fun foods(): FoodsEnvelope

    @GET("cart")
    suspend fun cart(): CartEnvelope

    @POST("cart/{foodId}")
    suspend fun addToCart(@Path("foodId") foodId: Int, @Body body: QuantityRequest): CartItemEnvelope

    @PATCH("cart/{cartId}")
    suspend fun updateCart(@Path("cartId") cartId: Int, @Body body: QuantityRequest): CartItemEnvelope

    @DELETE("cart/{cartId}")
    suspend fun removeFromCart(@Path("cartId") cartId: Int): MessageResponse

    @POST("checkout")
    suspend fun checkout(@Body body: CheckoutRequest): CheckoutResponse

    @GET("orders")
    suspend fun orders(): OrdersEnvelope

    /** Cancels the whole checkout group the order belongs to. */
    @DELETE("orders/{orderId}")
    suspend fun cancelOrder(@Path("orderId") orderId: Int): MessageResponse

    @GET("reservations")
    suspend fun reservations(): ReservationsEnvelope

    @POST("reservations")
    suspend fun createReservation(@Body body: ReservationRequest): ReservationCreated

    @DELETE("reservations/{reservationId}")
    suspend fun cancelReservation(@Path("reservationId") reservationId: Int): MessageResponse
}
