package com.micusina.customer.data

import com.micusina.customer.data.model.Order
import com.micusina.customer.ui.orders.DeliveryStage
import com.micusina.customer.ui.orders.groupOrders
import com.micusina.customer.ui.parseReservationTime
import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertNull
import org.junit.Assert.assertTrue
import org.junit.Test
import java.time.LocalTime

class HelpersTest {

    @Test
    fun ordersAreGroupedByCheckoutNewestFirst() {
        val orders = listOf(
            Order(id = 12, checkoutGroupId = "b", title = "Halo-halo", price = 90.0),
            Order(id = 11, checkoutGroupId = "b", title = "Adobo", price = 150.0),
            Order(id = 10, checkoutGroupId = "a", title = "Sinigang", price = 200.0, deliveryStatus = "Delivered"),
        )
        val groups = groupOrders(orders)
        assertEquals(listOf("b", "a"), groups.map { it.key })
        assertEquals(11, groups[0].id)
        assertEquals(240.0, groups[0].total, 0.0)
        assertEquals(DeliveryStage.Delivered, groups[1].stage)
    }

    @Test
    fun onlyUnpaidUndispatchedOrdersCanBeCancelled() {
        fun group(vararg orders: Order) = groupOrders(orders.toList()).single()
        assertTrue(group(Order(id = 1, checkoutGroupId = "g")).canCancel)
        assertFalse(group(Order(id = 1, checkoutGroupId = "g", paymentStatus = "Paid")).canCancel)
        assertFalse(group(Order(id = 1, checkoutGroupId = "g", riderId = 3)).canCancel)
        assertFalse(group(Order(id = 1, checkoutGroupId = "g", deliveryStatus = "On The Way")).canCancel)
        assertFalse(
            group(
                Order(id = 1, checkoutGroupId = "g"),
                Order(id = 2, checkoutGroupId = "g", deliveryStatus = "Canceled"),
            ).canCancel,
        )
    }

    @Test
    fun storedAddressIsParsedForPrefill() {
        val address = DeliveryAreas.parse("Purok 3, Barangay Ticad, Bantayan, Bantayan Island, Cebu - Blue gate")
        assertEquals(DeliveryAreas.Address("Bantayan", "Ticad", "3", "Blue gate"), address)
        assertEquals("", DeliveryAreas.parse("Purok Mangga, Barangay Pooc, Santa Fe, Bantayan Island, Cebu")?.details)
        assertNull(DeliveryAreas.parse("123 Main St, Cebu City"))
        assertNull(DeliveryAreas.parse("Purok 1, Barangay Nowhere, Bantayan, Bantayan Island, Cebu"))
    }

    @Test
    fun philippinePhoneRulesMatchTheBackend() {
        assertTrue(PhilippinePhone.isValid("09171234567"))
        assertTrue(PhilippinePhone.isValid("+639171234567"))
        assertFalse(PhilippinePhone.isValid("9171234567"))
        assertFalse(PhilippinePhone.isValid("0917123456"))
        assertEquals("09171234567", PhilippinePhone.toLocal("+639171234567"))
        assertEquals("+639171234567", PhilippinePhone.sanitizeInput("+63 917-123-4567"))
    }

    @Test
    fun reservationTimesUsePhpFormat() {
        assertEquals(LocalTime.of(19, 30), parseReservationTime("7:30 PM"))
        assertEquals(LocalTime.of(9, 5), parseReservationTime("9:05 am"))
        assertEquals("7:30 PM", LocalTime.of(19, 30).format(com.micusina.customer.ui.ReservationTimeFormat))
    }

    @Test
    fun foodImagesResolveLikeTheServer() {
        val origin = SiteUrls.origin
        assertEquals("$origin/food_img/adobo.jpg", SiteUrls.foodImage("adobo.jpg"))
        assertEquals("$origin/food_img/adobo.jpg", SiteUrls.foodImage("/food_img/adobo.jpg"))
        assertEquals("https://cdn.test/x.png", SiteUrls.foodImage("https://cdn.test/x.png"))
        assertNull(SiteUrls.foodImage("  "))
    }
}
