package com.micusina.customer.data

import com.micusina.customer.data.model.CartEnvelope
import com.micusina.customer.data.model.FoodsEnvelope
import com.micusina.customer.data.model.LoginResponse
import com.micusina.customer.data.model.OrdersEnvelope
import com.micusina.customer.data.model.ReservationsEnvelope
import com.micusina.customer.data.model.User
import com.micusina.customer.data.remote.ApiClient
import com.micusina.customer.data.remote.AuthTokenHolder
import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertNull
import org.junit.Assert.assertTrue
import org.junit.Test

/** Payload shapes taken from MobileApiController and the string-typed Laravel columns. */
class ModelDecodingTest {
    private val json = ApiClient("https://example.test/api/mobile/", AuthTokenHolder()).json

    @Test
    fun foodsDecodeComputedNumbers() {
        val body = """{"foods":[{"id":3,"title":"Chicken Adobo","detail":null,"category":"Ulam","price":150.5,
            "stock":12,"image":"adobo.jpg","image_url":"https://example.test/food_img/adobo.jpg"}]}"""
        val food = json.decodeFromString<FoodsEnvelope>(body).foods.single()
        assertEquals(150.5, food.price, 0.0)
        assertEquals(12, food.stock)
        assertNull(food.detail)
        assertEquals("https://example.test/food_img/adobo.jpg", food.imageUrl)
    }

    @Test
    fun cartRowsAcceptStringQuantitiesAndPrices() {
        val body = """{"items":[{"id":9,"userid":"4","food_id":3,"title":"Chicken Adobo","details":"Classic",
            "quantity":"2","image":"adobo.jpg","price":"301","created_at":"2026-09-25T07:04:13.000000Z"}]}"""
        val item = json.decodeFromString<CartEnvelope>(body).items.single()
        assertEquals(2, item.quantity)
        assertEquals(301.0, item.price, 0.0)
        assertEquals(150.5, item.unitPrice, 0.0)
    }

    @Test
    fun ordersToleratePesoSignsNullTitlesAndMissingGroups() {
        val body = """{"orders":[{"id":1,"user_id":4,"checkout_group_id":null,"title":null,"quantity":"1",
            "price":"₱1,250.00","delivery_status":"On The Way","payment_status":"Unpaid","rider_id":7}]}"""
        val order = json.decodeFromString<OrdersEnvelope>(body).orders.single()
        assertEquals(1250.0, order.price, 0.0)
        assertEquals("", order.title)
        assertNull(order.checkoutGroupId)
        assertEquals(7, order.riderId)
    }

    @Test
    fun reservationsDecodeDecimalStrings() {
        val body = """{"reservations":[{"id":5,"user_id":4,"first_name":"Ana","last_name":"Cruz","name":"Ana Cruz",
            "phone":"09171234567","guest":"4","date":"2026-10-01","time":"7:30 PM","reservation_price":"250.00",
            "deposit_amount":"125.00","payment_method":"GCash","payment_status":"Pending","status":"Awaiting Payment",
            "gcash_reference":"BK-000005","checkout_url":"https://checkout.paymongo.com/cs_1"}]}"""
        val reservation = json.decodeFromString<ReservationsEnvelope>(body).reservations.single()
        assertEquals(4, reservation.guest)
        assertEquals(125.0, reservation.depositAmount, 0.0)
        assertEquals("https://checkout.paymongo.com/cs_1", reservation.checkoutUrl)
        assertEquals("Ana Cruz", reservation.displayName)
    }

    @Test
    fun twoFactorChallengeHasNoToken() {
        val response = json.decodeFromString<LoginResponse>(
            """{"message":"Enter the authentication code from your authenticator app.","two_factor_required":true}""",
        )
        assertTrue(response.twoFactorRequired)
        assertNull(response.token)
    }

    @Test
    fun staffAccountsAreNotCustomers() {
        assertFalse(User(id = 1, usertype = "admin").isCustomer)
        assertFalse(User(id = 2, usertype = "staff", staffRole = "rider").isCustomer)
        assertTrue(User(id = 3, usertype = "user").isCustomer)
    }

    @Test
    fun optionalRequestFieldsAreOmitted() {
        val encoded = json.encodeToString(
            com.micusina.customer.data.model.CheckoutRequest.serializer(),
            com.micusina.customer.data.model.CheckoutRequest(
                name = "Ana", phone = "09171234567", municipality = "Bantayan", barangay = "Ticad",
                purok = "3", paymentMethod = PaymentMethods.CASH_ON_DELIVERY,
            ),
        )
        assertFalse(encoded.contains("payment_reference"))
        assertFalse(encoded.contains("address_details"))
    }
}
