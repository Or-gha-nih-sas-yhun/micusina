package com.micusina.customer.ui.cart

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.DeliveryAreas
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.PaymentMethods
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.data.model.CartItem
import com.micusina.customer.data.model.CheckoutRequest
import com.micusina.customer.data.remote.ApiException
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class CheckoutUiState(
    val name: String = "",
    val phone: String = "",
    val municipality: String = "",
    val barangay: String = "",
    val purok: String = "",
    val details: String = "",
    val paymentMethod: String = PaymentMethods.CASH_ON_DELIVERY,
    val paymentReference: String = "",
    val submitting: Boolean = false,
    val error: String? = null,
    val fieldErrors: Map<String, String> = emptyMap(),
    /** Set once the order is placed; the screen shows a confirmation. */
    val placedTotal: Double? = null,
) {
    val isOnlinePayment: Boolean get() = paymentMethod != PaymentMethods.CASH_ON_DELIVERY
}

class CheckoutViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    val items: StateFlow<List<CartItem>> = repository.cart

    private val _state = MutableStateFlow(prefilled())
    val state: StateFlow<CheckoutUiState> = _state.asStateFlow()

    private fun prefilled(): CheckoutUiState {
        val user = repository.currentUser ?: return CheckoutUiState()
        val address = DeliveryAreas.parse(user.address)
        return CheckoutUiState(
            name = user.name,
            phone = PhilippinePhone.toLocal(user.phone),
            municipality = address?.municipality.orEmpty(),
            barangay = address?.barangay.orEmpty(),
            purok = address?.purok.orEmpty(),
            details = address?.details.orEmpty(),
        )
    }

    fun update(transform: CheckoutUiState.() -> CheckoutUiState) {
        _state.update { it.transform().copy(error = null) }
    }

    fun selectMunicipality(municipality: String) = update {
        copy(municipality = municipality, barangay = if (municipality == this.municipality) barangay else "")
    }

    fun placeOrder() {
        val s = _state.value
        if (s.submitting) return
        val errors = buildMap {
            if (s.name.isBlank()) put("name", "Enter the recipient's name.")
            if (!PhilippinePhone.isValid(s.phone)) put("phone", "Use 09XXXXXXXXX or +639XXXXXXXXX.")
            if (s.municipality !in DeliveryAreas.municipalities) put("municipality", "Choose a municipality.")
            if (s.barangay !in DeliveryAreas.barangays[s.municipality].orEmpty()) put("barangay", "Choose a barangay.")
            if (s.purok.isBlank()) put("purok", "Enter your purok, house number, or street.")
            if (s.isOnlinePayment && s.paymentReference.isBlank()) {
                put("payment_reference", "Enter the reference number from your ${s.paymentMethod} payment.")
            }
        }
        if (errors.isNotEmpty()) {
            _state.update { it.copy(fieldErrors = errors, error = "Please check the highlighted fields.") }
            return
        }
        val total = items.value.sumOf { it.price }
        _state.update { it.copy(submitting = true, error = null, fieldErrors = emptyMap()) }
        viewModelScope.launch {
            try {
                repository.checkout(
                    CheckoutRequest(
                        name = s.name.trim(),
                        phone = s.phone.trim(),
                        municipality = s.municipality,
                        barangay = s.barangay,
                        purok = s.purok.trim(),
                        addressDetails = s.details.trim().ifEmpty { null },
                        paymentMethod = s.paymentMethod,
                        paymentReference = if (s.isOnlinePayment) s.paymentReference.trim() else null,
                    ),
                )
                _state.update { it.copy(placedTotal = total) }
            } catch (e: ApiException) {
                _state.update { it.copy(error = e.message, fieldErrors = e.fieldErrors) }
            } finally {
                _state.update { it.copy(submitting = false) }
            }
        }
    }
}
