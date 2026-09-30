package com.micusina.customer.ui.cart

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.rounded.ArrowBack
import androidx.compose.material.icons.rounded.CheckCircle
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardCapitalization
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import coil3.compose.SubcomposeAsyncImage
import com.micusina.customer.data.DeliveryAreas
import com.micusina.customer.data.PaymentMethods
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.data.SiteUrls
import com.micusina.customer.ui.components.ErrorBanner
import com.micusina.customer.ui.components.FormField
import com.micusina.customer.ui.components.PrimaryButton
import com.micusina.customer.ui.components.RadioOption
import com.micusina.customer.ui.components.SectionCard
import com.micusina.customer.ui.components.SelectField
import com.micusina.customer.ui.components.appViewModel
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.Success
import com.micusina.customer.ui.theme.SuccessSoft
import com.micusina.customer.ui.toPeso

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CheckoutScreen(onBack: () -> Unit, onOrderPlaced: () -> Unit) {
    val viewModel = appViewModel { CheckoutViewModel(it) }
    val state by viewModel.state.collectAsStateWithLifecycle()
    val items by viewModel.items.collectAsStateWithLifecycle()
    val total = items.sumOf { it.price }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Checkout") },
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
                        text = "Place order · ${total.toPeso()}",
                        onClick = viewModel::placeOrder,
                        loading = state.submitting,
                        enabled = items.isNotEmpty() && state.placedTotal == null,
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
                Text("Deliver to", style = MaterialTheme.typography.titleLarge)
                Text(
                    "We deliver across Bantayan Island.",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                FormField(
                    value = state.name,
                    onValueChange = { v -> viewModel.update { copy(name = v) } },
                    label = "Recipient name",
                    error = state.fieldErrors["name"],
                    capitalization = KeyboardCapitalization.Words,
                )
                FormField(
                    value = state.phone,
                    onValueChange = { v -> viewModel.update { copy(phone = PhilippinePhone.sanitizeInput(v)) } },
                    label = "Mobile number",
                    error = state.fieldErrors["phone"],
                    supportingText = "The rider will call this number",
                    keyboardType = KeyboardType.Phone,
                )
                SelectField(
                    label = "Municipality",
                    value = state.municipality,
                    options = DeliveryAreas.municipalities,
                    onSelect = viewModel::selectMunicipality,
                    error = state.fieldErrors["municipality"],
                    placeholder = "Select municipality",
                )
                SelectField(
                    label = "Barangay",
                    value = state.barangay,
                    options = DeliveryAreas.barangays[state.municipality].orEmpty(),
                    onSelect = { v -> viewModel.update { copy(barangay = v) } },
                    error = state.fieldErrors["barangay"],
                    enabled = state.municipality.isNotEmpty(),
                    placeholder = if (state.municipality.isEmpty()) "Choose a municipality first" else "Select barangay",
                )
                FormField(
                    value = state.purok,
                    onValueChange = { v -> viewModel.update { copy(purok = v) } },
                    label = "Purok, house number, or street",
                    error = state.fieldErrors["purok"],
                    capitalization = KeyboardCapitalization.Words,
                )
                FormField(
                    value = state.details,
                    onValueChange = { v -> viewModel.update { copy(details = v) } },
                    label = "Landmark or delivery notes (optional)",
                    error = state.fieldErrors["address_details"],
                    singleLine = false,
                    capitalization = KeyboardCapitalization.Sentences,
                )
            }

            SectionCard {
                Text("Payment", style = MaterialTheme.typography.titleLarge)
                PaymentMethods.checkout.forEach { method ->
                    RadioOption(
                        title = method,
                        description = when (method) {
                            PaymentMethods.CASH_ON_DELIVERY -> "Pay with cash when your order arrives."
                            PaymentMethods.GCASH -> "Scan our GCash QR and send the reference number."
                            else -> "Scan our bank QR and send the reference number."
                        },
                        selected = state.paymentMethod == method,
                        onSelect = { viewModel.update { copy(paymentMethod = method) } },
                    )
                }
                if (state.isOnlinePayment) {
                    SiteUrls.paymentQr(state.paymentMethod)?.let { qr ->
                        Column(Modifier.fillMaxWidth(), horizontalAlignment = Alignment.CenterHorizontally) {
                            PaymentQr(qr, contentDescription = "${state.paymentMethod} QR code")
                            Text(
                                "Pay ${total.toPeso()}, then enter the reference number below. We'll verify it before preparing your order.",
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant,
                                textAlign = TextAlign.Center,
                                modifier = Modifier.padding(top = 8.dp),
                            )
                        }
                    }
                    FormField(
                        value = state.paymentReference,
                        onValueChange = { v -> viewModel.update { copy(paymentReference = v.take(100)) } },
                        label = "Payment reference number",
                        error = state.fieldErrors["payment_reference"],
                    )
                }
            }

            SectionCard {
                Text("Order summary", style = MaterialTheme.typography.titleLarge)
                if (items.isEmpty()) {
                    Text("Your cart is empty.", color = MaterialTheme.colorScheme.onSurfaceVariant)
                } else {
                    OrderSummaryLines(items)
                }
            }
        }
    }

    state.placedTotal?.let { placedTotal ->
        OrderPlacedDialog(
            total = placedTotal,
            onlinePayment = state.isOnlinePayment,
            onTrack = onOrderPlaced,
        )
    }
}

/** Shows the whole QR image uncropped: the account name and number printed on it matter too. */
@Composable
private fun PaymentQr(url: String, contentDescription: String) {
    SubcomposeAsyncImage(
        model = url,
        contentDescription = contentDescription,
        contentScale = ContentScale.Fit,
        modifier = Modifier.fillMaxWidth().height(360.dp),
        loading = {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator(Modifier.size(24.dp), strokeWidth = 2.dp, color = Brand)
            }
        },
        error = {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                Text(
                    "The QR code couldn't load. Check your connection and try again.",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = TextAlign.Center,
                )
            }
        },
    )
}

@Composable
private fun OrderPlacedDialog(total: Double, onlinePayment: Boolean, onTrack: () -> Unit) {
    AlertDialog(
        onDismissRequest = onTrack,
        containerColor = Color.White,
        icon = {
            Box(Modifier.size(64.dp).background(SuccessSoft, CircleShape), contentAlignment = Alignment.Center) {
                Icon(Icons.Rounded.CheckCircle, contentDescription = null, tint = Success, modifier = Modifier.size(36.dp))
            }
        },
        title = { Text("Order placed!", textAlign = TextAlign.Center) },
        text = {
            Text(
                if (onlinePayment) {
                    "We received your ${total.toPeso()} order. We'll verify your payment, then start preparing it."
                } else {
                    "We received your ${total.toPeso()} order. Please prepare the exact cash for the rider."
                },
                textAlign = TextAlign.Center,
            )
        },
        confirmButton = {
            TextButton(onClick = onTrack) { Text("Track my order", color = Brand, fontWeight = FontWeight.Bold) }
        },
    )
}
