package com.micusina.customer.ui.auth

import android.util.Patterns
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.micusina.customer.data.LoginResult
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.data.model.RegistrationRequest
import com.micusina.customer.data.remote.ApiException
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class AuthUiState(
    // Sign in
    val email: String = "",
    val password: String = "",
    val twoFactorCode: String = "",
    val twoFactorPrompt: String? = null,
    // Registration
    val name: String = "",
    val phone: String = "",
    val address: String = "",
    val confirmPassword: String = "",
    val registrationId: String? = null,
    val codeExpiresAtMillis: Long = 0,
    val verificationCode: String = "",
    // Shared
    val busy: Boolean = false,
    val error: String? = null,
    val info: String? = null,
    val fieldErrors: Map<String, String> = emptyMap(),
)

class AuthViewModel(private val repository: MiCusinaRepository) : ViewModel() {
    private val _state = MutableStateFlow(AuthUiState())
    val state: StateFlow<AuthUiState> = _state.asStateFlow()

    fun update(transform: AuthUiState.() -> AuthUiState) {
        _state.update { it.transform().copy(error = null, info = null) }
    }

    /**
     * Resets everything but the typed email when moving between sign-in and registration, so a
     * password or 2FA prompt from one form never leaks into the other.
     */
    fun switchForm() = _state.update { AuthUiState(email = it.email, name = it.name, phone = it.phone, address = it.address) }

    /** Leaving the verification screen keeps the registration details for another attempt. */
    fun clearMessages() = _state.update { it.copy(error = null, info = null, fieldErrors = emptyMap(), verificationCode = "") }

    fun signIn() {
        val s = _state.value
        val errors = buildMap {
            if (!Patterns.EMAIL_ADDRESS.matcher(s.email.trim()).matches()) put("email", "Enter a valid email address.")
            if (s.password.isEmpty()) put("password", "Enter your password.")
            if (s.twoFactorPrompt != null && s.twoFactorCode.length != 6) put("two_factor_code", "Enter the 6-digit code.")
        }
        if (errors.isNotEmpty()) {
            _state.update { it.copy(fieldErrors = errors) }
            return
        }
        run {
            when (val result = repository.login(s.email.trim(), s.password, s.twoFactorCode)) {
                is LoginResult.TwoFactorRequired ->
                    _state.update { it.copy(twoFactorPrompt = result.message, info = result.message) }
                LoginResult.Success -> _state.value = AuthUiState(email = s.email.trim())
            }
        }
    }

    fun startRegistration(onCodeSent: () -> Unit) {
        val s = _state.value
        val errors = buildMap {
            if (s.name.isBlank()) put("name", "Enter your full name.")
            if (!Patterns.EMAIL_ADDRESS.matcher(s.email.trim()).matches()) put("email", "Enter a valid email address.")
            if (!PhilippinePhone.isValid(s.phone)) put("phone", "Use 09XXXXXXXXX or +639XXXXXXXXX.")
            if (s.address.isBlank()) put("address", "Enter your address.")
            if (s.password.length !in 8..15) put("password", "Use 8 to 15 characters.")
            if (s.confirmPassword != s.password) put("password_confirmation", "Passwords do not match.")
        }
        if (errors.isNotEmpty()) {
            _state.update { it.copy(fieldErrors = errors) }
            return
        }
        run {
            sendCode(s)
            onCodeSent()
        }
    }

    fun resendCode() {
        run {
            sendCode(_state.value)
            _state.update { it.copy(info = "We sent a new code to ${it.email.trim()}.", verificationCode = "") }
        }
    }

    private suspend fun sendCode(s: AuthUiState) {
        val started = repository.startRegistration(
            RegistrationRequest(
                name = s.name.trim(),
                email = s.email.trim(),
                phone = s.phone.trim(),
                address = s.address.trim(),
                password = s.password,
                passwordConfirmation = s.confirmPassword,
            ),
        )
        _state.update {
            it.copy(
                registrationId = started.registrationId,
                codeExpiresAtMillis = System.currentTimeMillis() + started.expiresIn * 1000L,
            )
        }
    }

    fun verifyEmail() {
        val s = _state.value
        val registrationId = s.registrationId ?: return
        if (s.verificationCode.length != 6) {
            _state.update { it.copy(fieldErrors = mapOf("email_code" to "Enter the 6-digit code.")) }
            return
        }
        run {
            repository.completeRegistration(registrationId, s.verificationCode)
            _state.value = AuthUiState(email = s.email.trim())
        }
    }

    private fun run(block: suspend () -> Unit) {
        if (_state.value.busy) return
        _state.update { it.copy(busy = true, error = null, info = null, fieldErrors = emptyMap()) }
        viewModelScope.launch {
            try {
                block()
            } catch (e: ApiException) {
                _state.update { it.copy(error = e.message, fieldErrors = e.fieldErrors) }
            } finally {
                _state.update { it.copy(busy = false) }
            }
        }
    }
}
