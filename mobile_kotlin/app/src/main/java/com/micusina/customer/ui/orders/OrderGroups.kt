package com.micusina.customer.ui.orders

import com.micusina.customer.data.model.Order

/** One checkout. The API returns one row per dish, sharing a `checkout_group_id`. */
data class OrderGroup(val key: String, val orders: List<Order>) {
    private val first: Order get() = orders.first()

    /** The id the customer sees and that cancellation is requested with. */
    val id: Int get() = first.id
    val total: Double get() = orders.sumOf { it.price }
    val deliveryStatus: String get() = first.deliveryStatus
    val paymentMethod: String? get() = first.paymentMethod
    val paymentStatus: String? get() = first.paymentStatus
    val paymentReference: String? get() = first.paymentReference
    val address: String? get() = first.address
    val createdAt: String? get() = first.createdAt

    val stage: DeliveryStage get() = DeliveryStage.of(deliveryStatus)

    /** Mirrors MobileApiController::cancelOrder(). */
    val canCancel: Boolean
        get() = orders.all { order ->
            order.deliveryStatus in CANCELLABLE_STATUSES &&
                !order.paymentStatus.equals("paid", ignoreCase = true) &&
                order.riderId == null
        }

    companion object {
        val CANCELLABLE_STATUSES = setOf("In Progress", "Pending", "Awaiting Confirmation", "Awaiting Payment")
    }
}

enum class DeliveryStage(val label: String) {
    Received("Order received"),
    OnTheWay("On the way"),
    Delivered("Delivered"),
    Canceled("Canceled");

    companion object {
        fun of(status: String): DeliveryStage = when (status.trim().lowercase()) {
            "on the way" -> OnTheWay
            "delivered" -> Delivered
            "canceled", "cancelled" -> Canceled
            else -> Received
        }
    }
}

/**
 * Groups item rows into checkouts, newest first. Legacy rows without a group id are grouped by
 * minute and address, close to the server's +/- 5 second legacy cancellation window.
 */
fun groupOrders(orders: List<Order>): List<OrderGroup> =
    orders
        .groupBy { it.checkoutGroupId ?: "legacy:${it.createdAt?.take(16)}|${it.address}" }
        .map { (key, rows) -> OrderGroup(key, rows.sortedBy { it.id }) }
        .sortedByDescending { group -> group.orders.maxOf { it.id } }
