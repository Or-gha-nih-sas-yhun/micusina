# Mi Cusina — Customer Android app (Kotlin)

A native Android app for Mi Cusina **customers**, written in Kotlin with Jetpack Compose.
It talks to the existing Laravel mobile API (`routes/api.php`, `MobileApiController`) —
no backend changes are needed.

Staff and admin accounts are intentionally not supported: if one signs in, the app
revokes the new token and asks them to use the Mi Cusina dashboard instead.

## Features

| Area | What customers can do |
| --- | --- |
| Account | Sign in (including authenticator-app 2FA), register with email verification code, sign out |
| Menu | Browse dishes by category, search, see live stock, add to cart with a quantity picker |
| Cart | Change quantities, remove items, see the running total (cart badge on the tab bar) |
| Checkout | Deliver anywhere in Bantayan, Madridejos or Santa Fe; Cash on Delivery or GCash |
| Orders | Track each checkout (Received → On the way → Delivered), auto-refreshes every 30 s, cancel while still allowed |
| Reservations | Book a table (date, time, guests), pay the ₱125 downpayment via PayMongo in a Custom Tab, cancel upcoming reservations |

The session token is kept in app-private DataStore and excluded from backups. The last
profile is cached so the app opens offline; any `401` signs the customer out.

## Requirements

- Android Studio (2025.2 or newer) or the command line with **JDK 17**
- Android SDK platform 36
- A device or emulator running Android 8.0 (API 26) or newer

## Build and run

Open the `mobile_kotlin` folder in Android Studio and press **Run**, or from a terminal:

```bash
cd mobile_kotlin
./gradlew assembleDebug            # APK: app/build/outputs/apk/debug/app-debug.apk
./gradlew installDebug             # install on a connected device/emulator
./gradlew testDebugUnitTest        # unit tests
```

Debug builds install as `com.micusina.customer.debug`, so they can sit next to a release build.

### Pointing at a different backend

The app uses `https://micusina-pos.com/api/mobile/` by default (see `gradle.properties`).
To use a local Laravel server (`php artisan serve`) from the emulator:

```bash
./gradlew installDebug -PmiCusinaApiUrl=http://10.0.2.2:8000/api/mobile/
```

Plain HTTP is only allowed for `10.0.2.2`, `localhost` and `127.0.0.1`, and only in debug builds.

## Release build

Release builds are minified with R8. To sign them, create `mobile_kotlin/keystore.properties`
(ignored by git):

```properties
storeFile=../path/to/mi-cusina-release.jks
storePassword=...
keyAlias=...
keyPassword=...
```

Then run `./gradlew assembleRelease`. Without that file the release APK is built unsigned.
Bump `versionCode` / `versionName` in `app/build.gradle.kts` for each release.

The published app is signed with the key in `C:\Users\davea\.micusina\mi-cusina-release.jks`
(certificate SHA-256 `AE:35:02:C1:ED:94:23:69:D9:FC:C2:D3:F3:CB:C2:2D:E0:47:6E:7B:B2:95:9D:B6:81:54:C0:90:6B:88:6F:A1`).
Keep a backup of that file and its password: every future update must be signed with the same
key, or phones will refuse to install it over the existing app.

### Publishing to the website

The website's **Download App** page (`/install-app` → `/download-app`) serves
`public/downloads/Mi-Cusina.apk`. To publish a new version:

1. Bump `versionCode` (and `versionName`) in `app/build.gradle.kts`.
2. `./gradlew clean assembleRelease`
3. Copy `app/build/outputs/apk/release/app-release.apk` to `../public/downloads/Mi-Cusina.apk`.
4. Commit and deploy the site as usual.

## Project layout

```
app/src/main/java/com/micusina/customer/
├── MiCusinaApplication.kt      App + manual dependency container, Coil image loader
├── MainActivity.kt
├── data/
│   ├── remote/                 Retrofit API, OkHttp client (bearer token, 401 handling), error mapping
│   ├── model/                  API models; lenient number parsing for string DB columns
│   ├── MiCusinaRepository.kt   Session state, shared cart, all API calls
│   ├── SessionStore.kt         Token + cached profile (DataStore)
│   └── DeliveryAreas.kt, PhilippinePhone.kt, SiteUrls.kt
└── ui/
    ├── navigation/             Splash → sign-in flow → bottom-tab app
    ├── auth/  menu/  cart/  orders/  reservations/  account/
    ├── components/             Shared Compose building blocks
    └── theme/                  Mi Cusina colors, type and shapes
```

Business rules that mirror the backend (phone format, barangay list, cancellation rules,
reservation price/downpayment) live in one place each, with a comment pointing at the
PHP code they copy, so they are easy to update together.
