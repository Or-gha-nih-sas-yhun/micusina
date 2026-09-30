package com.micusina.customer.ui.cart

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.DeleteOutline
import androidx.compose.material.icons.outlined.ShoppingBag
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.micusina.customer.data.SiteUrls
import com.micusina.customer.data.model.CartItem
import com.micusina.customer.ui.components.EmptyState
import com.micusina.customer.ui.components.ErrorBanner
import com.micusina.customer.ui.components.FoodImage
import com.micusina.customer.ui.components.LoadingState
import com.micusina.customer.ui.components.LocalSnackbarHostState
import com.micusina.customer.ui.components.PrimaryButton
import com.micusina.customer.ui.components.QuantityStepper
import com.micusina.customer.ui.components.ScreenHeader
import com.micusina.customer.ui.components.appViewModel
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.Hairline
import com.micusina.customer.ui.theme.PriceTextStyle
import com.micusina.customer.ui.toPeso

@Composable
fun CartScreen(onBrowseMenu: () -> Unit, onCheckout: () -> Unit) {
    val viewModel = appViewModel { CartViewModel(it) }
    val items by viewModel.items.collectAsStateWithLifecycle()
    val state by viewModel.state.collectAsStateWithLifecycle()
    val snackbar = LocalSnackbarHostState.current
    val itemCount = items.sumOf { it.quantity }
    val total = items.sumOf { it.price }

    LaunchedEffect(viewModel) {
        viewModel.messages.collect { snackbar.showSnackbar(it) }
    }

    Column(Modifier.fillMaxSize()) {
        ScreenHeader(
            title = "Your cart",
            subtitle = when (itemCount) {
                0 -> "Nothing here yet"
                1 -> "1 item"
                else -> "$itemCount items"
            },
        )

        PullToRefreshBox(
            isRefreshing = state.refreshing,
            onRefresh = viewModel::refresh,
            modifier = Modifier.weight(1f),
        ) {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 16.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                state.error?.let { error ->
                    item(key = "error") { ErrorBanner(error, onRetry = viewModel::refresh) }
                }
                when {
                    state.loading && items.isEmpty() -> item(key = "loading") { LoadingState() }
                    items.isEmpty() -> item(key = "empty") {
                        EmptyState(
                            icon = Icons.Outlined.ShoppingBag,
                            title = "Your cart is empty",
                            body = "Add your Mi Cusina favorites from the menu and they'll show up here.",
                            action = {
                                PrimaryButton("Browse the menu", onClick = onBrowseMenu, modifier = Modifier.padding(horizontal = 24.dp))
                            },
                        )
                    }
                    else -> items(items, key = { it.id }) { item ->
                        CartRow(
                            item = item,
                            busy = item.id in state.busyItemIds,
                            onQuantityChange = { viewModel.setQuantity(item, it) },
                            onRemove = { viewModel.remove(item) },
                            modifier = Modifier.animateItem(),
                        )
                    }
                }
            }
        }

        if (items.isNotEmpty()) {
            Surface(color = Color.White, shadowElevation = 8.dp) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Text("Total", style = MaterialTheme.typography.titleMedium, modifier = Modifier.weight(1f))
                        Text(total.toPeso(), style = MaterialTheme.typography.titleLarge.merge(PriceTextStyle), color = Brand)
                    }
                    PrimaryButton("Proceed to checkout", onClick = onCheckout, enabled = state.busyItemIds.isEmpty())
                }
            }
        }
    }
}

@Composable
private fun CartRow(
    item: CartItem,
    busy: Boolean,
    onQuantityChange: (Int) -> Unit,
    onRemove: () -> Unit,
    modifier: Modifier = Modifier,
) {
    Card(
        modifier = modifier.fillMaxWidth(),
        shape = MaterialTheme.shapes.large,
        colors = CardDefaults.cardColors(containerColor = Color.White),
        border = BorderStroke(1.dp, Hairline),
    ) {
        Row(Modifier.padding(12.dp), horizontalArrangement = Arrangement.spacedBy(14.dp)) {
            FoodImage(SiteUrls.foodImage(item.image), contentDescription = item.title, modifier = Modifier.size(84.dp))
            Column(Modifier.weight(1f)) {
                Row(verticalAlignment = Alignment.Top) {
                    Column(Modifier.weight(1f)) {
                        Text(item.title, style = MaterialTheme.typography.titleMedium, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        Text(
                            "${item.unitPrice.toPeso()} each",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                    IconButton(onClick = onRemove, enabled = !busy, modifier = Modifier.size(36.dp)) {
                        Icon(Icons.Outlined.DeleteOutline, contentDescription = "Remove ${item.title}")
                    }
                }
                Spacer(Modifier.height(8.dp))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    QuantityStepper(
                        quantity = item.quantity,
                        onDecrease = { onQuantityChange(item.quantity - 1) },
                        onIncrease = { onQuantityChange(item.quantity + 1) },
                        enabled = !busy,
                    )
                    Spacer(Modifier.weight(1f))
                    Text(item.price.toPeso(), style = MaterialTheme.typography.titleMedium.merge(PriceTextStyle))
                }
            }
        }
    }
}

@Composable
internal fun OrderSummaryLines(items: List<CartItem>) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        items.forEach { item ->
            Row {
                Text(
                    "${item.quantity} × ${item.title}",
                    style = MaterialTheme.typography.bodyMedium,
                    modifier = Modifier.weight(1f),
                )
                Text(item.price.toPeso(), style = MaterialTheme.typography.bodyMedium.merge(PriceTextStyle.copy(fontWeight = null)))
            }
        }
        HorizontalDivider(color = Hairline)
        Row {
            Text("Total", style = MaterialTheme.typography.titleMedium, modifier = Modifier.weight(1f))
            Text(items.sumOf { it.price }.toPeso(), style = MaterialTheme.typography.titleMedium.merge(PriceTextStyle), color = Brand)
        }
    }
}
