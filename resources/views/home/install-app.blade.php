<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install Mi Cusina</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { align-items: center; background: linear-gradient(135deg, #08090d, #251827); color: #fff; display: flex; font-family: Arial, sans-serif; justify-content: center; margin: 0; min-height: 100vh; padding: 24px; }
        main { background: rgba(20, 20, 27, .94); border: 1px solid #4f354e; border-radius: 20px; box-shadow: 0 24px 70px rgba(0, 0, 0, .35); max-width: 520px; padding: 34px; width: 100%; }
        h1 { margin: 0 0 12px; } p, li { color: #e4dce5; line-height: 1.55; } ol { padding-left: 22px; }
        .download { background: #f88379; border-radius: 999px; color: #1b1015; display: block; font-weight: 800; margin: 26px 0 20px; padding: 15px 20px; text-align: center; text-decoration: none; }
        .note { color: #cbb8c8; font-size: .92rem; } a:not(.download) { color: #ffaca4; }
    </style>
</head>
<body>
    <main>
        <h1>Install Mi Cusina</h1>
        <p>Download the official Android app, then install it from your phone's downloads.</p>
        <a class="download" href="{{ route('mobile-app.download') }}">Download Mi Cusina for Android</a>
        <ol>
            <li>Open the downloaded <strong>Mi-Cusina.apk</strong> file.</li>
            <li>If Android asks, allow your browser or file manager to install apps from this source.</li>
            <li>Tap <strong>Install</strong>, then <strong>Open</strong>.</li>
        </ol>
        <p class="note">Requires Android 8.0 or later. The app is for customers; staff should keep using the Mi Cusina dashboard.</p>
        <p class="note">Already have an older Mi Cusina app? Install this version first, sign in, then uninstall the old one so only one Mi Cusina stays on your phone.</p>
        <p><a href="{{ url('/') }}">Back to Mi Cusina</a></p>
    </main>
</body>
</html>
