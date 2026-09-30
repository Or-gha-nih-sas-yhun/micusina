package com.micusina.customer.ui.auth

import android.app.Activity
import androidx.activity.compose.BackHandler
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.layout.windowInsetsPadding
import androidx.compose.foundation.layout.windowInsetsTopHeight
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.rounded.ArrowBack
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.MarkEmailRead
import androidx.compose.material.icons.rounded.Visibility
import androidx.compose.material.icons.rounded.VisibilityOff
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableLongStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalView
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardCapitalization
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.core.view.WindowCompat
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.ui.components.BrandMark
import com.micusina.customer.ui.components.ErrorBanner
import com.micusina.customer.ui.components.FormField
import com.micusina.customer.ui.components.PrimaryButton
import com.micusina.customer.ui.components.SectionCard
import com.micusina.customer.ui.components.appViewModel
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.BrandDark
import com.micusina.customer.ui.theme.Info
import com.micusina.customer.ui.theme.InfoSoft
import kotlinx.coroutines.delay
import kotlinx.serialization.Serializable

@Serializable private data object SignInRoute
@Serializable private data object RegisterRoute
@Serializable private data object VerifyEmailRoute

/** Everything a signed-out customer can do: sign in, register, verify their email. */
@Composable
fun AuthFlow(notice: String?) {
    val viewModel = appViewModel { AuthViewModel(it) }
    val state by viewModel.state.collectAsStateWithLifecycle()
    val navController = rememberNavController()

    // Light status bar icons over the purple header; restored for the rest of the app.
    val view = LocalView.current
    DisposableEffect(view) {
        val window = (view.context as? Activity)?.window
        val controller = window?.let { WindowCompat.getInsetsController(it, view) }
        controller?.isAppearanceLightStatusBars = false
        onDispose { controller?.isAppearanceLightStatusBars = true }
    }

    NavHost(navController, startDestination = SignInRoute) {
        composable<SignInRoute> {
            SignInScreen(
                state = state,
                notice = notice,
                onChange = viewModel::update,
                onSignIn = viewModel::signIn,
                onCreateAccount = {
                    viewModel.switchForm()
                    navController.navigate(RegisterRoute)
                },
            )
        }
        composable<RegisterRoute> {
            RegisterScreen(
                state = state,
                onChange = viewModel::update,
                onSubmit = { viewModel.startRegistration { navController.navigate(VerifyEmailRoute) } },
                onBack = {
                    viewModel.switchForm()
                    navController.popBackStack()
                },
            )
        }
        composable<VerifyEmailRoute> {
            VerifyEmailScreen(
                state = state,
                onChange = viewModel::update,
                onVerify = viewModel::verifyEmail,
                onResend = viewModel::resendCode,
                onBack = {
                    viewModel.clearMessages()
                    navController.popBackStack()
                },
            )
        }
    }
}

@Composable
private fun AuthScaffold(
    title: String,
    subtitle: String,
    onBack: (() -> Unit)? = null,
    content: @Composable () -> Unit,
) {
    // The system back gesture must reset state exactly like the on-screen arrow.
    if (onBack != null) BackHandler(onBack = onBack)

    Box(Modifier.fillMaxSize()) {
        Column(
            Modifier
                .fillMaxSize()
                .background(MaterialTheme.colorScheme.background)
                .imePadding()
                .verticalScroll(rememberScrollState()),
        ) {
            Box(
                Modifier
                    .fillMaxWidth()
                    .background(
                        Brush.linearGradient(listOf(Brand, BrandDark)),
                        RoundedCornerShape(bottomStart = 36.dp, bottomEnd = 36.dp),
                    )
                    .windowInsetsPadding(WindowInsets.statusBars)
                    .padding(start = 24.dp, end = 24.dp, top = 12.dp, bottom = 64.dp),
            ) {
                Column {
                    if (onBack != null) {
                        IconButton(onClick = onBack, modifier = Modifier.offset(x = (-12).dp)) {
                            Icon(Icons.AutoMirrored.Rounded.ArrowBack, contentDescription = "Back", tint = Color.White)
                        }
                    } else {
                        Spacer(Modifier.size(24.dp))
                    }
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Surface(shape = RoundedCornerShape(18.dp), color = Color.White.copy(alpha = 0.16f)) {
                            BrandMark(size = 52.dp, modifier = Modifier.padding(4.dp))
                        }
                        Spacer(Modifier.size(14.dp))
                        Text(
                            "Mi Cusina",
                            style = MaterialTheme.typography.titleLarge,
                            color = Color.White,
                        )
                    }
                    Spacer(Modifier.size(22.dp))
                    Text(title, style = MaterialTheme.typography.headlineLarge, color = Color.White)
                    Spacer(Modifier.size(6.dp))
                    Text(subtitle, style = MaterialTheme.typography.bodyLarge, color = Color.White.copy(alpha = 0.85f))
                }
            }
            Box(
                Modifier
                    .padding(horizontal = 16.dp)
                    .offset(y = (-40).dp),
            ) {
                SectionCard(contentPadding = PaddingValues(20.dp)) {
                    content()
                }
            }
            Spacer(Modifier.navigationBarsPadding())
        }
        // Solid scrim so scrolled content never runs under the (white) status bar icons.
        Box(
            Modifier
                .fillMaxWidth()
                .windowInsetsTopHeight(WindowInsets.statusBars)
                .background(Brand),
        )
    }
}

