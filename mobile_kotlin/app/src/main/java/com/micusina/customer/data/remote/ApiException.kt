package com.micusina.customer.data.remote

import android.util.Log
import com.micusina.customer.BuildConfig
import com.micusina.customer.data.model.ApiErrorBody
import kotlinx.coroutines.CancellationException
import kotlinx.serialization.SerializationException
import kotlinx.serialization.json.Json
import retrofit2.HttpException
import java.io.IOException

/** A failure with a message that is safe to show to the customer. */
class ApiException(
    message: String,
    /** Laravel validation errors, first message per field (e.g. "phone" -> "..."). */
    val fieldErrors: Map<String, String> = emptyMap(),
    val statusCode: Int? = null,
    cause: Throwable? = null,
) : Exception(message, cause)

/** Runs an API call and converts every failure except cancellation into an [ApiException]. */
suspend fun <T> apiCall(json: Json, block: suspend () -> T): T =
    try {
        block()
    } catch (e: CancellationException) {
        throw e
    } catch (e: ApiException) {
        throw e
    } catch (e: Throwable) {
        if (BuildConfig.DEBUG) Log.w("MiCusinaApi", "API call failed", e)
        throw e.toApiException(json)
    }

fun Throwable.toApiException(json: Json): ApiException = when (this) {
    is HttpException -> {
        val body = runCatching {
            response()?.errorBody()?.string()?.let { json.decodeFromString<ApiErrorBody>(it) }
        }.getOrNull()
        val fields = body?.errors.orEmpty()
            .mapNotNull { (field, messages) -> messages.firstOrNull()?.let { field to it } }
            .toMap()
        val message = fields.values.firstOrNull()
            ?: body?.message?.takeIf { it.isNotBlank() && it != "Server Error" }
            ?: fallbackMessage(code())
        ApiException(message, fields, code(), this)
    }
    is IOException -> ApiException("Unable to reach Mi Cusina. Check your internet connection.", cause = this)
    is SerializationException -> ApiException("Mi Cusina sent an unexpected response. Please try again.", cause = this)
    else -> ApiException("Something went wrong. Please try again.", cause = this)
}

private fun fallbackMessage(code: Int): String = when (code) {
    401 -> "Your session has expired. Please sign in again."
    403 -> "You don't have access to this."
    404 -> "We couldn't find that. It may have been removed."
    419, 429 -> "Too many attempts. Please wait a minute and try again."
    in 500..599 -> "Mi Cusina is having trouble right now. Please try again shortly."
    else -> "Request failed. Please try again."
}
