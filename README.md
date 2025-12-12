# What To Eat - Meal Planner

A Progressive Web App (PWA) for tracking meals and getting suggestions based on what you haven't eaten in a while. Built with Laravel and featuring offline support.

## Features

- **Meal Tracking**: Record meals with food items, meal types (breakfast, lunch, snack, dinner), and timestamps
- **Smart Suggestions**: Get suggestions for meal combinations and individual food items you haven't eaten recently
- **Tagging System**: Organize meals and food items with tags and tag categories
- **Filtering**: Filter suggestions by meal type and tags
- **Food Item Management**: View, edit, and delete food items
- **Meal History**: View all your recorded meals with pagination
- **Offline Support**: View cached pages (suggestions, meal history) even when offline
- **PWA Ready**: Install as a standalone app on mobile devices
- **Responsive Design**: Works seamlessly on desktop and mobile devices

## Requirements

- PHP 8.3 or higher
- Composer
- Node.js and npm
- SQLite (or MySQL/PostgreSQL)
- Apache with mod_rewrite enabled
- OpenSSL (for SSL certificate generation)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd what-to-eat
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Edit `.env` and configure:

```env
APP_NAME="What To Eat"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://app.whattoeat.lan
APP_TIMEZONE=America/New_York  # Set your timezone

DB_CONNECTION=sqlite
# DB_DATABASE will default to database/database.sqlite
```

### 5. Create SQLite Database

```bash
touch database/database.sqlite
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Build Frontend Assets

For production:

```bash
npm run build
```

For development (with hot reload):

```bash
npm run dev
```

### 8. Set Permissions

Ensure the web server can write to the database directory:

```bash
sudo chown -R www-data:www-data database/
sudo chmod 775 database/
sudo chmod 664 database/database.sqlite
```

### 9. Configure Apache

#### Option A: Using a Virtual Host (Recommended)

Create a virtual host configuration pointing to the `public` directory:

```apache
<VirtualHost *:80>
    ServerName app.whattoeat.lan
    DocumentRoot /var/www/html/what-to-eat/public

    <Directory /var/www/html/what-to-eat/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Option B: Using Document Root

If your Apache document root is `/var/www/html`, create a symlink or configure the document root to point to the `public` directory.

### 10. Enable HTTPS (Required for PWA)

Run the SSL setup script:

```bash
./setup-ssl.sh
```

This will:
- Enable Apache SSL module
- Generate a self-signed certificate for `app.whattoeat.lan`
- Create HTTPS virtual host
- Create HTTP to HTTPS redirect

**Note**: You'll need to accept the self-signed certificate warning in your browser.

### 11. Update Hosts File (Optional)

If using a custom domain, add it to `/etc/hosts`:

```
127.0.0.1 app.whattoeat.lan
```

### 12. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Usage

### Accessing the App

- **Web**: `https://app.whattoeat.lan` (or your configured domain)
- **Development**: `http://localhost:8000` (if using `php artisan serve`)

### Basic Workflow

1. **Add a Meal**: Click "Add Meal" and enter food items, select meal type, date, and optional tags
2. **View Suggestions**: The home page shows meal combinations and food items you haven't eaten recently
3. **Filter Suggestions**: Use meal type filters (Breakfast/Snack, Lunch/Dinner) and tag filters
4. **Accept Suggestions**: Click "Accept" to quickly add a suggested meal, or "Customize" to modify it first
5. **Manage Food Items**: View, edit, or delete food items from the "Manage Food Items" page
6. **Manage Tags**: Create tag categories and tags to organize your meals

### Meal Types and Default Times

- **Breakfast**: 8:00 AM
- **Lunch**: 12:00 PM
- **Snack**: 5:00 PM
- **Dinner**: 9:00 PM

Times are automatically set based on the meal type and your configured timezone.

## Installing as a Mobile App (PWA)

### Android (Chrome/Edge)

1. Open the app in Chrome or Edge
2. Tap the menu (three dots) → "Install app" or "Add to Home screen"
3. Tap "Install" or "Add"
4. The app icon will appear on your home screen

### iOS (Safari)

1. Open the app in Safari
2. Tap the Share button (square with arrow)
3. Scroll and tap "Add to Home Screen"
4. Tap "Add"
5. The app icon will appear on your home screen

### After Installation

- Opens without browser UI (standalone mode)
- Works offline for viewing cached pages
- Appears on home screen like a native app

## Development

### Running in Development Mode

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite dev server (with hot reload)
npm run dev
```

### Building for Production

```bash
npm run build
```

### Running Migrations

```bash
php artisan migrate
```

### Creating New Migrations

```bash
php artisan make:migration create_example_table
```

## Project Structure

```
what-to-eat/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── MealController.php
│   │       ├── FoodItemController.php
│   │       └── TagController.php
│   └── Models/
│       ├── Meal.php
│       ├── FoodItem.php
│       ├── Tag.php
│       └── TagCategory.php
├── database/
│   ├── migrations/
│   └── database.sqlite
├── public/
│   ├── sw.js (Service Worker)
│   ├── manifest.json (PWA Manifest)
│   ├── icon-192.png
│   ├── icon-512.png
│   └── index.php
├── resources/
│   ├── views/
│   │   ├── meals/
│   │   ├── food-items/
│   │   └── tags/
│   ├── css/
│   └── js/
└── routes/
    └── web.php
```

## Troubleshooting

### 419 Page Expired Error

- Ensure `@csrf` is in all forms
- Clear Laravel cache: `php artisan config:clear`
- Check session driver in `.env` (should be `file` for local development)

### Database Permission Errors

```bash
sudo chown -R www-data:www-data database/
sudo chmod 775 database/
sudo chmod 664 database/database.sqlite
```

### Service Worker Not Registering

- Ensure you're using HTTPS (or localhost)
- Check browser console for errors
- Verify service worker file is accessible at `/sw.js`
- Clear browser cache and reload

### PWA Not Installing

- Verify HTTPS is working
- Check that service worker is active (DevTools → Application → Service Workers)
- Verify manifest is valid (DevTools → Application → Manifest)
- Ensure icons are accessible
- Visit `/pwa-check.html` for diagnostic information

### Vite Assets Not Loading

- Run `npm run build` for production
- Or run `npm run dev` for development
- Check that Vite dev server is running if using development mode

## Configuration

### Timezone

Set your timezone in `.env`:

```env
APP_TIMEZONE=America/New_York
```

Then clear config cache:

```bash
php artisan config:clear
```

### Meal Type Times

Default meal times are defined in `MealController::getMealTimes()`. Times are set in your configured timezone and stored as UTC in the database.

## Security Notes

- SSL certificates (`*.key`, `*.crt`) are in `.gitignore` and should not be committed
- Self-signed certificates are for local development only
- For production, use proper SSL certificates from a trusted CA
- Keep `.env` file secure and never commit it
