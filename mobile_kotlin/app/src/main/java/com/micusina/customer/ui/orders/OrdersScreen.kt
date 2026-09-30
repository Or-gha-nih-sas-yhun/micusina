package com.micusina.customer.ui.orders

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.Payments
import androidx.compose.material.icons.outlined.Place
import androidx.compose.material.icons.rounded.Check
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.compose.LocalLifecycleOwner
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.repeatOnLifecycle
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
import com.micusina.customer.ui.formatServerTimestamp
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.Danger
import com.micusina.customer.ui.theme.DangerSoft
import com.micusina.customer.ui.theme.Hairline
import com.micusina.customer.ui.theme.Info
import com.micusina.customer.ui.theme.InfoSoft
import com.micusina.customer.ui.theme.PriceTextStyle
import com.micusina.customer.ui.theme.Success
import com.micusina.customer.ui.theme.SuccessSoft
import com.micusina.customer.ui.theme.Warning
import com.micusina.customer.ui.theme.WarningSoft
import com.micusina.customer.ui.toPeso
import kotlinx.coroutines.delay

private const val AUTO_REFRESH_MILLIS = 30_000L

@Composable
fun OrdersScreen(onBrowseMenu: () -> Unit) {
    val viewModel = appViewModel { OrdersViewModel(it) }
    val state by viewModel.state.collectAsStateWithLifecycle()
    val snackbar = LocalSnackbarHostState.current
    var cancelTarget by rememberConfirmTarget<OrderGroup>()
    val lifecycleOwner = LocalLifecycleOwner.current

    // Keep delivery status fresh while the customer is watching this screen.
    LaunchedEffect(lifecycleOwner) {
        lifecycleOwner.repeatOnLifecycle(Lifecycle.State.RESUMED) {
            while (true) {
                viewModel.load()
                delay(AUTO_REFRESH_MILLIS)
            }
        }
    }
    LaunchedEffect(viewModel) {
        viewModel.messages.collect { snackbar.showSnackbar(it) }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader("My orders", "Track your deliveries in real time")
        PullToRefreshBox(
            isRefreshing = state.refreshing,
            onRefresh = { viewModel.load(userInitiated = true) },
            modifier = Modifier.weight(1f),
        ) {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 24.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                state.error?.let { error ->
                    item(key = "error") { ErrorBanner(error, onRetry = { viewModel.load(userInitiated = true) }) }
                }
                when {
                    !state.loaded -> item(key = "loading") { LoadingState() }
                    state.groups.isEmpty() && state.error == null -> item(key = "empty") {
                        EmptyState(
                            icon = Icons.AutoMirrored.Outlined.ReceiptLong,
                            title = "No orders yet",
                            body = "When you place an order, you can follow it here until it reaches your door.",
                            action = {
                                PrimaryButton("Order something", onClick = onBrowseMenu, modifier = Modifier.padding(horizontal = 24.dp))
                            },
                        )
                    }
                    else -> items(state.groups, key = { it.key }) { group ->
                        OrderCard(
                            group = group,
                            cancelling = state.cancellingId == group.id,
                            onCancel = { cancelTarget = group },
                            modifier = Modifier.animateItem(),
                        )
                    }
                }
            }
        }
    }

    cancelTarget?.let { group ->
        ConfirmDialog(
            title = "Cancel order #${group.id}?",
            message = if (group.orders.size == 1) {
                "Your ${group.orders.single().title} order will be cancelled."
            } else {
                "All ${group.orders.size} dishes in this order will be cancelled."
            },
            confirmLabel = "Cancel order",
            onConfirm = {
                cancelTarget = null
                viewModel.cancel(group)
            },
            onDismiss = { cancelTarget = null },
        )
    }
}

