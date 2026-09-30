package com.micusina.customer

import android.app.Application
import android.content.Context
import android.os.Build
import coil3.ImageLoader
import coil3.PlatformContext
import coil3.SingletonImageLoader
import coil3.request.crossfade
import com.micusina.customer.data.MiCusinaRepository
import com.micusina.customer.data.SessionStore
import com.micusina.customer.data.remote.ApiClient
import com.micusina.customer.data.remote.AuthTokenHolder
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob

class MiCusinaApplication : Application(), SingletonImageLoader.Factory {

    lateinit var container: AppContainer
        private set

    override fun onCreate() {
        super.onCreate()
        container = AppContainer(this)
    }

    // Menu photos are public, so images load through Coil's own client without the API token.
    override fun newImageLoader(context: PlatformContext): ImageLoader =
        ImageLoader.Builder(context).crossfade(true).build()
}

/** Manual dependency graph; the app is small enough not to need a DI framework. */
class AppContainer(context: Context) {
    private val appScope = CoroutineScope(SupervisorJob() + Dispatchers.Main.immediate)
    private val tokens = AuthTokenHolder()
    private val client = ApiClient(BuildConfig.API_BASE_URL, tokens)

    val repository = MiCusinaRepository(
        api = client.api,
        json = client.json,
        tokens = tokens,
        store = SessionStore(context, client.json),
        scope = appScope,
        deviceName = "Mi Cusina Android (${Build.MANUFACTURER} ${Build.MODEL})".take(80),
    )
}
