package com.micusina.customer.ui.reservations

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.rounded.ArrowBack
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.Schedule
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.DatePicker
import androidx.compose.material3.DatePickerDefaults
import androidx.compose.material3.DatePickerDialog
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SelectableDates
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TimePicker
import androidx.compose.material3.TimePickerDefaults
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.material3.rememberDatePickerState
import androidx.compose.material3.rememberTimePickerState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardCapitalization
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.micusina.customer.data.PaymentMethods
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.data.ReservationPricing
import com.micusina.customer.ui.ManilaZone
import com.micusina.customer.ui.ReservationTimeFormat
import com.micusina.customer.ui.components.ErrorBanner
import com.micusina.customer.ui.components.FormField
import com.micusina.customer.ui.components.LocalSnackbarHostState
import com.micusina.customer.ui.components.PrimaryButton
import com.micusina.customer.ui.components.QuantityStepper
import com.micusina.customer.ui.components.RadioOption
import com.micusina.customer.ui.components.SectionCard
import com.micusina.customer.ui.components.appViewModel
import com.micusina.customer.ui.components.pickerSemantics
import com.micusina.customer.ui.formatReservationDate
import com.micusina.customer.ui.openInBrowser
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.BrandSoft
import com.micusina.customer.ui.toPeso
import java.time.Instant
import java.time.LocalDate
import java.time.LocalTime
import java.time.ZoneOffset

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NewReservationScreen(onBack: () -> Unit) {
    val viewModel = appViewModel { NewReservationViewModel(it) }
    val state by viewModel.state.collectAsStateWithLifecycle()
    val context = LocalContext.current
    val snackbar = LocalSnackbarHostState.current
    var showDatePicker by rememberSaveable { mutableStateOf(false) }
    var showTimePicker by rememberSaveable { mutableStateOf(false) }

    LaunchedEffect(viewModel) {
        viewModel.events.collect { event ->
            when (event) {
                is NewReservationEvent.OpenPayment -> {
                    if (!openInBrowser(context, event.checkoutUrl)) {
                        // Messages must show before leaving: this screen's scope ends on navigation.
                        snackbar.showSnackbar("Reservation saved. Tap Pay in Reservations once a browser is available.")
                    }
                    onBack()
                }
                // The list screen refreshes on resume and shows the new reservation.
                is NewReservationEvent.Created -> onBack()
            }
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Reserve a table") },
                navigationIcon = {
                    IconButton(onClick = onBack) { Icon(Icons.AutoMirrored.Rounded.ArrowBack, contentDescription = "Back") }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = MaterialTheme.colorScheme.background),
            )
        },
        bottomBar = {
            Surface(color = Color.White, shadowElevation = 8.dp) {
                Column(Modifier.navigationBarsPadding().padding(16.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                    state.error?.let { ErrorBanner(it) }
                    PrimaryButton(
                        text = "Continue to pay ${ReservationPricing.DEPOSIT.toPeso()}",
                        onClick = viewModel::submit,
                        loading = state.submitting,
                    )
                }
            }
        },
    ) { padding ->
        Column(
            Modifier
                .padding(padding)
                .fillMaxSize()
                .imePadding()
                .verticalScroll(rememberScrollState())
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp),
        ) {
            SectionCard {
                Text("When", style = MaterialTheme.typography.titleLarge)
                PickerField(
                    label = "Date",
                    value = state.date?.let { formatReservationDate(it.toString()) }.orEmpty(),
                    placeholder = "Choose a date",
                    icon = { Icon(Icons.Outlined.CalendarMonth, contentDescription = null) },
                    error = state.fieldErrors["date"],
                    onClick = { showDatePicker = true },
                )
                PickerField(
                    label = "Time",
                    value = state.time?.format(ReservationTimeFormat).orEmpty(),
                    placeholder = "Choose a time",
                    icon = { Icon(Icons.Outlined.Schedule, contentDescription = null) },
                    error = state.fieldErrors["time"],
                    onClick = { showTimePicker = true },
                )
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Column(Modifier.weight(1f)) {
                        Text("Guests", style = MaterialTheme.typography.titleSmall)
                        Text(
                            state.fieldErrors["guest"] ?: "Up to ${ReservationPricing.MAX_GUESTS} people",
                            style = MaterialTheme.typography.bodySmall,
                            color = if (state.fieldErrors["guest"] != null) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                    QuantityStepper(
                        quantity = state.guests,
                        onDecrease = { viewModel.update { copy(guests = guests - 1) } },
                        onIncrease = { viewModel.update { copy(guests = guests + 1) } },
                        canIncrease = state.guests < ReservationPricing.MAX_GUESTS,
                    )
                }
            }

            SectionCard {
                Text("Your details", style = MaterialTheme.typography.titleLarge)
                FormField(
                    value = state.firstName,
                    onValueChange = { v -> viewModel.update { copy(firstName = v) } },
                    label = "First name",
                    error = state.fieldErrors["first_name"],
                    capitalization = KeyboardCapitalization.Words,
                )
                FormField(
                    value = state.lastName,
                    onValueChange = { v -> viewModel.update { copy(lastName = v) } },
                    label = "Last name",
                    error = state.fieldErrors["last_name"],
                    capitalization = KeyboardCapitalization.Words,
                )
                FormField(
                    value = state.phone,
                    onValueChange = { v -> viewModel.update { copy(phone = PhilippinePhone.sanitizeInput(v)) } },
                    label = "Mobile number",
                    error = state.fieldErrors["phone"],
                    supportingText = "09XXXXXXXXX",
                    keyboardType = KeyboardType.Phone,
                )
            }

            SectionCard {
                Text("Downpayment", style = MaterialTheme.typography.titleLarge)
                Surface(color = BrandSoft, shape = MaterialTheme.shapes.medium) {
                    Row(Modifier.padding(14.dp), verticalAlignment = Alignment.Top) {
                        Icon(Icons.Outlined.Info, contentDescription = null, tint = Brand, modifier = Modifier.size(20.dp))
                        Spacer(Modifier.size(10.dp))
                        Text(
                            "The reservation fee is ${ReservationPricing.PRICE.toPeso()}. Pay a " +
                                "${ReservationPricing.DEPOSIT.toPeso()} downpayment now through secure PayMongo checkout " +
                                "to hold your table. Downpayments are non-refundable.",
                            style = MaterialTheme.typography.bodyMedium,
                        )
                    }
                }
                PaymentMethods.reservation.forEach { method ->
                    RadioOption(
                        title = method,
                        selected = state.paymentMethod == method,
                        onSelect = { viewModel.update { copy(paymentMethod = method) } },
                    )
                }
            }
        }
    }

    if (showDatePicker) {
        ReservationDatePicker(
            initial = state.date,
            onPick = { picked ->
                viewModel.update { copy(date = picked) }
                showDatePicker = false
            },
            onDismiss = { showDatePicker = false },
        )
    }
    if (showTimePicker) {
        ReservationTimePicker(
            initial = state.time ?: LocalTime.of(18, 0),
            onPick = { picked ->
                viewModel.update { copy(time = picked) }
                showTimePicker = false
            },
            onDismiss = { showTimePicker = false },
        )
    }
}

