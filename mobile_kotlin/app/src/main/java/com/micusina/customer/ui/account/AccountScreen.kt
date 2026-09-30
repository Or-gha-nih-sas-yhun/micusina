package com.micusina.customer.ui.account

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
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
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.automirrored.outlined.ReceiptLong
import androidx.compose.material.icons.automirrored.rounded.KeyboardArrowRight
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.EventSeat
import androidx.compose.material.icons.outlined.Language
import androidx.compose.material.icons.outlined.Phone
import androidx.compose.material.icons.outlined.Place
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.micusina.customer.BuildConfig
import com.micusina.customer.MiCusinaApplication
import com.micusina.customer.data.PhilippinePhone
import com.micusina.customer.data.SessionState
import com.micusina.customer.data.SiteUrls
import com.micusina.customer.data.remote.ApiException
import com.micusina.customer.ui.components.ConfirmDialog
import com.micusina.customer.ui.components.ScreenHeader
import com.micusina.customer.ui.components.SectionCard
import com.micusina.customer.ui.openInBrowser
import com.micusina.customer.ui.theme.Brand
import com.micusina.customer.ui.theme.BrandSoft
import com.micusina.customer.ui.theme.Danger
import com.micusina.customer.ui.theme.Hairline
import kotlinx.coroutines.launch

@Composable
fun AccountScreen(onOpenOrders: () -> Unit, onOpenReservations: () -> Unit) {
    val context = LocalContext.current
    val repository = (context.applicationContext as MiCusinaApplication).container.repository
    val session by repository.session.collectAsStateWithLifecycle()
    val user = (session as? SessionState.SignedIn)?.user ?: return
    val scope = rememberCoroutineScope()
    var confirmSignOut by rememberSaveable { mutableStateOf(false) }

    // Pick up profile changes made on the website or by a recent checkout.
    LaunchedEffect(Unit) {
        try {
            repository.refreshProfile()
        } catch (_: ApiException) {
        }
    }

    Column(
        Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState()),
    ) {
        ScreenHeader("Account")
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(16.dp)) {
            SectionCard {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(
                        Modifier.size(60.dp).background(BrandSoft, CircleShape),
                        contentAlignment = Alignment.Center,
                    ) {
                        Text(
                            initials(user.name),
                            style = MaterialTheme.typography.titleLarge,
                            color = Brand,
                        )
                    }
                    Spacer(Modifier.size(14.dp))
                    Column(Modifier.weight(1f)) {
                        Text(user.name, style = MaterialTheme.typography.titleLarge)
                        Text("Customer", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    }
                }
                HorizontalDivider(color = Hairline)
                ProfileLine(Icons.Outlined.Email, user.email)
                user.phone?.takeIf { it.isNotBlank() }?.let { ProfileLine(Icons.Outlined.Phone, PhilippinePhone.toLocal(it)) }
                user.address?.takeIf { it.isNotBlank() }?.let { ProfileLine(Icons.Outlined.Place, it) }
            }

            SectionCard(contentPadding = PaddingValues(vertical = 4.dp), spacing = 0.dp) {
                NavRow(Icons.AutoMirrored.Outlined.ReceiptLong, "My orders", onOpenOrders)
                HorizontalDivider(color = Hairline, modifier = Modifier.padding(start = 56.dp))
                NavRow(Icons.Outlined.EventSeat, "My reservations", onOpenReservations)
                HorizontalDivider(color = Hairline, modifier = Modifier.padding(start = 56.dp))
                NavRow(Icons.Outlined.Language, "Visit the Mi Cusina website") { openInBrowser(context, SiteUrls.origin) }
            }

            OutlinedButton(
                onClick = { confirmSignOut = true },
                modifier = Modifier.fillMaxWidth().heightIn(min = 52.dp),
                shape = CircleShape,
            ) {
                Icon(Icons.AutoMirrored.Outlined.Logout, contentDescription = null, tint = Danger)
                Spacer(Modifier.size(8.dp))
                Text("Sign out", color = Danger, fontWeight = FontWeight.Bold)
            }
            Text(
                "Mi Cusina for customers · v${BuildConfig.VERSION_NAME}",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                textAlign = TextAlign.Center,
                modifier = Modifier.fillMaxWidth(),
            )
        }
    }

    if (confirmSignOut) {
        ConfirmDialog(
            title = "Sign out?",
            message = "You'll need your email and password to sign back in.",
            confirmLabel = "Sign out",
            dismissLabel = "Stay signed in",
            onConfirm = {
                confirmSignOut = false
                scope.launch { repository.logout() }
            },
            onDismiss = { confirmSignOut = false },
        )
    }
}

@Composable
private fun ProfileLine(icon: ImageVector, text: String) {
    Row(verticalAlignment = Alignment.Top) {
        Icon(icon, contentDescription = null, tint = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.size(20.dp))
        Spacer(Modifier.size(12.dp))
        Text(text, style = MaterialTheme.typography.bodyMedium)
    }
}

@Composable
private fun NavRow(icon: ImageVector, label: String, onClick: () -> Unit) {
    Row(
        Modifier
            .fillMaxWidth()
            .clickable(onClick = onClick)
            .padding(horizontal = 16.dp, vertical = 14.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Icon(icon, contentDescription = null, tint = Brand)
        Spacer(Modifier.size(16.dp))
        Text(label, style = MaterialTheme.typography.titleSmall, modifier = Modifier.weight(1f))
        Icon(Icons.AutoMirrored.Rounded.KeyboardArrowRight, contentDescription = null, tint = Color(0xFFB0B3BA))
    }
}

private fun initials(name: String): String =
    name.trim().split(Regex("\\s+")).filter { it.isNotEmpty() }.take(2).joinToString("") { it.first().uppercase() }
        .ifEmpty { "MC" }