@Composable
private fun OrderCard(group: OrderGroup, cancelling: Boolean, onCancel: () -> Unit, modifier: Modifier = Modifier) {
    SectionCard(modifier) {
        Row(verticalAlignment = Alignment.Top) {
            Column(Modifier.weight(1f)) {
                Text("Order #${group.id}", style = MaterialTheme.typography.titleMedium)
                val placed = formatServerTimestamp(group.createdAt)
                if (placed.isNotEmpty()) {
                    Text(placed, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                }
            }
            DeliveryStatusPill(group.stage)
        }

        if (group.stage != DeliveryStage.Canceled) DeliveryProgress(group.stage)

        Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
            group.orders.forEach { order ->
                Row {
                    Text("${order.quantity} × ${order.title}", style = MaterialTheme.typography.bodyMedium, modifier = Modifier.weight(1f))
                    Text(order.price.toPeso(), style = MaterialTheme.typography.bodyMedium.merge(PriceTextStyle.copy(fontWeight = null)))
                }
            }
        }
        HorizontalDivider(color = Hairline)
        Row(verticalAlignment = Alignment.CenterVertically) {
            Text("Total", style = MaterialTheme.typography.titleMedium, modifier = Modifier.weight(1f))
            Text(group.total.toPeso(), style = MaterialTheme.typography.titleMedium.merge(PriceTextStyle), color = Brand)
        }

        val payment = listOfNotNull(group.paymentMethod, group.paymentStatus).joinToString(" · ")
        if (payment.isNotEmpty()) {
            DetailLine(Icons.Outlined.Payments, payment + (group.paymentReference?.let { " · Ref $it" } ?: ""))
        }
        group.address?.takeIf { it.isNotBlank() }?.let { DetailLine(Icons.Outlined.Place, it) }

        if (group.canCancel) {
            OutlinedButton(
                onClick = onCancel,
                enabled = !cancelling,
                modifier = Modifier.fillMaxWidth().heightIn(min = 44.dp),
                shape = CircleShape,
            ) {
                if (cancelling) {
                    CircularProgressIndicator(Modifier.size(16.dp), strokeWidth = 2.dp, color = Danger)
                } else {
                    Text("Cancel order", color = Danger, fontWeight = FontWeight.Bold)
                }
            }
        }
    }
}

@Composable
private fun DetailLine(icon: ImageVector, text: String) {
    Row(verticalAlignment = Alignment.Top) {
        Icon(icon, contentDescription = null, tint = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.size(18.dp))
        Spacer(Modifier.size(8.dp))
        Text(text, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
    }
}

@Composable
private fun DeliveryStatusPill(stage: DeliveryStage) {
    val (container, content) = when (stage) {
        DeliveryStage.Received -> WarningSoft to Warning
        DeliveryStage.OnTheWay -> InfoSoft to Info
        DeliveryStage.Delivered -> SuccessSoft to Success
        DeliveryStage.Canceled -> DangerSoft to Danger
    }
    StatusPill(stage.label, container, content)
}

/** Received -> On the way -> Delivered. */
@Composable
private fun DeliveryProgress(stage: DeliveryStage) {
    val steps = listOf(DeliveryStage.Received, DeliveryStage.OnTheWay, DeliveryStage.Delivered)
    val current = steps.indexOf(stage)
    Column {
        Row(verticalAlignment = Alignment.CenterVertically) {
            steps.forEachIndexed { index, _ ->
                val done = index <= current
                Box(
                    Modifier
                        .size(22.dp)
                        .background(if (done) Brand else Hairline, CircleShape),
                    contentAlignment = Alignment.Center,
                ) {
                    if (done) Icon(Icons.Rounded.Check, contentDescription = null, tint = Color.White, modifier = Modifier.size(14.dp))
                }
                if (index < steps.lastIndex) {
                    Box(
                        Modifier
                            .weight(1f)
                            .height(3.dp)
                            .padding(horizontal = 4.dp)
                            .background(if (index < current) Brand else Hairline, CircleShape),
                    )
                }
            }
        }
        Spacer(Modifier.height(6.dp))
        Row {
            steps.forEachIndexed { index, step ->
                Text(
                    step.label,
                    style = MaterialTheme.typography.labelSmall,
                    color = if (index <= current) Brand else MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = when (index) {
                        0 -> TextAlign.Start
                        steps.lastIndex -> TextAlign.End
                        else -> TextAlign.Center
                    },
                    modifier = Modifier.weight(1f),
                )
            }
        }
    }
}
