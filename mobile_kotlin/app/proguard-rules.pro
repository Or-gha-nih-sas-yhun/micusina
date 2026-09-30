# kotlinx.serialization, Retrofit, OkHttp and Coil ship their own consumer R8 rules.
# Keep the API models' generated serializers reachable for R8 full mode.
-keep,includedescriptorclasses class com.micusina.customer.data.model.**$$serializer { *; }
-keepclassmembers class com.micusina.customer.data.model.** {
    *** Companion;
}
-keepclasseswithmembers class com.micusina.customer.data.model.** {
    kotlinx.serialization.KSerializer serializer(...);
}
