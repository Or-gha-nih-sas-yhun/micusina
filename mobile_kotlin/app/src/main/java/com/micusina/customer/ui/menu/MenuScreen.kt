package com.micusina.customer.ui.menu

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.layout.windowInsetsPadding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.SearchOff
import androidx.compose.material.icons.rounded.Add
import androidx.compose.material.icons.rounded.Close
import androidx.compose.material.icons.rounded.Search
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FilledTonalButton
import androidx.compose.material3.FilterChip
import androidx.compose.material3.FilterChipDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalBottomSheet
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.SnackbarDuration
import androidx.compose.material3.SnackbarResult
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.material3.rememberModalBottomSheetState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.micusina.customer.data.model.Food
import com.micusina.customer.ui.components.EmptyState
import com.micusina.customer.ui.components.ErrorBanner
import com.micusina.customer.ui.components.FoodImage
import com.micusina.customer.ui.components.LoadingState
import com.micusina.customer.ui.components.LocalSnackbarHostState
import com.micusina.customer.ui.components.PrimaryButton
import com.micusina.customer.ui.components.QuantityStepper
import com.micusina.customer.ui.components.StatusPill
import com.micusina.customer.ui.components.appViewModel
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.BrandSoft
import com.micusina.customer.ui.theme.Danger
import com.micusina.customer.ui.theme.Hairline
import com.micusina.customer.ui.theme.PriceTextStyle
import com.micusina.customer.ui.theme.Success
import com.micusina.customer.ui.theme.Warning
import com.micusina.customer.ui.ManilaZone
import com.micusina.customer.ui.toPeso
import java.time.LocalTime

private const val LOW_STOCK = 5

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MenuScreen(onOpenCart: () -> Unit) {
    val viewModel = appViewModel { MenuViewModel(it) }
    val state by viewModel.state.collectAsStateWithLifecycle()
    val cart by viewModel.cart.collectAsStateWithLifecycle()
    val firstName by viewModel.firstName.collectAsStateWithLifecycle()
    val inCart = remember(cart) { cart.groupBy { it.foodId }.mapValues { (_, rows) -> rows.sumOf { it.quantity } } }
    val snackbar = LocalSnackbarHostState.current
    var selectedFoodId by rememberSaveable { mutableStateOf<Int?>(null) }
    val selectedFood = state.foods.firstOrNull { it.id == selectedFoodId }

    LaunchedEffect(viewModel) {
        viewModel.messages.collect { message ->
            when (message) {
                is MenuMessage.Added -> {
                    selectedFoodId = null
                    val result = snackbar.showSnackbar(message.text, actionLabel = "View cart", duration = SnackbarDuration.Short)
                    if (result == SnackbarResult.ActionPerformed) onOpenCart()
                }
                is MenuMessage.Failed -> snackbar.showSnackbar(message.text)
            }
        }
    }

    PullToRefreshBox(
        isRefreshing = state.refreshing,
        onRefresh = viewModel::refresh,
        modifier = Modifier.fillMaxSize(),
    ) {
        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(bottom = 24.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            item(key = "header") {
                MenuHeader(
                    firstName = firstName,
                    query = state.query,
                    onQueryChange = viewModel::setQuery,
                )
            }
            if (state.categories.isNotEmpty()) {
                item(key = "categories") {
                    CategoryChips(
                        categories = state.categories,
                        selected = state.category,
                        onSelect = viewModel::setCategory,
                    )
                }
            }
            state.error?.let { error ->
                item(key = "error") {
                    ErrorBanner(error, Modifier.padding(horizontal = 16.dp), onRetry = viewModel::refresh)
                }
            }
            when {
                state.loading -> item(key = "loading") { LoadingState() }
                state.visibleFoods.isEmpty() && state.error == null -> item(key = "empty") {
                    EmptyState(
                        icon = Icons.Outlined.SearchOff,
                        title = if (state.foods.isEmpty()) "The menu is empty" else "No dishes found",
                        body = if (state.foods.isEmpty()) {
                            "Check back soon for today's dishes."
                        } else {
                            "Try a different search or category."
                        },
                    )
                }
                else -> items(state.visibleFoods, key = { it.id }) { food ->
                    FoodCard(
                        food = food,
                        inCart = inCart[food.id] ?: 0,
                        adding = state.addingFoodId == food.id,
                        onOpen = { selectedFoodId = food.id },
                        onAdd = { viewModel.addToCart(food, 1) },
                        modifier = Modifier.padding(horizontal = 16.dp).animateItem(),
                    )
                }
            }
        }
    }

    if (selectedFood != null) {
        FoodDetailSheet(
            food = selectedFood,
            inCart = inCart[selectedFood.id] ?: 0,
            adding = state.addingFoodId == selectedFood.id,
            onAdd = { quantity -> viewModel.addToCart(selectedFood, quantity) },
            onDismiss = { selectedFoodId = null },
        )
    }
}

