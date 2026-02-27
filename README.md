# Istanbul Airport Transfer WordPress Plugin

A comprehensive WordPress plugin for managing airport transfer reservations between Istanbul Airport (IST) and Sabiha Gökçen Airport (SAW) with 13 service zones across Istanbul.

## 📋 Features

- **Multi-step Booking Form**: Date/time, addresses, passengers, flight code
- **Address Autocomplete**: Yandex/Google Maps with 20 API keys rotation
- **GeoJSON Zone Detection**: 13 zones + 2 airports with point-in-polygon detection
- **Zone-to-Zone Pricing**: 50+ route combinations with EUR pricing
- **Booking State Machine**: pending → confirmed/auto_confirmed/cancelled
- **Email Notifications**: Admin and customer notifications via WP Mail SMTP
- **Admin Dashboard**: Manage zones, pricing, APIs, and bookings
- **Return Trip Support**: Optional return booking with same pricing
- **Security Features**: reCAPTCHA v3, rate limiting, prepared statements

## 🛠️ Technical Requirements

- **PHP**: 8.0+
- **WordPress**: 6.0+
- **Database**: MySQL 5.7+
- **Browser**: Modern browsers with ES6+ support

## 🚀 Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/` directory
3. Activate the plugin from WordPress admin
4. Configure settings in "Istanbul Airport Transfer" menu

## 📁 Plugin Structure

```
istanbul-airport-transfer/
├── includes/                    # Core PHP classes
│   ├── class-iat-main.php      # Main plugin class
│   ├── class-iat-activator.php # Database setup
│   ├── class-iat-db-manager.php # Database operations
│   ├── class-iat-security.php  # Security features
│   └── ...
├── admin/                       # Admin interface
│   ├── class-iat-admin-regions.php
│   ├── class-iat-admin-pricings.php
│   └── views/                  # Admin templates
├── assets/                      # Frontend assets
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript files
│   └── images/                 # Plugin images
├── languages/                   # Translation files
├── tests/                       # Unit tests
└── istanbul-airport-transfer.php # Main plugin file
```

## ⚙️ Configuration

### API Keys
- **Yandex Maps**: 10 API keys for autocomplete
- **Google Maps**: 10 API keys for autocomplete
- **Nominatim**: Free geocoding (primary)
- **reCAPTCHA v3**: Site and secret keys

### Contact Information
- Admin email for undefined zones
- WhatsApp contact number
- Phone number

### Booking Settings
- Minimum advance booking time (default: 24 hours)
- Maximum passengers (1-5)
- Maximum luggage (1-5)
- Currency (EUR)

## 🎯 Usage

### For Customers
1. Visit the booking page via shortcode `[iat_booking_form]`
2. Enter pickup and drop-off addresses
3. Select date, time, and passenger details
4. Review quote and confirm booking
5. Receive confirmation email

### For Administrators
1. **Manage Zones**: Add/edit/delete service zones with GeoJSON
2. **Manage Pricing**: Set zone-to-zone prices
3. **Manage Bookings**: View, confirm, or cancel reservations
4. **API Management**: Configure and monitor API usage
5. **Settings**: Configure contact info and system settings

## 🔒 Security Features

- **SQL Injection Protection**: Prepared statements for all database queries
- **XSS Prevention**: Input sanitization and output escaping
- **CSRF Protection**: Nonce verification for all forms
- **Rate Limiting**: IP-based request limiting
- **reCAPTCHA v3**: Bot protection
- **API Key Security**: Masked display in admin interface

## 📊 Database Schema

The plugin creates 7 custom tables:

- `wp_iat_regions` - Service zones with GeoJSON polygons
- `wp_iat_pricings` - Zone-to-zone pricing matrix
- `wp_iat_bookings` - Reservation data
- `wp_iat_options` - Service options (TV vehicle, child seat, etc.)
- `wp_iat_booking_options` - Selected options per booking
- `wp_iat_api_usage` - API usage tracking
- `wp_iat_geocache` - Geocoding result cache

