package com.micusina.customer.ui

import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import androidx.browser.customtabs.CustomTabColorSchemeParams
import androidx.browser.customtabs.CustomTabsIntent
import androidx.compose.ui.graphics.toArgb
import androidx.core.net.toUri
import com.micusina.customer.ui.theme.Brand

/**
 * Opens [url] in a Custom Tab (used for PayMongo checkout), falling back to any browser.
 * Returns false when the device has nothing that can open it.
 */
fun openInBrowser(context: Context, url: String): Boolean {
    val uri = url.toUri()
    return try {
        CustomTabsIntent.Builder()
            .setDefaultColorSchemeParams(CustomTabColorSchemeParams.Builder().setToolbarColor(Brand.toArgb()).build())
            .setShowTitle(true)
            .build()
            .launchUrl(context, uri)
        true
    } catch (_: ActivityNotFoundException) {
        try {
            context.startActivity(Intent(Intent.ACTION_VIEW, uri).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
            true
        } catch (_: ActivityNotFoundException) {
            false
        }
    }
}