@Composable
private fun MenuHeader(firstName: String, query: String, onQueryChange: (String) -> Unit) {
    val greeting = remember {
        when (LocalTime.now(ManilaZone).hour) {
            in 5..11 -> "Good morning"
            in 12..17 -> "Good afternoon"
            else -> "Good evening"
        }
    }
    Column(
        Modifier
            .windowInsetsPadding(WindowInsets.statusBars)
            .padding(start = 16.dp, end = 16.dp, top = 16.dp),
    ) {
        Text(
            if (firstName.isNotBlank()) "$greeting, $firstName" else greeting,
            style = MaterialTheme.typography.titleMedium,
            color = Brand,
        )
        Spacer(Modifier.height(2.dp))
        Text("What are you craving?", style = MaterialTheme.typography.headlineMedium)
        Spacer(Modifier.height(14.dp))
        OutlinedTextField(
            value = query,
            onValueChange = onQueryChange,
            placeholder = { Text("Search the menu") },
            leadingIcon = { Icon(Icons.Rounded.Search, contentDescription = null) },
            trailingIcon = {
                if (query.isNotEmpty()) {
                    IconButton(onClick = { onQueryChange("") }) {
                        Icon(Icons.Rounded.Close, contentDescription = "Clear search")
                    }
                }
            },
            singleLine = true,
            shape = CircleShape,
            colors = OutlinedTextFieldDefaults.colors(
                unfocusedContainerColor = Color.White,
                focusedContainerColor = Color.White,
                unfocusedBorderColor = Hairline,
            ),
            modifier = Modifier.fillMaxWidth(),
        )
    }
}

@Composable
private fun CategoryChips(categories: List<String>, selected: String?, onSelect: (String?) -> Unit) {
    LazyRow(
        contentPadding = PaddingValues(horizontal = 16.dp),
        horizontalArrangement = Arrangement.spacedBy(8.dp),
    ) {
        item { CategoryChip("All", selected == null) { onSelect(null) } }
        items(categories) { category -> CategoryChip(category, selected == category) { onSelect(category) } }
    }
}

@Composable
private fun CategoryChip(label: String, selected: Boolean, onClick: () -> Unit) {
    FilterChip(
        selected = selected,
        onClick = onClick,
        label = { Text(label, fontWeight = FontWeight.Bold) },
        shape = CircleShape,
        colors = FilterChipDefaults.filterChipColors(
            containerColor = Color.White,
            labelColor = Brand,
            selectedContainerColor = Brand,
            selectedLabelColor = Color.White,
        ),
        border = FilterChipDefaults.filterChipBorder(
            enabled = true,
            selected = selected,
            borderColor = Hairline,
            selectedBorderColor = Brand,
        ),
    )
}

@Composable
private fun StockLabel(stock: Int) {
    val (text, color) = when {
        stock <= 0 -> "Sold out" to Danger
        stock <= LOW_STOCK -> "Only $stock left" to Warning
        else -> "$stock available" to Success
    }
    Text(text, style = MaterialTheme.typography.labelMedium, color = color)
}

