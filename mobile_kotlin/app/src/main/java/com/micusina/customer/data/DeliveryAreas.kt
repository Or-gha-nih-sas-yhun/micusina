package com.micusina.customer.data

/** Delivery coverage on Bantayan Island, matching resources/views/home/checkout.blade.php. */
object DeliveryAreas {
    val barangays: Map<String, List<String>> = linkedMapOf(
        "Bantayan" to listOf(
            "Atop-atop", "Baigad", "Baod", "Binaobao", "Botigues", "Doong", "Guiwanon", "Hilotongan",
            "Kabac", "Kabangbang", "Kampingganon", "Kangkaibe", "Lipayran", "Luyongbaybay", "Mojon",
            "Obo-ob", "Patao", "Putian", "Sillon", "Suba", "Sulangan", "Sungko", "Tamiao", "Ticad",
        ),
        "Madridejos" to listOf(
            "Bunakan", "Kangwayan", "Kaongkod", "Kodia", "Maalat", "Malbago", "Mancilang", "Pili",
            "Poblacion", "San Agustin", "Tabagak", "Talangnan", "Tarong", "Tugas",
        ),
        "Santa Fe" to listOf(
            "Balidbid", "Hagdan", "Hilantagaan", "Kinatarkan", "Langub", "Maricaban", "Okoy",
            "Poblacion", "Pooc", "Talisay",
        ),
    )

    val municipalities: List<String> = barangays.keys.toList()

    data class Address(
        val municipality: String,
        val barangay: String,
        val purok: String,
        val details: String,
    )

    // The server stores: "Purok X, Barangay Y, Municipality, Bantayan Island, Cebu[ - details]"
    private val storedAddress =
        Regex("^Purok (.+?), Barangay (.+?), (Bantayan|Madridejos|Santa Fe), Bantayan Island, Cebu(?: - (.*))?$")

    /** Recovers the structured address from a previous checkout, for pre-filling the form. */
    fun parse(address: String?): Address? {
        val match = storedAddress.matchEntire(address?.trim().orEmpty()) ?: return null
        val (purok, barangay, municipality, details) = match.destructured
        if (barangay !in barangays[municipality].orEmpty()) return null
        return Address(municipality, barangay, purok, details)
    }
}