@Composable
private fun InfoBanner(message: String) {
    Surface(color = InfoSoft, shape = MaterialTheme.shapes.medium, modifier = Modifier.fillMaxWidth()) {
        Row(Modifier.padding(14.dp), verticalAlignment = Alignment.CenterVertically) {
            Icon(Icons.Outlined.Info, contentDescription = null, tint = Info, modifier = Modifier.size(20.dp))
            Spacer(Modifier.size(10.dp))
            Text(message, style = MaterialTheme.typography.bodyMedium, color = Info)
        }
    }
}

/** The banner message, unless it only repeats an error already shown under one of [shownFields]. */
private fun AuthUiState.bannerError(vararg shownFields: String): String? =
    error?.takeUnless { message -> shownFields.any { fieldErrors[it] == message } }

@Composable
private fun PasswordField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    error: String?,
    supportingText: String? = null,
) {
    var visible by rememberSaveable { mutableStateOf(false) }
    FormField(
        value = value,
        onValueChange = onValueChange,
        label = label,
        error = error,
        supportingText = supportingText,
        keyboardType = KeyboardType.Password,
        visualTransformation = if (visible) VisualTransformation.None else PasswordVisualTransformation(),
        trailingIcon = {
            IconButton(onClick = { visible = !visible }) {
                Icon(
                    if (visible) Icons.Rounded.VisibilityOff else Icons.Rounded.Visibility,
                    contentDescription = if (visible) "Hide password" else "Show password",
                )
            }
        },
    )
}

@Composable
private fun SignInScreen(
    state: AuthUiState,
    notice: String?,
    onChange: (AuthUiState.() -> AuthUiState) -> Unit,
    onSignIn: () -> Unit,
    onCreateAccount: () -> Unit,
) {
    AuthScaffold(
        title = "Welcome back",
        subtitle = "Fresh local favorites, simple ordering, and delivery tracking.",
    ) {
        Text("Sign in", style = MaterialTheme.typography.headlineSmall)
        if (notice != null && state.error == null && state.info == null) InfoBanner(notice)
        FormField(
            value = state.email,
            onValueChange = { v -> onChange { copy(email = v) } },
            label = "Email address",
            error = state.fieldErrors["email"],
            keyboardType = KeyboardType.Email,
        )
        PasswordField(
            value = state.password,
            onValueChange = { v -> onChange { copy(password = v) } },
            label = "Password",
            error = state.fieldErrors["password"],
        )
        if (state.twoFactorPrompt != null) {
            FormField(
                value = state.twoFactorCode,
                onValueChange = { v -> onChange { copy(twoFactorCode = v.filter(Char::isDigit).take(6)) } },
                label = "Authenticator code",
                error = state.fieldErrors["two_factor_code"],
                supportingText = "6-digit code from your authenticator app",
                keyboardType = KeyboardType.NumberPassword,
            )
        }
        state.info?.let { InfoBanner(it) }
        state.bannerError("email", "password", "two_factor_code")?.let { ErrorBanner(it) }
        PrimaryButton(
            text = if (state.twoFactorPrompt != null) "Verify and sign in" else "Sign in",
            onClick = onSignIn,
            loading = state.busy,
        )
        Row(verticalAlignment = Alignment.CenterVertically) {
            HorizontalDivider(Modifier.weight(1f))
            Text(
                "New to Mi Cusina?",
                style = MaterialTheme.typography.labelMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.padding(horizontal = 12.dp),
            )
            HorizontalDivider(Modifier.weight(1f))
        }
        OutlinedButton(
            onClick = onCreateAccount,
            enabled = !state.busy,
            modifier = Modifier.fillMaxWidth().heightIn(min = 54.dp),
            shape = CircleShape,
        ) {
            Text("Create customer account", fontWeight = FontWeight.ExtraBold, color = Brand)
        }
    }
}

