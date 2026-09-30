package com.micusina.customer.ui.orders

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.remote.ApiException
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.receiveAsFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class OrdersUiState(
    val groups: List<OrderGroup> = emptyList(),
    val loaded: Boolean = false,
    val refreshing: Boolean = false,
    val error: String? = null,
    val cancellingId: Int? = null,
)

class OrdersViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    private val _state = MutableStateFlow(OrdersUiState())
    val state: StateFlow<OrdersUiState> = _state.asStateFlow()

    private val _messages = Channel<String>(Channel.BUFFERED)
    val messages: Flow<String> = _messages.receiveAsFlow()

    /** Called on resume and periodically while visible; only a pull-to-refresh shows the spinner. */
    fun load(userInitiated: Boolean = false) {
        viewModelScope.launch {
            if (userInitiated) _state.update { it.copy(refreshing = true) }
            try {
                val groups = groupOrders(repository.orders())
                _state.update { it.copy(groups = groups, error = null) }
            } catch (e: ApiException) {
                // Keep showing the last good list during a background refresh.
                if (userInitiated || !_state.value.loaded) _state.update { it.copy(error = e.message) }
            } finally {
                _state.update { it.copy(loaded = true, refreshing = false) }
            }
        }
    }

    fun cancel(group: OrderGroup) {
        if (_state.value.cancellingId != null) return
        _state.update { it.copy(cancellingId = group.id) }
        viewModelScope.launch {
            try {
                _messages.send(repository.cancelOrder(group.id))
                load()
            } catch (e: ApiException) {
                _messages.send(e.message ?: "Couldn't cancel this order.")
                load()
            } finally {
                _state.update { it.copy(cancellingId = null) }
            }
        }
    }
}
