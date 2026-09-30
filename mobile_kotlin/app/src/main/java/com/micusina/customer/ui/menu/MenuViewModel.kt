package com.micusina.customer.ui.menu

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.SessionState
import com.micusina.customer.data.model.CartItem
import com.micusina.customer.data.model.Food
import com.micusina.customer.data.remote.ApiException
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.receiveAsFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

/** The backend's default category for uncategorised dishes. */
const val UNCATEGORISED = "All menu"

data class MenuUiState(
    val foods: List<Food> = emptyList(),
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val error: String? = null,
    val query: String = "",
    /** null shows every dish. */
    val category: String? = null,
    val addingFoodId: Int? = null,
) {
    val categories: List<String>
        get() = foods.map { it.category }.filter { it.isNotBlank() && it != UNCATEGORISED }.distinct()

    val visibleFoods: List<Food>
        get() {
            val q = query.trim()
            return foods.filter { food ->
                (category == null || food.category == category) &&
                    (q.isEmpty() || food.title.contains(q, ignoreCase = true) ||
                        food.detail.orEmpty().contains(q, ignoreCase = true))
            }
        }
}

sealed interface MenuMessage {
    data class Added(val text: String) : MenuMessage
    data class Failed(val text: String) : MenuMessage
}

class MenuViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    private val _state = MutableStateFlow(MenuUiState())
    val state: StateFlow<MenuUiState> = _state.asStateFlow()

    val cart: StateFlow<List<CartItem>> = repository.cart

    /** Follows the session so the greeting updates when the cached profile is refreshed. */
    val firstName: StateFlow<String> = repository.session
        .map { (it as? SessionState.SignedIn)?.user?.firstName.orEmpty() }
        .stateIn(viewModelScope, SharingStarted.Eagerly, repository.currentUser?.firstName.orEmpty())

    private val _messages = Channel<MenuMessage>(Channel.BUFFERED)
    val messages: Flow<MenuMessage> = _messages.receiveAsFlow()

    init {
        load(refresh = false)
    }

    fun refresh() = load(refresh = true)

    fun setQuery(query: String) = _state.update { it.copy(query = query) }

    fun setCategory(category: String?) = _state.update { it.copy(category = category) }

    private fun load(refresh: Boolean) {
        viewModelScope.launch {
            _state.update { it.copy(loading = it.foods.isEmpty(), refreshing = refresh, error = null) }
            try {
                val foods = repository.foods()
                _state.update { it.copy(foods = foods) }
                if (refresh) repository.refreshCart()
            } catch (e: ApiException) {
                _state.update { it.copy(error = e.message) }
            } finally {
                _state.update { it.copy(loading = false, refreshing = false) }
            }
        }
    }

    fun addToCart(food: Food, quantity: Int) {
        if (_state.value.addingFoodId != null) return
        _state.update { it.copy(addingFoodId = food.id) }
        viewModelScope.launch {
            try {
                repository.addToCart(food.id, quantity)
                _messages.send(MenuMessage.Added("Added $quantity × ${food.title} to your cart"))
            } catch (e: ApiException) {
                _messages.send(MenuMessage.Failed(e.message ?: "Couldn't add to cart."))
                // Stock may have changed since the menu loaded.
                try {
                    val foods = repository.foods()
                    _state.update { it.copy(foods = foods) }
                } catch (_: ApiException) {
                }
            } finally {
                _state.update { it.copy(addingFoodId = null) }
            }
        }
    }
}
