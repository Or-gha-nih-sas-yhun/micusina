package com.micusina.customer.ui.reservations

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.EventSeat
import androidx.compose.material.icons.outlined.Groups
import androidx.compose.material.icons.outlined.Payments
import androidx.compose.material.icons.outlined.Schedule
import androidx.compose.material.icons.rounded.Add
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExtendedFloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.LifecycleResumeEffect
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.micusina.customer.data.model.Reservation
import com.micusina.customer.ui.components.ConfirmDialog
import com.micusina.customer.ui.components.EmptyState
import com.micusina.customer.ui.components.ErrorBanner
import com.micusina.customer.ui.components.LoadingState
import com.micusina.customer.ui.components.LocalSnackbarHostState
import com.micusina.customer.ui.components.PrimaryButton
import com.micusina.customer.ui.components.ScreenHeader
import com.micusina.customer.ui.components.SectionCard
import com.micusina.customer.ui.components.StatusPill
import com.micusina.customer.ui.components.appViewModel
import com.micusina.customer.ui.components.rememberConfirmTarget
import com.micusina.customer.ui.formatReservationDate
import com.micusina.customer.ui.openInBrowser
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.Danger
import com.micusina.customer.ui.theme.DangerSoft
import com.micusina.customer.ui.theme.Info
import com.micusina.customer.ui.theme.InfoSoft
import com.micusina.customer.ui.theme.Muted
import com.micusina.customer.ui.theme.Success
import com.micusina.customer.ui.theme.SuccessSoft
import com.micusina.customer.ui.theme.Warning
import com.micusina.customer.ui.theme.WarningSoft
import com.micusina.customer.ui.toPeso
import kotlinx.coroutines.launch

@Composable
fun ReservationsScreen(onNewReservation: () -> Unit) {
    val viewModel = appViewModel { ReservationsViewModel(it) }
    val state by viewModel.state.collectAsStateWithLifecycle()
    val snackbar = LocalSnackbarHostState.current
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    var cancelTarget by rememberConfirmTarget<Reservation>()

    LifecycleResumeEffect(viewModel) {
        viewModel.load()
        onPauseOrDispose { }
    }
    LaunchedEffect(viewModel) {
        viewModel.messages.collect { snackbar.showSnackbar(it) }
    }

    Box(Modifier.fillMaxSize()) {
        Column(Modifier.fillMaxSize()) {
            ScreenHeader("Reservations", "Book a table at Mi Cusina")
            PullToRefreshBox(
                isRefreshing = state.refreshing,
                onRefresh = { viewModel.load(userInitiated = true) },
                modifier = Modifier.weight(1f),
            ) {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 96.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp),
                ) {
                    state.error?.let { error ->
                        item(key = "error") { ErrorBanner(error, onRetry = { viewModel.load(userInitiated = true) }) }
                    }
                    when {
                        !state.loaded -> item(key = "loading") { LoadingState() }
                        state.reservations.isEmpty() && state.error == null -> item(key = "empty") {
                            EmptyState(
                                icon = Icons.Outlined.EventSeat,
                                title = "No reservations yet",
                                body = "Reserve a table for your next celebration or family dinner.",
                            )
                        }
                        else -> items(state.reservations, key = { it.id }) { reservation ->
                            ReservationCard(
                                reservation = reservation,
                                cancelling = state.cancellingId == reservation.id,
                                onPay = { url ->
                                    if (!openInBrowser(context, url)) {
                                        scope.launch { snackbar.showSnackbar("No browser is available to open the payment page.") }
                                    }
                                },
                                onCancel = { cancelTarget = reservation },
                                modifier = Modifier.animateItem(),
                            )
                        }
                    }
                }
            }
        }
        ExtendedFloatingActionButton(
            onClick = onNewReservation,
            containerColor = Brand,
            contentColor = Color.White,
            icon = { Icon(Icons.Rounded.Add, contentDescription = null) },
            text = { Text("Reserve a table", fontWeight = FontWeight.Bold) },
            modifier = Modifier.align(Alignment.BottomEnd).padding(16.dp),
        )
    }

    cancelTarget?.let { reservation ->
        ConfirmDialog(
            title = "Cancel this reservation?",
            message = "Your table for ${formatReservationDate(reservation.date)} at ${reservation.time} will be released. " +
                "Reservation downpayments are non-refundable.",
            confirmLabel = "Cancel reservation",
            onConfirm = {
                cancelTarget = null
                viewModel.cancel(reservation)
            },
            onDismiss = { cancelTarget = null },
        )
    }
}

@Composable
private fun ReservationCard(
    reservation: Reservation,
    cancelling: Boolean,
    onPay: (String) -> Unit,
    onCancel: () -> Unit,
    modifier: Modifier = Modifier,
) {
    val stage = reservation.stage
    SectionCard(modifier) {
        Row(verticalAlignment = Alignment.Top) {
            Column(Modifier.weight(1f)) {
                Text(formatReservationDate(reservation.date), style = MaterialTheme.typography.titleMedium)
                reservation.reference?.let {
                    Text("Ref $it", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
            }
            ReservationStatusPill(stage)
        }
        Text(stage.explanation, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
        Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
            InfoLine(Icons.Outlined.Schedule, reservation.time)
            InfoLine(
                Icons.Outlined.Groups,
                (if (reservation.guest == 1) "1 guest" else "${reservation.guest} guests") +
                    reservation.displayName.takeIf { it.isNotBlank() }?.let { " · $it" }.orEmpty(),
            )
            val payment = listOfNotNull(reservation.paymentMethod, reservation.paymentStatus).joinToString(" · ")
            InfoLine(
                Icons.Outlined.Payments,
                "Downpayment ${reservation.depositAmount.toPeso()} of ${reservation.reservationPrice.toPeso()}" +
                    if (payment.isNotEmpty()) " · $payment" else "",
            )
        }
        reservation.checkoutUrl?.let { url ->
            PrimaryButton("Pay ${reservation.depositAmount.toPeso()} downpayment", onClick = { onPay(url) })
        }
        if (reservation.canCancel) {
            OutlinedButton(
                onClick = onCancel,
                enabled = !cancelling,
                modifier = Modifier.fillMaxWidth().heightIn(min = 44.dp),
                shape = CircleShape,
            ) {
                if (cancelling) {
                    CircularProgressIndicator(Modifier.size(16.dp), strokeWidth = 2.dp, color = Danger)
                } else {
                    Text("Cancel reservation", color = Danger, fontWeight = FontWeight.Bold)
                }
            }
        }
    }
}

@Composable
private fun InfoLine(icon: ImageVector, text: String) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Icon(icon, contentDescription = null, tint = Brand, modifier = Modifier.size(18.dp))
        Spacer(Modifier.size(10.dp))
        Text(text, style = MaterialTheme.typography.bodyMedium)
    }
}

@Composable
private fun ReservationStatusPill(stage: ReservationStage) {
    val (container, content) = when (stage) {
        ReservationStage.AwaitingPayment -> WarningSoft to Warning
        ReservationStage.Pending -> InfoSoft to Info
        ReservationStage.Approved -> SuccessSoft to Success
        ReservationStage.Completed -> Color(0xFFEFEFF3) to Muted
        ReservationStage.Canceled -> DangerSoft to Danger
    }
    StatusPill(stage.label, container, content)
}
