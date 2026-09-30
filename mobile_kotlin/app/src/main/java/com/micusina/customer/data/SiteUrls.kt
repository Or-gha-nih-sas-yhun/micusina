package com.micusina.customer.data

import com.micusina.customer.BuildConfig

/** Public URLs on the Mi Cusina website, derived from the configured API base URL. */
object SiteUrls {
    /** e.g. https://micusina-pos.com */
    val origin: String = BuildConfig.API_BASE_URL.substringBefore("/api/mobile").trimEnd('/')

    /** Mirrors MobileApiController::foodImageUrl() for cart and order rows that only carry a file name. */
    fun foodImage(image: String?): String? {
        val trimmed = image?.trim().orEmpty()
        if (trimmed.isEmpty()) return null
        if (trimmed.startsWith("http://", ignoreCase = true) || trimmed.startsWith("https://", ignoreCase = true)) {
            return trimmed
        }
        val path = trimmed.replace('\\', '/').trimStart('/')
        return "$origin/" + if (path.startsWith("food_img/")) path else "food_img/$path"
    }

    /** The same QR codes the web checkout shows for online payment. */
    fun paymentQr(paymentMethod: String): String? = when (paymentMethod) {
        PaymentMethods.GCASH -> "$origin/payment/gcash-qr.jpg"
        PaymentMethods.BANK_TRANSFER -> "$origin/payment/bank-qr.jpg"
        else -> null
    }
}

object PaymentMethods {
    const val CASH_ON_DELIVERY = "Cash on Delivery"
    const val GCASH = "GCash"
    const val BANK_TRANSFER = "Bank Transfer"

    val checkout = listOf(CASH_ON_DELIVERY, GCASH, BANK_TRANSFER)
    val reservation = listOf(GCASH, BANK_TRANSFER)
}

/** Reservation pricing fixed by MobileApiController::createReservation(). */
object ReservationPricing {
    const val PRICE = 250.0
    const val DEPOSIT = 125.0
    const val MAX_GUESTS = 20
}