/** A read-only field that opens a picker dialog when tapped. */
@Composable
private fun PickerField(
    label: String,
    value: String,
    placeholder: String,
    icon: @Composable () -> Unit,
    error: String?,
    onClick: () -> Unit,
) {
    Box {
        OutlinedTextField(
            value = value,
            onValueChange = {},
            readOnly = true,
            label = { Text(label) },
            placeholder = { Text(placeholder) },
            leadingIcon = icon,
            isError = error != null,
            supportingText = error?.let { { Text(it) } },
            singleLine = true,
            shape = MaterialTheme.shapes.medium,
            modifier = Modifier.fillMaxWidth(),
        )
        Box(
            Modifier
                .matchParentSize()
                .clickable(role = Role.Button, onClickLabel = placeholder, onClick = onClick)
                .pickerSemantics(label, value, placeholder, error),
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun ReservationDatePicker(initial: LocalDate?, onPick: (LocalDate) -> Unit, onDismiss: () -> Unit) {
    // DatePicker works in UTC midnight millis; "today" is the restaurant's date in Manila.
    val todayUtcMillis = LocalDate.now(ManilaZone).atStartOfDay(ZoneOffset.UTC).toInstant().toEpochMilli()
    val state = rememberDatePickerState(
        initialSelectedDateMillis = initial?.atStartOfDay(ZoneOffset.UTC)?.toInstant()?.toEpochMilli(),
        selectableDates = object : SelectableDates {
            override fun isSelectableDate(utcTimeMillis: Long) = utcTimeMillis >= todayUtcMillis
            override fun isSelectableYear(year: Int) = year >= LocalDate.now(ManilaZone).year
        },
    )
    DatePickerDialog(
        onDismissRequest = onDismiss,
        confirmButton = {
            TextButton(
                onClick = {
                    state.selectedDateMillis?.let { millis ->
                        onPick(Instant.ofEpochMilli(millis).atZone(ZoneOffset.UTC).toLocalDate())
                    }
                },
                enabled = state.selectedDateMillis != null,
            ) { Text("OK", fontWeight = FontWeight.Bold) }
        },
        dismissButton = { TextButton(onClick = onDismiss) { Text("Cancel") } },
        colors = DatePickerDefaults.colors(containerColor = Color.White),
    ) {
        DatePicker(state = state, colors = DatePickerDefaults.colors(containerColor = Color.White))
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun ReservationTimePicker(initial: LocalTime, onPick: (LocalTime) -> Unit, onDismiss: () -> Unit) {
    val state = rememberTimePickerState(initialHour = initial.hour, initialMinute = initial.minute, is24Hour = false)
    AlertDialog(
        onDismissRequest = onDismiss,
        containerColor = Color.White,
        title = { Text("Choose a time") },
        text = {
            Box(Modifier.fillMaxWidth(), contentAlignment = Alignment.Center) {
                TimePicker(state = state, colors = TimePickerDefaults.colors(clockDialColor = BrandSoft))
            }
        },
        confirmButton = {
            TextButton(onClick = { onPick(LocalTime.of(state.hour, state.minute)) }) { Text("OK", fontWeight = FontWeight.Bold) }
        },
        dismissButton = { TextButton(onClick = onDismiss) { Text("Cancel") } },
    )
}