@Composable
private fun RegisterScreen(
    state: AuthUiState,
    onChange: (AuthUiState.() -> AuthUiState) -> Unit,
    onSubmit: () -> Unit,
    onBack: () -> Unit,
) {
    AuthScaffold(
        title = "Create account",
        subtitle = "Order your favorites and reserve a table in a few taps.",
        onBack = onBack,
    ) {
        FormField(
            value = state.name,
            onValueChange = { v -> onChange { copy(name = v) } },
            label = "Full name",
            error = state.fieldErrors["name"],
            capitalization = KeyboardCapitalization.Words,
        )
        FormField(
            value = state.email,
            onValueChange = { v -> onChange { copy(email = v) } },
            label = "Email address",
            error = state.fieldErrors["email"],
            keyboardType = KeyboardType.Email,
        )
        FormField(
            value = state.phone,
            onValueChange = { v -> onChange { copy(phone = PhilippinePhone.sanitizeInput(v)) } },
            label = "Mobile number",
            error = state.fieldErrors["phone"],
            supportingText = "09XXXXXXXXX",
            keyboardType = KeyboardType.Phone,
        )
        FormField(
            value = state.address,
            onValueChange = { v -> onChange { copy(address = v) } },
            label = "Address",
            error = state.fieldErrors["address"],
            singleLine = false,
            capitalization = KeyboardCapitalization.Words,
        )
        PasswordField(
            value = state.password,
            onValueChange = { v -> onChange { copy(password = v.take(15)) } },
            label = "Password",
            error = state.fieldErrors["password"],
            supportingText = "8 to 15 characters",
        )
        PasswordField(
            value = state.confirmPassword,
            onValueChange = { v -> onChange { copy(confirmPassword = v.take(15)) } },
            label = "Confirm password",
            error = state.fieldErrors["password_confirmation"],
        )
        state.bannerError("name", "email", "phone", "address", "password", "password_confirmation")?.let { ErrorBanner(it) }
        PrimaryButton(text = "Send verification code", onClick = onSubmit, loading = state.busy)
        Text(
            "We'll email you a 6-digit code to confirm it's you.",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            textAlign = TextAlign.Center,
            modifier = Modifier.fillMaxWidth(),
        )
    }
}

@Composable
private fun VerifyEmailScreen(
    state: AuthUiState,
    onChange: (AuthUiState.() -> AuthUiState) -> Unit,
    onVerify: () -> Unit,
    onResend: () -> Unit,
    onBack: () -> Unit,
) {
    var now by remember { mutableLongStateOf(System.currentTimeMillis()) }
    LaunchedEffect(state.codeExpiresAtMillis) {
        while (now < state.codeExpiresAtMillis) {
            delay(1_000)
            now = System.currentTimeMillis()
        }
    }
    val secondsLeft = ((state.codeExpiresAtMillis - now) / 1000).coerceAtLeast(0)

    AuthScaffold(
        title = "Check your email",
        subtitle = "Enter the code we sent to ${state.email.trim()}.",
        onBack = onBack,
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(Icons.Outlined.MarkEmailRead, contentDescription = null, tint = Brand)
            Spacer(Modifier.size(10.dp))
            Text("Verify your email", style = MaterialTheme.typography.titleLarge)
        }
        FormField(
            value = state.verificationCode,
            onValueChange = { v -> onChange { copy(verificationCode = v.filter(Char::isDigit).take(6)) } },
            label = "6-digit code",
            error = state.fieldErrors["email_code"],
            supportingText = if (secondsLeft > 0) {
                "Code expires in %d:%02d".format(secondsLeft / 60, secondsLeft % 60)
            } else {
                "This code has expired. Send a new one."
            },
            keyboardType = KeyboardType.NumberPassword,
        )
        state.info?.let { InfoBanner(it) }
        state.bannerError("email_code")?.let { ErrorBanner(it) }
        PrimaryButton(
            text = "Verify and create account",
            onClick = onVerify,
            loading = state.busy,
            enabled = state.verificationCode.length == 6 && secondsLeft > 0,
        )
        TextButton(onClick = onResend, enabled = !state.busy, modifier = Modifier.fillMaxWidth()) {
            Text("Send a new code", color = Brand, fontWeight = FontWeight.Bold)
        }
    }
}
