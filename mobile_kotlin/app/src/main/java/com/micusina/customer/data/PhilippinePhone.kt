package com.micusina.customer.data

/** Matches the backend rule `/^(09[0-9]{9}|\+639[0-9]{9})$/`. */
object PhilippinePhone {
    private val pattern = Regex("^(09\\d{9}|\\+639\\d{9})$")

    fun isValid(phone: String): Boolean = pattern.matches(phone.trim())

    /** "+639171234567" -> "09171234567", which is how customers usually type it. */
    fun toLocal(phone: String?): String {
        val trimmed = phone?.trim().orEmpty()
        return if (trimmed.startsWith("+63")) "0" + trimmed.removePrefix("+63") else trimmed
    }

    /** Keeps only characters that can appear in a valid number while typing. */
    fun sanitizeInput(input: String): String =
        input.filterIndexed { index, c -> c.isDigit() || (c == '+' && index == 0) }.take(13)
}
