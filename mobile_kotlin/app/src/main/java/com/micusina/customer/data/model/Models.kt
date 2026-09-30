package com.micusina.customer.data.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class User(
    val id: Int,
    val name: String = "",
    val email: String = "",
    val phone: String? = null,
    val address: String? = null,
    val usertype: String? = null,
    @SerialName("staff_role") val staffRole: String? = null,
) {
    /** Admin and staff accounts get `mobile:staff` tokens and are not served by this app. */
    val isCustomer: Boolean get() = usertype !in setOf("admin", "staff")

    val firstName: String get() = name.trim().substringBefore(' ')
}

@Serializable
data class Food(
    val id: Int,
    val title: String = "",
    val detail: String? = null,
    val category: String = "All menu",
    @Serializable(LenientDoubleSerializer::class) val price: Double = 0.0,
    @Serializable(LenientIntSerializer::class) val stock: Int = 0,
    val image: String? = null,
    @SerialName("image_url") val imageUrl: String? = null,
)

@Serializable
data class CartItem(
    val id: Int,
    @SerialName("food_id") @Serializable(LenientIntSerializer::class) val foodId: Int = 0,
    val title: String = "",
    val details: String? = null,
    val image: String? = null,
    @Serializable(LenientIntSerializer::class) val quantity: Int = 0,
    /** Line total (unit price x quantity), as stored by the server. */
    @Serializable(LenientDoubleSerializer::class) val price: Double = 0.0,
) {
    val unitPrice: Double get() = if (quantity > 0) price / quantity else price
}

@Serializable
data class Order(
    val id: Int,
    @SerialName("checkout_group_id") val checkoutGroupId: String? = null,
    val name: String? = null,
    val phone: String? = null,
    val address: String? = null,
    val title: String = "",
    @Serializable(LenientIntSerializer::class) val quantity: Int = 0,
    /** Line total for this item. */
    @Serializable(LenientDoubleSerializer::class) val price: Double = 0.0,
    val image: String? = null,
    @SerialName("delivery_status") val deliveryStatus: String = "In Progress",
    @SerialName("payment_method") val paymentMethod: String? = null,
    @SerialName("payment_status") val paymentStatus: String? = null,
    @SerialName("payment_reference") val paymentReference: String? = null,
    @SerialName("rider_id") val riderId: Int? = null,
    @SerialName("created_at") val createdAt: String? = null,
)

@Serializable
data class Reservation(
    val id: Int,
    val name: String? = null,
    @SerialName("first_name") val firstName: String? = null,
    @SerialName("last_name") val lastName: String? = null,
    val phone: String? = null,
    @Serializable(LenientIntSerializer::class) val guest: Int = 0,
    val date: String = "",
    val time: String = "",
    @SerialName("reservation_price") @Serializable(LenientDoubleSerializer::class) val reservationPrice: Double = 0.0,
    @SerialName("deposit_amount") @Serializable(LenientDoubleSerializer::class) val depositAmount: Double = 0.0,
    @SerialName("payment_method") val paymentMethod: String? = null,
    @SerialName("payment_status") val paymentStatus: String? = null,
    val status: String? = null,
    @SerialName("gcash_reference") val reference: String? = null,
    /** Only present while a PayMongo checkout is still awaiting payment. */
    @SerialName("checkout_url") val checkoutUrl: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
) {
    val displayName: String
        get() = name?.takeIf { it.isNotBlank() }
            ?: listOfNotNull(firstName, lastName).joinToString(" ").trim()
}

// ---- Requests ----

@Serializable
data class LoginRequest(
    val email: String,
    val password: String,
    @SerialName("device_name") val deviceName: String,
    @SerialName("two_factor_code") val twoFactorCode: String? = null,
)

@Serializable
data class RegistrationRequest(
    val name: String,
    val email: String,
    val phone: String,
    val address: String,
    val password: String,
    @SerialName("password_confirmation") val passwordConfirmation: String,
)

@Serializable
data class VerifyRegistrationRequest(
    @SerialName("registration_id") val registrationId: String,
    @SerialName("email_code") val emailCode: String,
    @SerialName("device_name") val deviceName: String,
)

@Serializable
data class QuantityRequest(val quantity: Int)

@Serializable
data class CheckoutRequest(
    val name: String,
    val phone: String,
    val municipality: String,
    val barangay: String,
    val purok: String,
    @SerialName("address_details") val addressDetails: String? = null,
    @SerialName("payment_method") val paymentMethod: String,
    @SerialName("payment_reference") val paymentReference: String? = null,
)

@Serializable
data class ReservationRequest(
    @SerialName("first_name") val firstName: String,
    @SerialName("last_name") val lastName: String,
    val phone: String,
    val guest: Int,
    /** yyyy-MM-dd */
    val date: String,
    /** h:mm AM/PM, e.g. "7:30 PM" (PHP `g:i A`). */
    val time: String,
    @SerialName("payment_method") val paymentMethod: String,
)

// ---- Responses ----

@Serializable
data class LoginResponse(
    val token: String? = null,
    val user: User? = null,
    val message: String? = null,
    @SerialName("two_factor_required") val twoFactorRequired: Boolean = false,
)

@Serializable
data class RegistrationStarted(
    val message: String? = null,
    @SerialName("registration_id") val registrationId: String,
    @SerialName("expires_in") val expiresIn: Int = 600,
)

@Serializable
data class AuthResponse(val token: String, val user: User)

@Serializable
data class UserEnvelope(val user: User)

@Serializable
data class FoodsEnvelope(val foods: List<Food> = emptyList())

@Serializable
data class CartEnvelope(val items: List<CartItem> = emptyList())

@Serializable
data class CartItemEnvelope(val message: String? = null, val item: CartItem? = null)

@Serializable
data class CheckoutResponse(val message: String? = null, val orders: List<Order> = emptyList())

@Serializable
data class OrdersEnvelope(val orders: List<Order> = emptyList())

@Serializable
data class ReservationsEnvelope(val reservations: List<Reservation> = emptyList())

@Serializable
data class ReservationCreated(
    val message: String? = null,
    val reservation: Reservation? = null,
    @SerialName("checkout_url") val checkoutUrl: String? = null,
)

@Serializable
data class MessageResponse(val message: String? = null)

@Serializable
data class ApiErrorBody(
    val message: String? = null,
    val errors: Map<String, List<String>>? = null,
)
