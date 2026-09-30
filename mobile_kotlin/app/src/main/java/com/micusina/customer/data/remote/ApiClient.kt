package com.micusina.customer.data.remote

import com.micusina.customer.BuildConfig
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.asSharedFlow
import kotlinx.serialization.json.Json
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.kotlinx.serialization.asConverterFactory
import retrofit2.create
import java.util.concurrent.TimeUnit

/** Holds the Sanctum bearer token in memory and reports when the server rejects it. */
class AuthTokenHolder {
    @Volatile
    var token: String? = null

    private val _expired = MutableSharedFlow<Unit>(extraBufferCapacity = 1)
    val expired: SharedFlow<Unit> = _expired.asSharedFlow()

    /** Ignores 401s for a token that has already been replaced (e.g. after signing in again). */
    fun onUnauthorized(rejectedToken: String) {
        if (rejectedToken == token) _expired.tryEmit(Unit)
    }
}

class ApiClient(baseUrl: String, private val tokens: AuthTokenHolder) {

    val json = Json {
        ignoreUnknownKeys = true
        coerceInputValues = true
        explicitNulls = false
        isLenient = true
    }

    private val okHttp = OkHttpClient.Builder()
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .addInterceptor { chain ->
            val explicitAuth = chain.request().header("Authorization") != null
            val token = if (explicitAuth) null else tokens.token
            val request = chain.request().newBuilder()
                .header("Accept", "application/json")
                .apply { if (token != null) header("Authorization", "Bearer $token") }
                .build()
            val response = chain.proceed(request)
            if (response.code == 401 && token != null) tokens.onUnauthorized(token)
            response
        }
        .apply {
            if (BuildConfig.DEBUG) {
                addInterceptor(
                    HttpLoggingInterceptor().apply {
                        level = HttpLoggingInterceptor.Level.BASIC
                        redactHeader("Authorization")
                    },
                )
            }
        }
        .build()

    val api: MiCusinaApi = Retrofit.Builder()
        .baseUrl(baseUrl)
        .client(okHttp)
        .addConverterFactory(json.asConverterFactory("application/json".toMediaType()))
        .build()
        .create()
}
