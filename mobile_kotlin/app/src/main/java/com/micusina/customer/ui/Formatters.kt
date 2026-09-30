package com.micusina.customer.ui

import java.text.DecimalFormat
import java.text.DecimalFormatSymbols
import java.time.LocalDate
import java.time.LocalTime
import java.time.OffsetDateTime
import java.time.ZoneId
import java.time.format.DateTimeFormatter
import java.util.Locale

/** The restaurant's time zone; the backend validates reservations against it. */
val ManilaZone: ZoneId = ZoneId.of("Asia/Manila")

private val pesoFormat = DecimalFormat("#,##0.00", DecimalFormatSymbols(Locale.US))

fun Double.toPeso(): String = "₱" + pesoFormat.format(this)

private val dateTimeDisplay = DateTimeFormatter.ofPattern("MMM d, yyyy · h:mm a", Locale.US)
private val dateDisplay = DateTimeFormatter.ofPattern("EEE, MMM d, yyyy", Locale.US)

/** Laravel serializes timestamps as ISO-8601 UTC, e.g. 2026-09-25T07:04:13.000000Z. */
fun parseServerTimestamp(value: String?): OffsetDateTime? =
    value?.let { runCatching { OffsetDateTime.parse(it) }.getOrNull() }

fun formatServerTimestamp(value: String?): String =
    parseServerTimestamp(value)?.atZoneSameInstant(ManilaZone)?.format(dateTimeDisplay) ?: ""

fun formatReservationDate(date: String): String =
    runCatching { LocalDate.parse(date).format(dateDisplay) }.getOrDefault(date)

/** PHP `g:i A` as used by the reservation API, e.g. "7:30 PM". */
val ReservationTimeFormat: DateTimeFormatter = DateTimeFormatter.ofPattern("h:mm a", Locale.US)

fun parseReservationTime(time: String): LocalTime? =
    runCatching { LocalTime.parse(time.trim().uppercase(Locale.US), ReservationTimeFormat) }.getOrNull()
