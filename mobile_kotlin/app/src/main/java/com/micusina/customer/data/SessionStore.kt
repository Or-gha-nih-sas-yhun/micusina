package com.micusina.customer.data

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import com.micusina.customer.data.model.User
import kotlinx.coroutines.flow.first
import kotlinx.serialization.json.Json

private val Context.sessionDataStore by preferencesDataStore(name = "session")

/**
 * Persists the Sanctum token and the last known profile in app-private storage
 * (excluded from backups, see data_extraction_rules.xml).
 */
class SessionStore(context: Context, private val json: Json) {
    private val dataStore = context.applicationContext.sessionDataStore

    data class Saved(val token: String, val user: User?)

    suspend fun read(): Saved? {
        val prefs = dataStore.data.first()
        val token = prefs[TOKEN] ?: return null
        val user = prefs[USER]?.let { runCatching { json.decodeFromString<User>(it) }.getOrNull() }
        return Saved(token, user)
    }

    suspend fun save(token: String, user: User) {
        dataStore.edit {
            it[TOKEN] = token
            it[USER] = json.encodeToString(User.serializer(), user)
        }
    }

    suspend fun saveUser(user: User) {
        dataStore.edit { it[USER] = json.encodeToString(User.serializer(), user) }
    }

    suspend fun clear() {
        dataStore.edit { it.clear() }
    }

    private companion object {
        val TOKEN = stringPreferencesKey("token")
        val USER = stringPreferencesKey("user")
    }
}