## 🌐 Supported Zones

### European Side (7 zones)
- Bolge-Yarim
- Bolge-BirBucuk
- Bolge-Bir
- Bolge-Iki
- Bolge-IkiBucuk
- Bolge-BirBucuk-Sariyer
- Bolge-Iki-Sariyer

### Asian Side (4 zones)
- Anadolu-Yarim
- Anadolu-Bir
- Anadolu-BirBucuk
- Anadolu-Iki

### Airports (2)
- Istanbul Airport (IST)
- Sabiha Gökçen Airport (SAW)

## 📧 Email Templates

The plugin includes 5 email templates:
1. `booking_new_admin` - Admin notification with action links
2. `booking_pending_customer` - Customer booking confirmation
3. `booking_confirmed` - Booking confirmed notification
4. `booking_auto_confirmed` - Auto-confirmation after 24 hours
5. `booking_cancelled` - Cancellation confirmation

## 🔧 Development

### Building Assets
```bash
npm install
npm run build    # Production build
npm run dev      # Development with watch
```

### Running Tests
```bash
composer install
vendor/bin/phpunit
```

### Code Standards
The plugin follows:
- PSR-12 PHP coding standards
- WordPress coding standards
- OOP with namespaces
- Singleton pattern for main classes

## 🤝 Contributing

### Multi-AI Collaboration Workflow

This project supports collaboration between multiple AI assistants using a structured workflow:

1. **File Locking**: Create a lock file in `.locks/[filename].lock` before modifying any file
2. **AI Handoff Markers**: Add handoff comments in files when switching between AIs:
   ```php
   // AI Handoff: [AI Name] completed [feature/module] at [timestamp]
   // Next AI should continue with [next task]
   ```
3. **Progress Tracking**: Update `memory-bank/activeContext.md` with recent changes
4. **Documentation Updates**: Keep documentation synchronized across all AIs

### Standard Contribution Process

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

### AI-Assisted Development

For AI-assisted development:
1. Read `CODING_RULES.md` for multi-AI collaboration standards
2. Check existing lock files in `.locks/` directory
3. Update `memory-bank/progress.md` after major changes
4. Follow the handoff protocols described in the coding rules

## 📄 License

This plugin is licensed under the MIT License.
## 📚 Documentation & Planning Additions

During the initial planning phase we created a **Memory Bank** under `memory-bank/` in order to capture
architecture decisions, active context and progress. This mirrors the instructions from `.clinerules` and
ensures the project retains state across sessions. The following core files were added:

1. `projectbrief.md` – high‑level goals, requirements and constraints.
2. `productContext.md` – why the product exists and how it behaves.
3. `activeContext.md` – current focus, recent changes and next steps.
4. `systemPatterns.md` – component architecture and design patterns.
5. `techContext.md` – technologies, tooling and deployment environment.
6. `progress.md` – what works, what remains and known issues.

Each time the project progresses, update these documents with new findings or decisions so that no
planning detail is lost. The memory bank is the single source of truth for architectural and
infrastructure planning.

Additionally, the repository has been reset to the latest GitHub commit and the `prompts/` folder removed
as part of cleanup. The README and implementation plan themselves were expanded to reflect this
planning work and to provide explicit instructions for the next infrastructure tasks:

- scaffold core plugin directories and class templates
- configure development environment (WP + PHP 8 + MySQL + Node)
- set up CI/CD pipeline (composer, npm, phpunit)
- design deployment strategy and database migration process

Refer to the head of `implementation_plan.md` for the updated step‑by‑step plan.
## 🆘 Support

For support and questions:
- Check the plugin documentation
- Review the code comments
- Submit issues on GitHub

## 📝 Changelog

### v1.0.0
- Initial release
- Core booking functionality
- Admin dashboard
- Multi-zone support
- Email notifications