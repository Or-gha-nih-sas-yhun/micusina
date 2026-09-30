package com.micusina.customer.data.model

import kotlinx.serialization.KSerializer
import kotlinx.serialization.descriptors.PrimitiveKind
import kotlinx.serialization.descriptors.PrimitiveSerialDescriptor
import kotlinx.serialization.descriptors.SerialDescriptor
import kotlinx.serialization.encoding.Decoder
import kotlinx.serialization.encoding.Encoder
import kotlinx.serialization.json.JsonDecoder
import kotlinx.serialization.json.JsonNull
import kotlinx.serialization.json.JsonPrimitive

/**
 * The Laravel tables store most prices and quantities in string columns ("150", "₱150.00"),
 * while computed fields arrive as JSON numbers. These serializers accept either form.
 */
object LenientDoubleSerializer : KSerializer<Double> {
    override val descriptor: SerialDescriptor =
        PrimitiveSerialDescriptor("com.micusina.LenientDouble", PrimitiveKind.DOUBLE)

    override fun deserialize(decoder: Decoder): Double {
        if (decoder !is JsonDecoder) return decoder.decodeDouble()
        val primitive = decoder.decodeJsonElement() as? JsonPrimitive ?: return 0.0
        if (primitive is JsonNull) return 0.0
        return parseAmount(primitive.content)
    }

    override fun serialize(encoder: Encoder, value: Double) = encoder.encodeDouble(value)

    /** Mirrors MobileApiController::price(): keep digits and the decimal point only. */
    fun parseAmount(raw: String): Double =
        raw.filter { it.isDigit() || it == '.' }.toDoubleOrNull() ?: 0.0
}

object LenientIntSerializer : KSerializer<Int> {
    override val descriptor: SerialDescriptor =
        PrimitiveSerialDescriptor("com.micusina.LenientInt", PrimitiveKind.INT)

    override fun deserialize(decoder: Decoder): Int {
        if (decoder !is JsonDecoder) return decoder.decodeInt()
        val primitive = decoder.decodeJsonElement() as? JsonPrimitive ?: return 0
        if (primitive is JsonNull) return 0
        return primitive.content.trim().toDoubleOrNull()?.toInt()
            ?: primitive.content.filter { it.isDigit() }.toIntOrNull()
            ?: 0
    }

    override fun serialize(encoder: Encoder, value: Int) = encoder.encodeInt(value)
}