@Composable
private fun FoodCard(
    food: Food,
    inCart: Int,
    adding: Boolean,
    onOpen: () -> Unit,
    onAdd: () -> Unit,
    modifier: Modifier = Modifier,
) {
    val canAdd = food.stock - inCart > 0
    Card(
        modifier = modifier.fillMaxWidth().clickable(onClick = onOpen),
        shape = MaterialTheme.shapes.large,
        colors = CardDefaults.cardColors(containerColor = Color.White),
        border = BorderStroke(1.dp, Hairline),
    ) {
        Row(Modifier.padding(12.dp), horizontalArrangement = Arrangement.spacedBy(14.dp)) {
            FoodImage(food.imageUrl, contentDescription = food.title, modifier = Modifier.size(108.dp))
            Column(Modifier.weight(1f).height(108.dp)) {
                Text(food.title, style = MaterialTheme.typography.titleMedium, maxLines = 2, overflow = TextOverflow.Ellipsis)
                if (!food.detail.isNullOrBlank()) {
                    Text(
                        food.detail,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis,
                    )
                }
                Spacer(Modifier.weight(1f))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Column(Modifier.weight(1f)) {
                        Text(food.price.toPeso(), style = MaterialTheme.typography.titleMedium.merge(PriceTextStyle), color = Brand)
                        if (inCart > 0) {
                            Text("$inCart in cart", style = MaterialTheme.typography.labelMedium, color = Brand)
                        } else {
                            StockLabel(food.stock)
                        }
                    }
                    FilledTonalButton(
                        onClick = onAdd,
                        enabled = canAdd && !adding,
                        contentPadding = PaddingValues(horizontal = 14.dp),
                        modifier = Modifier.height(38.dp),
                    ) {
                        if (adding) {
                            CircularProgressIndicator(Modifier.size(16.dp), strokeWidth = 2.dp)
                        } else {
                            Icon(Icons.Rounded.Add, contentDescription = null, modifier = Modifier.size(18.dp))
                            Spacer(Modifier.size(4.dp))
                            Text(if (food.stock <= 0) "Sold out" else "Add")
                        }
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun FoodDetailSheet(
    food: Food,
    inCart: Int,
    adding: Boolean,
    onAdd: (Int) -> Unit,
    onDismiss: () -> Unit,
) {
    val remaining = (food.stock - inCart).coerceAtLeast(0)
    var quantity by rememberSaveable(food.id) { mutableIntStateOf(1) }
    ModalBottomSheet(
        onDismissRequest = onDismiss,
        sheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true),
        containerColor = Color.White,
    ) {
        Column(
            Modifier
                .verticalScroll(rememberScrollState())
                .padding(horizontal = 20.dp)
                .navigationBarsPadding()
                .padding(bottom = 16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            FoodImage(food.imageUrl, contentDescription = food.title, modifier = Modifier.fillMaxWidth().aspectRatio(16f / 10f))
            if (food.category != UNCATEGORISED) StatusPill(food.category, BrandSoft, Brand)
            Text(food.title, style = MaterialTheme.typography.headlineSmall)
            Text(food.price.toPeso(), style = MaterialTheme.typography.titleLarge.merge(PriceTextStyle), color = Brand)
            if (!food.detail.isNullOrBlank()) {
                Text(food.detail, style = MaterialTheme.typography.bodyLarge, color = MaterialTheme.colorScheme.onSurfaceVariant)
            }
            Row(verticalAlignment = Alignment.CenterVertically) {
                Column(Modifier.weight(1f)) {
                    StockLabel(food.stock)
                    if (inCart > 0) Text("$inCart already in your cart", style = MaterialTheme.typography.labelMedium, color = Brand)
                }
                if (remaining > 0) {
                    QuantityStepper(
                        quantity = quantity.coerceAtMost(remaining),
                        onDecrease = { quantity-- },
                        onIncrease = { quantity++ },
                        canIncrease = quantity < remaining,
                    )
                }
            }
            Spacer(Modifier.height(4.dp))
            val quantityToAdd = quantity.coerceIn(1, remaining.coerceAtLeast(1))
            PrimaryButton(
                text = when {
                    food.stock <= 0 -> "Sold out"
                    remaining <= 0 -> "All available stock is in your cart"
                    else -> "Add $quantityToAdd to cart · ${(food.price * quantityToAdd).toPeso()}"
                },
                onClick = { onAdd(quantityToAdd) },
                loading = adding,
                enabled = remaining > 0,
            )
        }
    }
}
