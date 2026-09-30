package com.micusina.customer.ui.reservations

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.model.Reservation
import com.micusina.customer.data.remote.ApiException
import com.micusina.customer.ui.ManilaZone
import com.micusina.customer.ui.parseReservationTime
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.receiveAsFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDate
import java.time.ZonedDateTime

data class ReservationsUiState(
    val reservations: List<Reservation> = emptyList(),
    val loaded: Boolean = false,
    val refreshing: Boolean = false,
    val error: String? = null,
    val cancellingId: Int? = null,
)

/** Reservation lifecycle: Awaiting Payment -> Pending (paid) -> Approved -> Completed, or Canceled. */
enum class ReservationStage(val label: String, val explanation: String) {
    AwaitingPayment("Awaiting payment", "Pay the downpayment to hold your table."),
    Pending("Awaiting confirmation", "Downpayment received. We'll confirm your table shortly."),
    Approved("Confirmed", "Your table is confirmed. See you soon!"),
    Completed("Completed", "Thanks for dining with us."),
    Canceled("Canceled", "This reservation was cancelled.");

    companion object {
        fun of(status: String?): ReservationStage = when (status?.trim()?.lowercase()) {
            "awaiting payment" -> AwaitingPayment
            "approved" -> Approved
            "completed" -> Completed
            "canceled", "cancelled" -> Canceled
            else -> Pending
        }
    }
}

val Reservation.stage: ReservationStage get() = ReservationStage.of(status)

/** Mirrors MobileApiController::cancelReservation(): not final and still in the future. */
val Reservation.canCancel: Boolean
    get() {
        if (stage == ReservationStage.Completed || stage == ReservationStage.Canceled) return false
        val date = runCatching { LocalDate.parse(date) }.getOrNull() ?: return false
        val time = parseReservationTime(time) ?: return false
        return ZonedDateTime.of(date, time, ManilaZone).isAfter(ZonedDateTime.now(ManilaZone))
    }

class ReservationsViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    private val _state = MutableStateFlow(ReservationsUiState())
    val state: StateFlow<ReservationsUiState> = _state.asStateFlow()

    private val _messages = Channel<String>(Channel.BUFFERED)
    val messages: Flow<String> = _messages.receiveAsFlow()

    /** Runs on every resume, e.g. when the customer returns from the PayMongo checkout. */
    fun load(userInitiated: Boolean = false) {
        viewModelScope.launch {
            if (userInitiated) _state.update { it.copy(refreshing = true) }
            try {
                val reservations = repository.reservations()
                _state.update { it.copy(reservations = reservations, error = null) }
            } catch (e: ApiException) {
                if (userInitiated || !_state.value.loaded) _state.update { it.copy(error = e.message) }
            } finally {
                _state.update { it.copy(loaded = true, refreshing = false) }
            }
        }
    }

    fun cancel(reservation: Reservation) {
        if (_state.value.cancellingId != null) return
        _state.update { it.copy(cancellingId = reservation.id) }
        viewModelScope.launch {
            try {
                _messages.send(repository.cancelReservation(reservation.id))
            } catch (e: ApiException) {
                _messages.send(e.message ?: "Couldn't cancel this reservation.")
            } finally {
                _state.update { it.copy(cancellingId = null) }
                load()
            }
        }
    }
}
