# MarketBob

A Laravel-based digital marketplace platform for buying and selling digital products, built with Laravel 10 and modern web technologies.

## 🚀 Features

- **Digital Marketplace**: Complete platform for selling digital products
- **User Management**: Author profiles, portfolios, and follower system
- **Payment Integration**: Multiple payment gateways including PayPal, Stripe, Razorpay, Flutterwave, and more
- **Item Management**: Upload, manage, and sell digital products with versioning
- **Review System**: Customer reviews and ratings
- **License Management**: Support for free and premium licenses
- **Referral System**: Built-in affiliate/referral program
- **KYC Verification**: Know Your Customer verification system
- **Withdrawal System**: Author earnings and withdrawal management
- **Newsletter Integration**: MailChimp integration
- **Multi-currency Support**: Support for multiple currencies
- **Blog System**: Built-in blog functionality
- **Help Center**: Customer support and FAQ system
- **Two-Factor Authentication**: Enhanced security with 2FA
- **API Support**: RESTful API with Laravel Sanctum

## 📋 Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Apache/Nginx web server

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yasirraheel/marketbob.git
cd marketbob
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

Configure your database connection in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketbob
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

### 5. Storage Link

Create the symbolic link for storage:

```bash
php artisan storage:link
```

### 6. Build Assets

```bash
# For development
npm run dev

# For production
npm run prod
```

### 7. Start the Application

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 🔧 Configuration

### Payment Gateways

Configure payment gateways in the `.env` file:

- **PayPal**: `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`
- **Stripe**: `STRIPE_KEY`, `STRIPE_SECRET`
- **Razorpay**: Configure in admin panel
- **Flutterwave**: Configure in admin panel
- **And many more...**

### Email Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### AWS S3 (Optional)

For cloud storage:

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

## 📁 Project Structure

```
marketbob/
├── app/                    # Application core files
│   ├── Classes/           # Custom classes
│   ├── Http/              # Controllers, Middleware
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic services
│   └── Traits/            # Reusable traits
├── config/                # Configuration files
├── database/              # Migrations, seeders, factories
├── public/                # Public assets
├── resources/             # Views, assets
│   └── views/
│       └── themes/        # Theme files
├── routes/                # Route definitions
│   ├── web.php           # Web routes
│   ├── admin.php         # Admin routes
│   ├── api.php           # API routes
│   └── payments.php      # Payment routes
├── storage/               # Logs, cache, uploads
├── tests/                 # Automated tests
└── vironeer/             # Custom packages
```

## 🔑 Admin Access

Access the admin panel at:
```
https://your-domain.com/admin
```

Default admin credentials will be created during installation.

## 🎨 Theming

Themes are located in `resources/views/themes/`. The default theme is `basic`.

## 🔐 Security

- Enable 2FA for admin accounts
- Keep dependencies updated: `composer update` and `npm update`
- Use strong passwords and API keys
- Configure proper file permissions
- Enable HTTPS in production

## 📝 License

This project is proprietary software. Please check the license agreement for usage terms.

## 🤝 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the development team

## 🌟 Credits

Built with:
- [Laravel Framework](https://laravel.com)
- [Bootstrap](https://getbootstrap.com)
- [jQuery](https://jquery.com)
- [CKEditor](https://ckeditor.com)

## 📊 Version

Current Version: 1.0.0

---

Made with ❤️ by the MarketBob Team
