package com.micusina.customer.ui.navigation

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.consumeWindowInsets
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.layout.windowInsetsTopHeight
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.EventSeat
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.automirrored.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.RestaurantMenu
import androidx.compose.material.icons.outlined.ShoppingBag
import androidx.compose.material.icons.rounded.EventSeat
import androidx.compose.material.icons.rounded.Person
import androidx.compose.material.icons.automirrored.rounded.ReceiptLong
import androidx.compose.material.icons.rounded.RestaurantMenu
import androidx.compose.material.icons.rounded.ShoppingBag
import androidx.compose.material3.Badge
import androidx.compose.material3.BadgedBox
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.CompositionLocalProvider
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.navigation.NavDestination.Companion.hasRoute
import androidx.navigation.NavDestination.Companion.hierarchy
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import com.micusina.customer.MiCusinaApplication
import com.micusina.customer.data.SessionState
import com.micusina.customer.ui.account.AccountScreen
import com.micusina.customer.ui.auth.AuthFlow
import com.micusina.customer.ui.cart.CartScreen
import com.micusina.customer.ui.cart.CheckoutScreen
import com.micusina.customer.ui.components.BrandMark
import com.micusina.customer.ui.components.LocalSnackbarHostState
import com.micusina.customer.ui.menu.MenuScreen
import com.micusina.customer.ui.orders.OrdersScreen
import com.micusina.customer.ui.reservations.NewReservationScreen
import com.micusina.customer.ui.reservations.ReservationsScreen
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.BrandSoft
import kotlinx.serialization.Serializable

@Serializable data object MenuRoute
@Serializable data object CartRoute
@Serializable data object CheckoutRoute
@Serializable data object OrdersRoute
@Serializable data object ReservationsRoute
@Serializable data object NewReservationRoute
@Serializable data object AccountRoute

private enum class Tab(val route: Any, val label: String, val icon: ImageVector, val selectedIcon: ImageVector) {
    Menu(MenuRoute, "Menu", Icons.Outlined.RestaurantMenu, Icons.Rounded.RestaurantMenu),
    Cart(CartRoute, "Cart", Icons.Outlined.ShoppingBag, Icons.Rounded.ShoppingBag),
    Orders(OrdersRoute, "Orders", Icons.AutoMirrored.Outlined.ReceiptLong, Icons.AutoMirrored.Rounded.ReceiptLong),
    Reserve(ReservationsRoute, "Reserve", Icons.Outlined.EventSeat, Icons.Rounded.EventSeat),
    Account(AccountRoute, "Account", Icons.Outlined.Person, Icons.Rounded.Person),
}

@Composable
fun MiCusinaApp() {
    val repository = (LocalContext.current.applicationContext as MiCusinaApplication).container.repository
    val session by repository.session.collectAsStateWithLifecycle()

    when (val state = session) {
        SessionState.Restoring -> SplashScreen()
        is SessionState.SignedOut -> AuthFlow(notice = state.notice)
        is SessionState.SignedIn -> SignedInApp()
    }
}

@Composable
private fun SplashScreen() {
    Column(
        Modifier.fillMaxSize(),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center,
    ) {
        BrandMark(size = 88.dp)
        Spacer(Modifier.size(18.dp))
        Text("Mi Cusina", style = MaterialTheme.typography.headlineMedium)
        Spacer(Modifier.size(28.dp))
        CircularProgressIndicator(color = Brand, modifier = Modifier.size(28.dp), strokeWidth = 3.dp)
    }
}

@Composable
private fun SignedInApp() {
    val repository = (LocalContext.current.applicationContext as MiCusinaApplication).container.repository
    val cart by repository.cart.collectAsStateWithLifecycle()
    val cartCount = cart.sumOf { it.quantity }
    val navController = rememberNavController()
    val snackbarHostState = remember { SnackbarHostState() }
    val backStackEntry by navController.currentBackStackEntryAsState()
    val destination = backStackEntry?.destination
    val showBottomBar = destination == null || Tab.entries.any { tab -> destination.hasRoute(tab.route::class) }

    Scaffold(
        contentWindowInsets = WindowInsets(0),
        snackbarHost = { SnackbarHost(snackbarHostState) },
        bottomBar = {
            if (showBottomBar) {
                NavigationBar(containerColor = Color.White, tonalElevation = 0.dp) {
                    Tab.entries.forEach { tab ->
                        val selected = destination?.hierarchy?.any { it.hasRoute(tab.route::class) } == true
                        NavigationBarItem(
                            selected = selected,
                            onClick = { navController.navigateToTab(tab.route) },
                            icon = {
                                BadgedBox(
                                    badge = {
                                        if (tab == Tab.Cart && cartCount > 0) {
                                            Badge(containerColor = Brand) { Text(if (cartCount > 99) "99+" else "$cartCount") }
                                        }
                                    },
                                ) {
                                    Icon(if (selected) tab.selectedIcon else tab.icon, contentDescription = null)
                                }
                            },
                            label = { Text(tab.label) },
                            colors = NavigationBarItemDefaults.colors(
                                selectedIconColor = Brand,
                                selectedTextColor = Brand,
                                indicatorColor = BrandSoft,
                            ),
                        )
                    }
                }
            }
        },
    ) { padding ->
        CompositionLocalProvider(LocalSnackbarHostState provides snackbarHostState) {
            Box(Modifier.fillMaxSize()) {
                NavHost(
                    navController = navController,
                    startDestination = MenuRoute,
                    modifier = Modifier.padding(padding).consumeWindowInsets(padding),
                ) {
                    composable<MenuRoute> {
                        MenuScreen(onOpenCart = { navController.navigateToTab(CartRoute) })
                    }
                    composable<CartRoute> {
                        CartScreen(
                            onBrowseMenu = { navController.navigateToTab(MenuRoute) },
                            onCheckout = { navController.navigate(CheckoutRoute) },
                        )
                    }
                    composable<CheckoutRoute> {
                        CheckoutScreen(
                            onBack = { navController.popBackStack() },
                            onOrderPlaced = {
                                navController.navigate(OrdersRoute) {
                                    popUpTo(navController.graph.findStartDestination().id)
                                    launchSingleTop = true
                                }
                            },
                        )
                    }
                    composable<OrdersRoute> {
                        OrdersScreen(onBrowseMenu = { navController.navigateToTab(MenuRoute) })
                    }
                    composable<ReservationsRoute> {
                        ReservationsScreen(onNewReservation = { navController.navigate(NewReservationRoute) })
                    }
                    composable<NewReservationRoute> {
                        NewReservationScreen(onBack = { navController.popBackStack() })
                    }
                    composable<AccountRoute> {
                        AccountScreen(
                            onOpenOrders = { navController.navigateToTab(OrdersRoute) },
                            onOpenReservations = { navController.navigateToTab(ReservationsRoute) },
                        )
                    }
                }
                // Scrolled lists pass under this instead of under the transparent status bar icons.
                Box(
                    Modifier
                        .fillMaxWidth()
                        .windowInsetsTopHeight(WindowInsets.statusBars)
                        .background(MaterialTheme.colorScheme.background),
                )
            }
        }
    }
}

private fun NavHostController.navigateToTab(route: Any) {
    navigate(route) {
        popUpTo(graph.findStartDestination().id) { saveState = true }
        launchSingleTop = true
        restoreState = true
    }
}
