package com.micusina.customer.ui.cart

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.model.CartItem
import com.micusina.customer.data.remote.ApiException
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.receiveAsFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class CartUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val error: String? = null,
    val busyItemIds: Set<Int> = emptySet(),
)

class CartViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    val items: StateFlow<List<CartItem>> = repository.cart

    private val _state = MutableStateFlow(CartUiState())
    val state: StateFlow<CartUiState> = _state.asStateFlow()

    private val _messages = Channel<String>(Channel.BUFFERED)
    val messages: Flow<String> = _messages.receiveAsFlow()

    init {
        load(refresh = false)
    }

    fun refresh() = load(refresh = true)

    private fun load(refresh: Boolean) {
        viewModelScope.launch {
            _state.update { it.copy(refreshing = refresh, error = null) }
            try {
                repository.refreshCart()
            } catch (e: ApiException) {
                _state.update { it.copy(error = e.message) }
            } finally {
                _state.update { it.copy(loading = false, refreshing = false) }
            }
        }
    }

    fun setQuantity(item: CartItem, quantity: Int) {
        if (quantity < 1) return
        runForItem(item) { repository.updateCartQuantity(item.id, quantity) }
    }

    fun remove(item: CartItem) {
        runForItem(item) {
            repository.removeFromCart(item.id)
            _messages.send("Removed ${item.title} from your cart")
        }
    }

    private fun runForItem(item: CartItem, block: suspend () -> Unit) {
        if (item.id in _state.value.busyItemIds) return
        _state.update { it.copy(busyItemIds = it.busyItemIds + item.id) }
        viewModelScope.launch {
            try {
                block()
            } catch (e: ApiException) {
                _messages.send(e.message ?: "Couldn't update your cart.")
            } finally {
                _state.update { it.copy(busyItemIds = it.busyItemIds - item.id) }
            }
        }
    }
}
