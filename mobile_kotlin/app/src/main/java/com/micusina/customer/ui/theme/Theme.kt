package com.micusina.customer.ui.theme

import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Shapes
import androidx.compose.material3.Typography
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp

// Mi Cusina brand palette (shared with the website and earlier mobile builds).
val Brand = Color(0xFF8D197F)
val BrandDark = Color(0xFF6B0F60)
val BrandAccent = Color(0xFFE70DB5)
val BrandSoft = Color(0xFFFAE7F5)
val Ink = Color(0xFF1F2329)
val Muted = Color(0xFF6D737D)
val Canvas = Color(0xFFF7F8FB)
val Hairline = Color(0xFFE6E6EA)
val Success = Color(0xFF158456)
val SuccessSoft = Color(0xFFE3F4EC)
val Warning = Color(0xFFA15C00)
val WarningSoft = Color(0xFFFFF1DC)
val Info = Color(0xFF1D5FB8)
val InfoSoft = Color(0xFFE4EEFB)
val Danger = Color(0xFFB3261E)
val DangerSoft = Color(0xFFFBE7E6)

private val colors = lightColorScheme(
    primary = Brand,
    onPrimary = Color.White,
    primaryContainer = BrandSoft,
    onPrimaryContainer = Color(0xFF3A0033),
    secondary = BrandAccent,
    onSecondary = Color.White,
    secondaryContainer = Color(0xFFF9E4F5),
    onSecondaryContainer = Color(0xFF3A0033),
    tertiary = Success,
    background = Canvas,
    onBackground = Ink,
    surface = Canvas,
    onSurface = Ink,
    surfaceVariant = Color(0xFFF1F1F5),
    onSurfaceVariant = Muted,
    surfaceContainerLowest = Color.White,
    surfaceContainerLow = Color.White,
    surfaceContainer = Color.White,
    surfaceContainerHigh = Color(0xFFF4F2F6),
    surfaceContainerHighest = Color(0xFFEDEAF0),
    outline = Color(0xFFCFCFD6),
    outlineVariant = Hairline,
    error = Danger,
    errorContainer = DangerSoft,
)

private val base = Typography()

private val typography = Typography(
    displaySmall = base.displaySmall.copy(fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.5).sp),
    headlineLarge = base.headlineLarge.copy(fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.5).sp),
    headlineMedium = base.headlineMedium.copy(fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.25).sp),
    headlineSmall = base.headlineSmall.copy(fontWeight = FontWeight.Bold),
    titleLarge = base.titleLarge.copy(fontWeight = FontWeight.Bold),
    titleMedium = base.titleMedium.copy(fontWeight = FontWeight.Bold),
    titleSmall = base.titleSmall.copy(fontWeight = FontWeight.SemiBold),
    bodyLarge = base.bodyLarge,
    bodyMedium = base.bodyMedium,
    bodySmall = base.bodySmall,
    labelLarge = base.labelLarge.copy(fontWeight = FontWeight.Bold),
    labelMedium = base.labelMedium.copy(fontWeight = FontWeight.SemiBold),
    labelSmall = base.labelSmall,
)

private val shapes = Shapes(
    extraSmall = RoundedCornerShape(8.dp),
    small = RoundedCornerShape(12.dp),
    medium = RoundedCornerShape(16.dp),
    large = RoundedCornerShape(24.dp),
    extraLarge = RoundedCornerShape(28.dp),
)

/** Tabular figures keep prices aligned in lists and summaries. */
val PriceTextStyle = TextStyle(fontFeatureSettings = "tnum", fontWeight = FontWeight.ExtraBold)

@Composable
fun MiCusinaTheme(content: @Composable () -> Unit) {
    MaterialTheme(colorScheme = colors, typography = typography, shapes = shapes, content = content)
}
