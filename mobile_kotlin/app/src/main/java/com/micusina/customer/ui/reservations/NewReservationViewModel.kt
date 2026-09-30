package com.micusina.customer.ui.reservations

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.PaymentMethods
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.data.ReservationPricing
import com.micusina.customer.data.model.ReservationRequest
import com.micusina.customer.data.remote.ApiException
import com.micusina.customer.ui.ManilaZone
import com.micusina.customer.ui.ReservationTimeFormat
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.receiveAsFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDate
import java.time.LocalTime
import java.time.ZonedDateTime

data class NewReservationUiState(
    val firstName: String = "",
    val lastName: String = "",
    val phone: String = "",
    val guests: Int = 2,
    val date: LocalDate? = null,
    val time: LocalTime? = null,
    val paymentMethod: String = PaymentMethods.GCASH,
    val submitting: Boolean = false,
    val error: String? = null,
    val fieldErrors: Map<String, String> = emptyMap(),
)

sealed interface NewReservationEvent {
    /** The reservation exists; the customer now pays the downpayment on PayMongo. */
    data class OpenPayment(val checkoutUrl: String) : NewReservationEvent
    data class Created(val message: String) : NewReservationEvent
}

class NewReservationViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    private val _state = MutableStateFlow(prefilled())
    val state: StateFlow<NewReservationUiState> = _state.asStateFlow()

    private val _events = Channel<NewReservationEvent>(Channel.BUFFERED)
    val events: Flow<NewReservationEvent> = _events.receiveAsFlow()

    private fun prefilled(): NewReservationUiState {
        val user = repository.currentUser ?: return NewReservationUiState()
        val parts = user.name.trim().split(Regex("\\s+"), limit = 2)
        return NewReservationUiState(
            firstName = parts.getOrElse(0) { "" },
            lastName = parts.getOrElse(1) { "" },
            phone = PhilippinePhone.toLocal(user.phone),
        )
    }

    fun update(transform: NewReservationUiState.() -> NewReservationUiState) {
        _state.update { it.transform().copy(error = null) }
    }

    fun submit() {
        val s = _state.value
        if (s.submitting) return
        val now = ZonedDateTime.now(ManilaZone)
        val errors = buildMap {
            if (s.firstName.isBlank()) put("first_name", "Enter your first name.")
            if (s.lastName.isBlank()) put("last_name", "Enter your last name.")
            if (!PhilippinePhone.isValid(s.phone)) put("phone", "Use 09XXXXXXXXX or +639XXXXXXXXX.")
            if (s.guests !in 1..ReservationPricing.MAX_GUESTS) put("guest", "Choose 1 to ${ReservationPricing.MAX_GUESTS} guests.")
            if (s.date == null) put("date", "Choose a date.")
            if (s.time == null) put("time", "Choose a time.")
            if (s.date != null && s.time != null && !ZonedDateTime.of(s.date, s.time, ManilaZone).isAfter(now)) {
                put("time", "Choose a time later than now.")
            }
        }
        val date = s.date
        val time = s.time
        if (errors.isNotEmpty() || date == null || time == null) {
            _state.update { it.copy(fieldErrors = errors, error = "Please check the highlighted fields.") }
            return
        }
        _state.update { it.copy(submitting = true, error = null, fieldErrors = emptyMap()) }
        viewModelScope.launch {
            try {
                val created = repository.createReservation(
                    ReservationRequest(
                        firstName = s.firstName.trim(),
                        lastName = s.lastName.trim(),
                        phone = s.phone.trim(),
                        guest = s.guests,
                        date = date.toString(),
                        time = time.format(ReservationTimeFormat),
                        paymentMethod = s.paymentMethod,
                    ),
                )
                val url = created.checkoutUrl
                _events.send(
                    if (url != null) {
                        NewReservationEvent.OpenPayment(url)
                    } else {
                        NewReservationEvent.Created(created.message ?: "Reservation created.")
                    },
                )
            } catch (e: ApiException) {
                _state.update { it.copy(error = e.message, fieldErrors = e.fieldErrors) }
            } finally {
                _state.update { it.copy(submitting = false) }
            }
        }
    }
}
