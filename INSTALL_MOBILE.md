# Installing "What To Eat" as a Mobile Web App

This app is a Progressive Web App (PWA) that can be installed on your mobile device and used like a native app.

## Installation Instructions

### Android (Chrome/Edge)

1. **Open the app** in Chrome or Edge browser on your Android device
2. **Look for the install prompt**:
   - You may see a banner at the bottom saying "Add to Home screen" or "Install app"
   - Tap "Install" or "Add"
3. **If you don't see the prompt**:
   - Tap the **menu button** (three dots) in the top-right corner
   - Select **"Add to Home screen"** or **"Install app"**
   - Tap **"Add"** or **"Install"** to confirm
4. The app icon will appear on your home screen
5. Tap the icon to open the app in standalone mode (without browser UI)

### iOS (Safari)

1. **Open the app** in Safari on your iPhone or iPad
2. **Tap the Share button** (square with arrow pointing up) at the bottom
3. **Scroll down** and tap **"Add to Home Screen"**
4. **Customize the name** if desired (default: "WhatToEat")
5. **Tap "Add"** in the top-right corner
6. The app icon will appear on your home screen
7. Tap the icon to open the app in standalone mode

### iOS (Chrome/Edge)

1. **Open the app** in Chrome or Edge on your iPhone/iPad
2. **Tap the menu button** (three dots) in the bottom-right
3. **Select "Add to Home Screen"**
4. **Tap "Add"** to confirm
5. The app will open in Safari's standalone mode

## After Installation

- The app will open **without browser UI** (no address bar, no tabs)
- It will work **offline** for viewing cached pages (suggestions, meal history, etc.)
- Creating/editing meals requires an internet connection
- The app icon will appear on your home screen like a native app

## Requirements

- **HTTPS**: The app must be accessed via HTTPS (or localhost) for installation
- **Service Worker**: Already configured and working
- **Manifest**: Already configured and linked

## Troubleshooting

### "Add to Home Screen" option not appearing?

1. **Make sure you're using HTTPS** (not HTTP)
2. **Clear browser cache** and reload the page
3. **Check if service worker is registered**: 
   - Open browser DevTools → Application → Service Workers
   - You should see the service worker registered
4. **Try a different browser** (Chrome, Edge, Safari)

### App not working offline?

- The service worker caches read-only pages (suggestions, lists)
- Creating/editing requires internet connection
- Make sure you've visited the pages at least once while online

### Icon not showing correctly?

- The app uses the favicon.ico as the app icon
- For better icons, you can add PNG images (192x192 and 512x512) to `/public/` and update `manifest.json`

