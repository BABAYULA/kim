# Implementation Plan

## [Overview]

WordPress eklentisinin admin panel tabanlı yönetim sistemi ile özel veritabanı tablolarını, bölgeleri ve fiyatlandırmayı yönetecek temel mimariyi oluşturmak.

Bu modül, İstanbul Havalimanı Transfer eklentisinin temel yönetim altyapısını oluşturur. Admin panelinden bölgeleri, fiyatlandırmaları, opsiyonları ve rezervasyonları yönetebilecek bir sistem kurar. Özel veritabanı tabloları ve admin sayfaları kullanılır. Bu yapı, fiyat_listesi.md dosyasındaki 13 bölge (11 bölge + 2 havaalanı) ve tüm fiyatların admin panelinden yönetilmesini sağlar.

**Dil:** Sadece İngilizce (v1). WPML desteği v2'de eklenebilir.
**Ödeme:** Sadece nakit (v1). Online ödeme v2'de eklenebilir (şema hazır).

## [Types]

**Özel Veritabanı Tabloları - Bölgeler:**

```php
// wp_iat_regions - Bölgeleri (GeoJSON polygonlar) saklar
interface IAT_Region_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    region_name: varchar(100) NOT NULL,    // Bölge adı: "Bölge 1"
    zone_code: varchar(50) UNIQUE NOT NULL, // Bolge-Bir, IST, SAW vb.
    zone_type: enum('european', 'anatolian', 'airport') NOT NULL,
    geojson: text NOT NULL,                // JSON formatında polygon koordinatları
    base_price_intra: decimal(10, 2) DEFAULT 0.00, // Bölge içi fiyat (EUR)
    created_at: datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (zone_type)
}
```

**Özel Veritabanı Tabloları - Fiyatlandırmalar:**

```php
// wp_iat_pricings - Bölge arası fiyatlandırmayı saklar
interface IAT_Pricing_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    from_zone_code: varchar(50) NOT NULL,  // Kaynak bölge kodu
    to_zone_code: varchar(50) NOT NULL,    // Hedef bölge kodu
    price_eur: decimal(10, 2) NOT NULL,    // Fiyat (EUR)
    is_bidirectional: tinyint(1) DEFAULT 1, // 1 = çift yönlü, 0 = tek yönlü
    created_at: datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pricing (from_zone_code, to_zone_code),
    INDEX (from_zone_code),
    INDEX (to_zone_code),
    FOREIGN KEY (from_zone_code) REFERENCES wp_iat_regions(zone_code) ON DELETE CASCADE,
    FOREIGN KEY (to_zone_code) REFERENCES wp_iat_regions(zone_code) ON DELETE CASCADE
}
```

**Özel Veritabanı Tabloları - Rezervasyonlar:**

```php
// wp_iat_bookings - Rezervasyonları saklar
interface IAT_Booking_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    booking_id: varchar(32) UNIQUE NOT NULL, // Random hash (32 karakter hex)
    status: enum('pending', 'confirmed', 'auto_confirmed', 'cancelled') DEFAULT 'pending',
    
    // Return Trip bağlantısı
    linked_booking_id: bigint NULL,             // Return trip için bağlı booking ID
    is_return_trip: tinyint(1) DEFAULT 0,       // 1 = bu dönüş yolculuğu
    
    // Pickup bilgileri
    pickup_address: text NOT NULL,
    pickup_lat: decimal(10, 7),
    pickup_lng: decimal(10, 7),
    pickup_yandex_link: varchar(500),
    pickup_zone_code: varchar(50),
    
    // Dropoff bilgileri
    dropoff_address: text NOT NULL,
    dropoff_lat: decimal(10, 7),
    dropoff_lng: decimal(10, 7),
    dropoff_yandex_link: varchar(500),
    dropoff_zone_code: varchar(50),
    
    // Seyahat detayları
    pickup_datetime: datetime NOT NULL,    // Europe/Istanbul timezone
    flight_code: varchar(10),              // TK1234 (opsiyonel)
    has_tv_option: tinyint(1) DEFAULT 0,   // 1 = +10 EUR TV option
    
    // Yolcu bilgileri
    passenger_count: tinyint DEFAULT 1,    // 1-5
    luggage_count: tinyint DEFAULT 1,      // 1-5
    passenger_names: json,                 // JSON array of strings
    contact_phone: varchar(20) NOT NULL,   // E.164 format: +905551234567
    contact_email: varchar(100) NOT NULL,
    
    // Fiyat
    price_eur: decimal(10, 2) NOT NULL,
    currency: varchar(3) DEFAULT 'EUR',
    payment_method: varchar(20) DEFAULT 'cash', // v1: always 'cash', v2: online ödeme eklenebilir
    
    // Token
    cancellation_token: varchar(64),           // Tek iptal tokeni (müşteri + admin)
    
    // Sistem
    auto_confirm_deadline: datetime,
    recaptcha_score: decimal(3, 2),        // 0.0-1.0
    created_at: datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX (status),
    INDEX (pickup_zone_code),
    INDEX (dropoff_zone_code),
    INDEX (pickup_datetime),
    INDEX (booking_id)
}
```

**Özel Veritabanı Tabloları - Opsiyonlar:**

```php
// wp_iat_options - Admin tarafından yönetilen opsiyonlar (TV araç, çocuk koltuğu vb.)
interface IAT_Options_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    option_name: varchar(100) NOT NULL,        // "TV Vehicle", "Child Seat"
    option_slug: varchar(50) UNIQUE NOT NULL,   // "tv-vehicle", "child-seat"
    price_eur: decimal(10,2) NOT NULL DEFAULT 0,// Opsiyon fiyatı (EUR)
    description: text,                          // Açıklama
    is_active: tinyint(1) DEFAULT 1,           // Aktif/Pasif
    sort_order: int DEFAULT 0,                  // Sıralama
    created_at: datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
}
```

**Özel Veritabanı Tabloları - Booking Opsiyonları:**

```php
// wp_iat_booking_options - Rezervasyonda seçilen opsiyonlar
interface IAT_Booking_Options_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    booking_id: bigint NOT NULL,
    option_id: bigint NOT NULL,
    option_price_eur: decimal(10,2) NOT NULL,   // Satın alma anındaki fiyat
    FOREIGN KEY (booking_id) REFERENCES wp_iat_bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES wp_iat_options(id) ON DELETE CASCADE
}
```

**Özel Veritabanı Tabloları - API Kullanım Takibi:**

```php
// wp_iat_api_usage - API kullanım takibi
interface IAT_API_Usage_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    api_provider: varchar(20),             // 'yandex' | 'google'
    api_key_index: tinyint,                // 1-10
    call_date: date,
    call_count: int DEFAULT 0,
    monthly_count: int DEFAULT 0,
    last_call_time: datetime,
    UNIQUE KEY (api_provider, api_key_index, call_date)
}
```

**Özel Veritabanı Tabloları - Geocaching Önbelleği:**

```php
// wp_iat_geocache - Geocoding sonuçları önbellek
interface IAT_Geocache_Table {
    id: bigint AUTO_INCREMENT PRIMARY KEY,
    address_hash: varchar(64),             // md5(lower(trim(address)))
    lat: decimal(10, 7),
    lng: decimal(10, 7),
    formatted_address: text,
    zone_code: varchar(50),
    created_at: datetime,
    updated_at: datetime,
    UNIQUE KEY (address_hash),
    INDEX (zone_code)
}
```

**WordPress Settings:**

```php
interface IAT_Settings {
    // API Keys (10 key per provider)
    yandex_api_keys: array[string],
    google_api_keys: array[string],
    api_rotation_order: array,             // [{provider: 'yandex', index: 0}, ...]
    
    // reCAPTCHA v3
    recaptcha_site_key: string,
    recaptcha_secret_key: string,
    
    // İletişim Bilgileri
    contact_email: string,
    contact_whatsapp: string,
    contact_phone: string,
    
    // Email
    sender_email: string,
    sender_name: string,
    
    // Sistem
    timezone: string,                      // Europe/Istanbul
    min_booking_hours: int,                // 24
    
    // Fiyat
    currency: string,                      // EUR
}
```

**Bölge Types:**

```php
// 13 Toplam Bölge (11 bölge + 2 havaalanı):
// Avrupa Yakası (7): Bolge-Yarim, Bolge-BirBucuk, Bolge-Bir, Bolge-Iki, 
//                    Bolge-IkiBucuk, Bolge-BirBucuk-Sariyer, Bolge-Iki-Sariyer
// Anadolu Yakası (4): Anadolu-Yarim, Anadolu-Bir, Anadolu-BirBucuk, Anadolu-Iki
// Havaalanları (2): IstanbulAirport (IST), SabihaAirport (SAW)

enum Zone_Type {
    EUROPEAN = 'european',  // 7 Avrupa yakası bölgesi
    ANATOLIAN = 'anatolian', // 4 Anadolu yakası bölgesi
    AIRPORT = 'airport'      // 2 havalimanı
}
```

## [Files]

**Yeni Dosyalar:**

1. `istanbul-airport-transfer.php` - Ana eklenti dosyası
   - Plugin header metadata
   - Eklenti aktivasyon/deaktivasyon hookları
   - Main class başlatma

2. `includes/class-iat-main.php` - Ana eklenti sınıfı
   - Singleton pattern
   - Tüm modülleri yükler
   - Activation/deactivation yönetimi

3. `includes/class-iat-activator.php` - Aktivasyon sınıfı
   - 7 özel tablo oluşturma
   - Varsayılan ayarları ekleme
   - Varsayılan bölgeleri ekleme (13 bölge)
   - Fiyat listesini import etme (fiyat_listesi.md'den)
   - Varsayılan opsiyonları ekleme (TV Vehicle: +10€)
   - Rewrite rules flush

4. `includes/class-iat-deactivator.php` - Deaktivasyon sınıfı
   - Temizlik işlemleri (opsiyonel)

5. `includes/database/class-iat-db-manager.php` - Veritabanı yöneticisi
   - 7 özel tabloyu oluşturma
   - Tablo güncellemeleri
   - CRUD işlemleri için helper metodlar

6. `includes/admin/class-iat-admin-regions.php` - Bölgeler admin sayfası
   - Bölge listesi tablosu
   - Bölge ekleme/düzenleme formu
   - GeoJSON upload/girişi
   - Bölge içi fiyat girişi
   - Silme işlemleri

7. `includes/admin/class-iat-admin-pricings.php` - Fiyatlandırmalar admin sayfası
   - Fiyat listesi tablosu
   - Fiyat ekleme/düzenleme formu
   - Çift yönlü fiyat desteği
   - Silme işlemleri
   - fiyat_listesi.md'den bulk import

8. `includes/admin/class-iat-admin-bookings.php` - Rezervasyonlar admin sayfası
   - Rezervasyon listesi tablosu
   - Detay görüntüleme
   - Status değiştirme (pending → confirmed)
   - Rezervasyon silme
   - Filtreleme (status, tarih, bölge)

9. `includes/admin/class-iat-settings.php` - Genel ayarlar sayfası
   - Settings API kullanarak admin paneli
   - API key girişi (10 key x 2 provider)
   - API rotation sıralaması
   - İletişim bilgileri
   - reCAPTCHA ayarları

10. `includes/utils/class-iat-helper.php` - Yardımcı fonksiyonlar
    - Booking ID oluşturma (random hash)
    - Token oluşturma (cancel link için)
    - Tarih formatlama (Europe/Istanbul)
    - Tel numarası validasyonu
    - Email validasyonu
    - Fiyat hesaplama helper'ları

11. `includes/security/class-iat-security.php` - Güvenlik sınıfı
    - Nonce oluşturma ve validasyon
    - Prepared statement wrapper'ları
    - Rate limiting kontrolü
    - reCAPTCHA v3 validasyon
    - Map provider: Leaflet.js with OpenStreetMap (Free alternative)

12. `includes/import/class-iat-price-importer.php` - Fiyat import sınıfı
    - fiyat_listesi.md dosyasını okuma
    - Bölgeleri otomatik oluşturma
    - Fiyatları otomatik import etme
    - Update/overwrite mekanizması

13. `assets/css/admin-styles.css` - Admin panel CSS
    - Bölgeler sayfası stilleri
    - Fiyatlandırmalar sayfası stilleri
    - Rezervasyonlar sayfası stilleri
    - Settings page stilleri
    - GeoJSON preview stilleri

14. `assets/js/admin-regions.js` - Bölgeler sayfası JavaScript
    - GeoJSON validation
    - Map preview (Leaflet.js)
    - Form validasyonları

15. `assets/js/admin-pricings.js` - Fiyatlandırmalar sayfası JavaScript
    - Region dropdown population
    - Fiyat hesaplama preview
    - Form validasyonları

16. `assets/js/admin-bookings.js` - Rezervasyonlar sayfası JavaScript
    - Status değiştirme
    - Filtreleme
    - Ajax işlemleri

17. `assets/js/admin-settings.js` - Settings page JavaScript
    - API key girişi için dinamik field'lar
    - Drag-drop sıralama
    - Form validasyonları

18. `readme.txt` - WordPress plugin readme
    - Plugin açıklaması
    - Kurulum talimatları
    - Admin panel kullanım kılavuzu
    - Changelog

19. `.gitignore` - Git ignore dosyası
    - node_modules
    - IDE dosyaları
    - .env

**Konfigürasyon Dosyaları:**

20. `composer.json` - PHP bağımlılıkları
    - PHP 8.0+ gereksinimi
    - WordPress coding standards

21. `package.json` - NPM bağımlılıkları
    - Webpack veya browserify için
    - CSS/JS build scripts
    - Leaflet.js (map için)

**Yeni Dosyalar (Önceki listeye ek):**

22. `includes/admin/class-iat-admin-options.php` - Opsiyonlar admin sayfası
    - Opsiyon listesi tablosu
    - Opsiyon ekleme/düzenleme formu (İsim, slug, fiyat, açıklama, aktif/pasif)
    - Sıralama (sort_order)
    - Silme işlemleri

23. `includes/class-iat-email-manager.php` - Email yönetimi
    - 5 email şablonu (İngilizce)
    - Admin bildirim emaili (yeni rezervasyon + confirm/cancel linkleri)
    - Müşteri emailleri (pending, confirmed, auto_confirmed, cancelled)
    - WP Mail SMTP entegrasyonu

24. `includes/class-iat-cron-manager.php` - Cron job yönetimi
    - Saatlik: 24h geçen pending → auto_confirmed
    - Günlük: 6 aydan eski rezervasyonları sil
    - Haftalık: 30 günden eski geocache'i sil

25. `includes/class-iat-shortcodes.php` - Shortcode yönetimi
    - `[iat_booking_form]` → Frontend booking formunu render eder

26. `includes/frontend/class-iat-booking-form.php` - Frontend form render & AJAX
    - Multi-step form HTML render
    - AJAX handler'lar (geocode, quote, book)
    - Return trip toggle işlevi

27. `includes/frontend/class-iat-pricing-engine.php` - Fiyat hesaplama
    - Zone-to-zone fiyat arama
    - Opsiyon fiyatlarını ekleme
    - Return trip fiyat hesaplama (2x birim fiyat)

28. `includes/geocoding/class-iat-nominatim.php` - Ücretsiz geocoding
    - Nominatim (OpenStreetMap) API
    - Rate limit: 1 req/sec
    - Birincil geocoding provider

29. `includes/geocoding/class-iat-yandex-geocoder.php` - Yandex geocoding
    - Autocomplete + geocoding
    - Yedek provider

30. `includes/geocoding/class-iat-google-geocoder.php` - Google geocoding
    - Autocomplete + geocoding
    - Yedek provider

31. `includes/zones/class-iat-zone-detector.php` - Bölge tespit
    - Point-in-polygon (Ray Casting)
    - Overlap resolution (çakışma: en küçük alan seçilir)
    - Cache entegrasyonu

32. `uninstall.php` - Eklenti silme
    - Tüm custom tabloları DROP
    - wp_options'tan iat_* kayıtları sil
    - Cron job'ları kaldır

33. `assets/css/public-booking.css` - Frontend booking form CSS
    - Multi-step form stilleri
    - Responsive tasarım (mobile-first)
    - Return trip toggle stilleri

34. `assets/js/public-booking-form.js` - Frontend booking form JS (Vanilla JS ES6+)
    - Multi-step form yönetimi
    - Address autocomplete (debounce 300ms)
    - Return trip toggle UI
    - reCAPTCHA v3 entegrasyonu
    - AJAX istekleri

35. `assets/js/admin-options.js` - Opsiyonlar admin sayfası JS
    - Form validasyonları
    - Sıralama işlevleri

## [Functions]

**Yeni Fonksiyonlar - Helper:**

1. `iat_generate_booking_id(): string`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: 32 karakter hexadecimal random hash oluşturur
   - Dönüş: "a1b2c3d4e5f6...32karakter"
   - Örnek: bin2hex(random_bytes(16))

2. `iat_generate_token(): string`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: Cancel link için güvenli token oluşturur
   - Dönüş: 64 karakter random string
   - Örnek: bin2hex(random_bytes(32))

3. `iat_validate_phone(string $phone): bool`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: E.164 formatında telefon numarası validasyonu
   - Örnek: +905551234567 → true, 05551234567 → false
   - Regex: /^\+[1-9]\d{1,14}$/

4. `iat_validate_email(string $email): bool`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: Email validasyonu
   - Kullanım: is_email() WordPress fonksiyonu

5. `iat_format_datetime(string $datetime, string $format = 'Y-m-d H:i:s'): string`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: Europe/Istanbul timezone ile tarih formatlama
   - Kullanım: date_default_timezone_set('Europe/Istanbul')

6. `iat_get_timezone_datetime(): string`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: Mevcut zaman (Europe/Istanbul) döner
   - Dönüş: '2026-02-26 22:00:00'

7. `iat_add_hours(string $datetime, int $hours): string`
   - Dosya: `includes/utils/class-iat-helper.php`
   - Açıklama: Tarihe saat ekler
   - Kullanım: strtotime() + date()

**Yeni Fonksiyonlar - Bölge Yönetimi:**

8. `iat_get_region_by_code(string $zone_code): ?array`
   - Dosya: `includes/database/class-iat-db-manager.php`
   - Açıklama: Bölge koduna göre bölge getirir
   - Dönüş: Bölge dizisi veya null

9. `iat_get_all_regions(array $filters = []): array`
   - Dosya: `includes/database/class-iat-db-manager.php`
   - Açıklama: Tüm bölgeleri getirir (filtrelenebilir)
   - Parametre: zone_type (european/anatolian/airport)

10. `iat_create_region(array $data): int|false`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Yeni bölge oluşturur
    - Dönüş: Yeni bölge ID veya false

11. `iat_update_region(int $id, array $data): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Bölge günceller
    - Dönüş: true/false

12. `iat_delete_region(int $id): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Bölge siler (CASCADE: ilgili fiyatları da siler)
    - Dönüş: true/false

**Yeni Fonksiyonlar - Fiyat Yönetimi:**

13. `iat_get_pricing(string $from_zone, string $to_zone): ?array`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: İki bölge arasındaki fiyatı getirir
    - Not: Çift yönlü fiyatları kontrol eder

14. `iat_get_all_pricings(array $filters = []): array`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Tüm fiyatlandırmaları getirir

15. `iat_create_pricing(array $data): int|false`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Yeni fiyatlandırma oluşturur
    - Dönüş: Yeni fiyat ID veya false

16. `iat_update_pricing(int $id, array $data): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Fiyat günceller
    - Dönüş: true/false

17. `iat_delete_pricing(int $id): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Fiyat siler
    - Dönüş: true/false

18. `iat_bulk_import_pricing(string $filepath): int`
    - Dosya: `includes/import/class-iat-price-importer.php`
    - Açıklama: fiyat_listesi.md dosyasından fiyatları import eder
    - Dönüş: Import edilen fiyat sayısı

**Yeni Fonksiyonlar - Rezervasyon Yönetimi:**

19. `iat_create_booking(array $data): int|false`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Yeni rezervasyon oluşturur
    - Dönüş: Yeni rezervasyon ID veya false

20. `iat_get_booking(int $id): ?array`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Rezervasyon getirir

21. `iat_get_booking_by_id(string $booking_id): ?array`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Booking ID ile rezervasyon getirir

22. `iat_update_booking_status(int $id, string $status): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Rezervasyon status günceller

23. `iat_cancel_booking(string $token): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Cancel token ile rezervasyon iptal eder (Rezervasyon saatine 1 saat kalana kadar aktif)

**Yeni Fonksiyonlar - Güvenlik:**

24. `iat_create_nonce(string $action = 'iat_default'): string`
    - Dosya: `includes/security/class-iat-security.php`
    - Açıklama: WordPress nonce oluşturma
    - Kullanım: wp_create_nonce()

25. `iat_verify_nonce(string $nonce, string $action = 'iat_default'): bool`
    - Dosya: `includes/security/class-iat-security.php`
    - Açıklama: WordPress nonce validasyonu
    - Kullanım: wp_verify_nonce()

26. `iat_check_rate_limit(string $ip, int $max_requests = 10, int $timeframe = 60): bool`
    - Dosya: `includes/security/class-iat-security.php`
    - Açıklama: IP bazlı rate limiting
    - Dönüş: true (izin ver) | false (engelle)

27. `iat_verify_recaptcha(string $token): bool`
    - Dosya: `includes/security/class-iat-security.php`
    - Açıklama: reCAPTCHA v3 score validasyonu
    - API: https://www.google.com/recaptcha/api/siteverify
    - Min score: 0.5

28. `iat_sanitize_input(string $input): string`
    - Dosya: `includes/security/class-iat-security.php`
    - Açıklama: Input sanitization

29. `iat_prepare_sql(string $sql, array $params): string`
    - Dosya: `includes/security/class-iat-security.php`
    - Açıklama: Prepared statement wrapper (SQL injection koruması)

**Yeni Fonksiyonlar - API & Cache:**

30. `iat_log_api_usage(string $provider, int $index): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: API kullanımını log'lar

31. `iat_get_api_usage(string $provider, int $index, string $date): ?array`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: API kullanım bilgisi getirir

32. `iat_cache_geocoding(string $address, array $data): bool`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Geocaching sonucunu cache'ler

33. `iat_get_geocache(string $address): ?array`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Cache'ten geocaching sonucu getirir

34. `iat_delete_old_geocache(int $days = 30): int`
    - Dosya: `includes/database/class-iat-db-manager.php`
    - Açıklama: Eski cache'leri temizler
    - Dönüş: Silinen kayıt sayısı

## [Classes]

**Yeni Sınıflar:**

1. **IAT_Main** (`includes/class-iat-main.php`)
   - Singleton pattern
   - Metodlar:
     - `public static function get_instance(): IAT_Main`
     - `public function __construct()`: Hook'ları kaydeder
     - `public function run()`: Tüm modülleri başlatır
     - `public function activate()`: Aktivasyon işlemleri
     - `public function deactivate()`: Deaktivasyon işlemleri

2. **IAT_Activator** (`includes/class-iat-activator.php`)
   - Singleton pattern
   - Metodlar:
     - `public static function activate()`: 
       - Özel tabloları oluştur
       - Varsayılan ayarları ekle
       - 17 bölgeyi oluştur (fiyat_listesi.md'den)
       - Tüm fiyatları import et (fiyat_listesi.md'den)
       - Rewrite rules flush

3. **IAT_Deactivator** (`includes/class-iat-deactivator.php`)
   - Singleton pattern
   - Metodlar:
     - `public static function deactivate()`: Temizlik işlemleri

4. **IAT_DB_Manager** (`includes/database/class-iat-db-manager.php`)
   - Singleton pattern
   - Metodlar:
     - `public function create_tables()`: Özel tabloları oluştur
     - `public function get_table_name(string $table): string`
     - `public function get_region_by_code(string $zone_code): ?array`
     - `public function get_all_regions(array $filters = []): array`
     - `public function create_region(array $data): int|false`
     - `public function update_region(int $id, array $data): bool`
     - `public function delete_region(int $id): bool`
     - `public function get_pricing(string $from_zone, string $to_zone): ?array`
     - `public function get_all_pricings(array $filters = []): array`
     - `public function create_pricing(array $data): int|false`
     - `public function update_pricing(int $id, array $data): bool`
     - `public function delete_pricing(int $id): bool`
     - `public function create_booking(array $data): int|false`
     - `public function get_booking(int $id): ?array`
     - `public function get_booking_by_id(string $booking_id): ?array`
     - `public function update_booking_status(int $id, string $status): bool`
     - `public function cancel_booking(string $token): bool`
     - `public function log_api_usage(string $provider, int $index): bool`
     - `public function get_api_usage(string $provider, int $index, string $date): ?array`
     - `public function cache_geocoding(string $address, array $data): bool`
     - `public function get_geocache(string $address): ?array`
     - `public function delete_old_geocache(int $days = 30): int`

5. **IAT_Admin_Regions** (`includes/admin/class-iat-admin-regions.php`)
   - Singleton pattern
   - Metodlar:
     - `public function __construct()`: Register hooks
     - `public function add_admin_menu()`: Admin menu ekle
     - `public function render_regions_page()`: Bölgeler sayfası HTML
     - `public function render_add_region_page()`: Bölge ekleme sayfası
     - `public function render_edit_region_page()`: Bölge düzenleme sayfası
     - `public function handle_region_form_submission()`: Form submit handler
     - `public function handle_region_deletion()`: Silme handler
     - `private function validate_geojson(string $geojson): bool`
     - `private function get_regions_list()`: Bölgeleri tablo halinde getir

6. **IAT_Admin_Pricings** (`includes/admin/class-iat-admin-pricings.php`)
   - Singleton pattern
   - Metodlar:
     - `public function __construct()`: Register hooks
     - `public function add_admin_menu()`: Admin menu ekle
     - `public function render_pricings_page()`: Fiyat listesi sayfası HTML
     - `public function render_add_pricing_page()`: Fiyat ekleme sayfası
     - `public function render_edit_pricing_page()`: Fiyat düzenleme sayfası
     - `public function handle_pricing_form_submission()`: Form submit handler
     - `public function handle_pricing_deletion()`: Silme handler
     - `public function handle_bulk_import()`: Bulk import handler
     - `private function get_pricings_list()`: Fiyatları tablo halinde getir

7. **IAT_Admin_Bookings** (`includes/admin/class-iat-admin-bookings.php`)
   - Singleton pattern
   - Metodlar:
     - `public function __construct()`: Register hooks
     - `public function add_admin_menu()`: Admin menu ekle
     - `public function render_bookings_page()`: Rezervasyon listesi sayfası
     - `public function render_booking_detail_page()`: Detay sayfası
     - `public function handle_status_change()`: Status değiştirme handler
     - `public function handle_booking_deletion()`: Silme handler
     - `public function ajax_get_booking_details()`: Ajax handler
     - `private function get_bookings_list(array $filters = []): array`

8. **IAT_Settings** (`includes/admin/class-iat-settings.php`)
   - Singleton pattern
   - Metodlar:
     - `public function __construct()`: Register hooks
     - `public function add_settings_page()`: Admin menu ekle
     - `public function render_settings_page()`: Settings page HTML
     - `public function register_settings()`: Settings API register
     - `public function render_api_keys_section()`: API keys section
     - `public function render_yandex_api_keys_field()`: Yandex keys input
     - `public function render_google_api_keys_field()`: Google keys input
     - `public function render_rotation_order_field()`: Drag-drop sort
     - `public function render_contact_settings_section()`: İletişim ayarları
     - `public function render_recaptcha_section()`: reCAPTCHA ayarları
     - `public function sanitize_api_keys(array $input): array`
     - `public function get_setting(string $key, mixed $default = null): mixed`

9. **IAT_Price_Importer** (`includes/import/class-iat-price-importer.php`)
   - Singleton pattern
   - Metodlar:
     - `public function import_from_file(string $filepath): int`
     - `public function import_from_string(string $content): int`
     - `private function parse_price_list(string $content): array`
     - `private function create_regions_from_price_list(array $parsed): int`
     - `private function create_pricings_from_price_list(array $parsed): int`
     - `private function extract_regions_from_table(array $table_rows): array`
     - `private function extract_pricings_from_table(array $table_rows): array`

10. **IAT_Helper** (`includes/utils/class-iat-helper.php`)
    - Static methods (singleton yok, utility class)
    - Tüm yardımcı fonksiyonlar

11. **IAT_Security** (`includes/security/class-iat-security.php`)
    - Singleton pattern
    - Metodlar:
      - `public function __construct()`: Register hooks
      - `public function verify_ajax_nonce(): bool`
      - `public function verify_recaptcha(string $token): bool`
      - `public function check_rate_limit(string $ip): bool`
      - `public function sanitize_input(string $input): string`
      - `public function prepare_sql(string $sql, array $params): string`

## [Dependencies]

**PHP Gereksinimler:**
- PHP 8.0 veya üzeri
- WordPress 6.0 veya üzeri
- MySQL 5.7 veya üzeri
- PHP Extensions: json, mbstring, openssl (for random_bytes), pdo_mysql

**PHP Bağımlılıkları (Composer):**
```json
{
    "require": {
        "php": ">=8.0",
        "composer/installers": "^2.0"
    },
    "require-dev": {
        "squizlabs/php_codesniffer": "^3.7",
        "wp-coding-standards/wpcs": "^3.0",
        "phpunit/phpunit": "^9.5"
    }
}
```

**NPM Bağımlılıkları:**
```json
{
    "devDependencies": {
        "@wordpress/scripts": "^26.0.0",
        "webpack-cli": "^5.1.0",
        "css-loader": "^6.8.0",
        "mini-css-extract-plugin": "^2.7.0"
    },
    "dependencies": {
        "leaflet": "^1.9.0"  // Map preview için
    }
}
```

**WordPress Functions Kullanılacak:**
- add_menu_page()
- add_submenu_page()
- add_settings_section()
- add_settings_field()
- register_setting()
- wp_create_nonce()
- wp_verify_nonce()
- add_action()
- add_filter()
- dbDelta() (tablo oluşturma için)
- $wpdb (veritabanı işlemleri)

**External Libraries:**
- Leaflet.js (1.9.0+) - Map preview için (CDN veya NPM)
- reCAPTCHA v3 - Google API

**Kullanılmayacaklar (Gelecek modüllerde):**
- GeoPHP (MODULE_2'de)
- Yandex/Google API client'ları (MODULE_3'te)
- React/Vue (Frontend MODULE_6'da Vanilla JS kullanılacak)

## [Testing]

**Test Dosyaları:**

1. `tests/unit/class-iat-helper-test.php`
   - Booking ID oluşturma testi
   - Token oluşturma testi
   - Telefon numarası validasyonu testi
   - Email validasyonu testi
   - Tarih formatlama testi

2. `tests/unit/class-iat-security-test.php`
   - Nonce oluşturma/validasyon testi
   - Rate limiting testi
   - reCAPTCHA validasyon mock testi
   - Input sanitization testi

3. `tests/unit/class-iat-db-manager-test.php`
   - Tablo oluşturma testi
   - Bölge CRUD işlemleri testi
   - Fiyat CRUD işlemleri testi
   - Rezervasyon CRUD işlemleri testi
   - API usage insert/update testi
   - Geocache insert/get testi
   - Eski cache temizleme testi

4. `tests/integration/admin-pages-test.php`
   - Admin sayfaları render testi
   - Form submit handler testi
   - Bölge ekleme/düzenleme testi
   - Fiyat ekleme/düzenleme testi
   - Silme işlemleri testi

5. `tests/integration/price-importer-test.php`
   - fiyat_listesi.md parse testi
   - Bölge oluşturma testi
   - Fiyat import testi
   - Update/overwrite testi
   - Duplicate handling testi

6. `tests/integration/settings-test.php`
   - Settings kayıt testi
   - API keys kayıt testi
   - API rotation sıralama testi
   - Ayarları getirme testi

**Test Senaryoları:**

1. **Veritabanı:**
   - 5 özel tablonun doğru oluşturulması
   - Tablo yapılarının doğru olması (FOREIGN KEY, UNIQUE constraint)
   - Bölge CRUD işlemlerinin çalışması
   - Fiyat CRUD işlemlerinin çalışması
   - Rezervasyon CRUD işlemlerinin çalışması
   - CASCADE delete'lerin çalışması
   - Index'lerin doğru oluşturulması

2. **Bölge Yönetimi:**
   - 13 bölgenin doğru import edilmesi
   - Bölge ekleme (European/Anatolian/Airport types)
   - Bölge düzenleme
   - Bölge silme (CASCADE: fiyatları da siler)
   - GeoJSON validasyonu
   - Bölge içi fiyat girişi

3. **Fiyat Yönetimi:**
   - 60+ fiyatın doğru import edilmesi (fiyat_listesi.md'den)
   - Fiyat ekleme
   - Fiyat düzenleme
   - Fiyat silme
   - Çift yönlü fiyatların doğru çalışması
   - Aynı route için duplicate kontrolü (UNIQUE constraint)
   - Bulk import işlemi

4. **Admin Sayfaları:**
   - Bölgeler sayfasının render edilmesi
   - Fiyatlandırmalar sayfasının render edilmesi
   - Rezervasyonlar sayfasının render edilmesi
   - Settings sayfasının render edilmesi
   - Form submit'lerin doğru çalışması
   - Ajax işlemleri

5. **Güvenlik:**
   - Nonce oluşturma/validasyon
   - Rate limiting çalışması
   - Input sanitization
   - SQL injection koruması (prepared statements)
   - CSRF koruması

6. **Fiyat Importer:**
   - fiyat_listesi.md parse
   - 13 bölgenin otomatik oluşturulması
   - Tüm fiyatların import edilmesi (IST → Avrupa, IST → Anadolu, SAW → Avrupa, SAW → Anadolu, Havaalanlar arası, Bölgeler arası, Bölge içi)
   - Update mekanizması (varolan kayıtları günceller)
   - Error handling

7. **Helper Fonksiyonlar:**
   - Booking ID benzersizliği
   - Token benzersizliği ve güvenliği
   - Telefon validasyonu (E.164)
   - Email validasyonu
   - Timezone doğru çalışması

**Test Framework:**
- PHPUnit 9.x
- WordPress Test Suite
- Mockery (for external API mocking)

**Test Koşma:**
```bash
composer install
vendor/bin/phpunit
```

**Manual Testing:**
- WordPress admin paneline giriş
- "İstanbul Airport Transfer" menüsünün görünürlüğü
- Bölgeler sayfasının çalışması (13 bölge görünürlüğü)
- Fiyatlandırmalar sayfasının çalışması (60+ fiyat görünürlüğü)
- Rezervasyonlar sayfasının çalışması
- Settings page'in çalışması
- Bölge ekleme/düzenleme/silme
- Fiyat ekleme/düzenleme/silme
- Bulk import (fiyat_listesi.md'den)
- GeoJSON upload ve map preview
- Rezervasyon status değiştirme

## [Implementation Order]

1. **Ana Eklenti Dosyası ve Main Class Oluşturma**
   - istanbul-airport-transfer.php oluştur
   - IAT_Main sınıfını oluştur
   - Singleton pattern uygula
   - Activation/deactivation hook'larını ekle

2. **Activator ve Deactivator Sınıfları**
   - IAT_Activator oluştur
   - IAT_Deactivator oluştur
   - dbDelta() kullanarak 7 tabloyu oluştur
   - Rewrite rules flush

3. **Veritabanı Yöneticisi (DB Manager)**
   - IAT_DB_Manager oluştur
   - Tablo oluşturma metodları (7 tablo)
   - Bölge CRUD metodları
   - Fiyat CRUD metodları
   - Opsiyon CRUD metodları
   - Rezervasyon CRUD metodları
   - Booking options metodları
   - API usage metodları
   - Geocache metodları

4. **Helper Utilities Sınıfı**
   - IAT_Helper oluştur
   - Booking ID generator
   - Token generator
   - Telefon/email validasyonu
   - Tarih formatlama fonksiyonları

5. **Security Sınıfı**
   - IAT_Security oluştur
   - Nonce oluşturma/validasyon
   - Rate limiting implementasyonu
   - Input sanitization metodları
   - Prepared statement wrapper

6. **Price Importer Sınıfı**
   - IAT_Price_Importer oluştur
   - fiyat_listesi.md parser
   - Bölge oluşturma metodları (13 bölge)
   - Fiyat import metodları (80+ fiyat)
   - Varsayılan opsiyon oluşturma (TV Vehicle: +10€)
   - Update/overwrite mekanizması

7. **Admin - Bölgeler Sayfası**
   - IAT_Admin_Regions oluştur
   - Admin menu ekle
   - Bölgeler listesi tablosu
   - Bölge ekleme formu
   - Bölge düzenleme formu
   - GeoJSON upload/girişi
   - Form submit handler
   - Silme handler
   - admin-regions.js (map preview ile)

8. **Admin - Fiyatlandırmalar Sayfası**
   - IAT_Admin_Pricings oluştur
   - Admin menu ekle
   - Fiyat listesi tablosu
   - Fiyat ekleme formu
   - Fiyat düzenleme formu
   - Form submit handler
   - Silme handler
   - Bulk import handler (fiyat_listesi.md'den)
   - admin-pricings.js

9. **Admin - Rezervasyonlar Sayfası**
   - IAT_Admin_Bookings oluştur
   - Admin menu ekle
   - Rezervasyon listesi tablosu
   - Detay görüntüleme
   - Status değiştirme handler
   - Silme handler
   - Filtreleme
   - admin-bookings.js

10. **Admin - Settings Page**
    - IAT_Settings oluştur
    - Admin menu ekle
    - Settings API register
    - API keys section (10 key x 2 provider)
    - API rotation sıralaması (drag-drop)
    - İletişim bilgileri section
    - reCAPTCHA section
    - Sanitization callbacks
    - admin-settings.js

11. **Admin - Opsiyonlar Sayfası**
    - IAT_Admin_Options oluştur
    - Admin menu ekle
    - Opsiyon listesi tablosu
    - Opsiyon ekleme/düzenleme formu
    - Sıralama ve silme handler
    - admin-options.js

12. **Email Manager**
    - IAT_Email_Manager oluştur
    - 5 email şablonu (İngilizce)
    - Admin bildirim emaili (confirm/cancel linkleri)
    - Müşteri emailleri
    - WP Mail SMTP entegrasyonu

13. **Cron Manager**
    - IAT_Cron_Manager oluştur
    - Saatlik: auto_confirm (24h geçen pending)
    - Günlük: cleanup (6 aydan eski booking silme)
    - Haftalık: geocache temizleme (30 gün)

14. **Geocoding Katmanı**
    - IAT_API_Rotator oluştur
    - IAT_Nominatim oluştur (ücretsiz birincil geocoding)
    - IAT_Yandex_Geocoder oluştur (autocomplete + yedek geocoding)
    - IAT_Google_Geocoder oluştur (autocomplete + yedek geocoding)
    - Rotation ve failover mantığı

15. **Zone Detector**
    - IAT_Zone_Detector oluştur
    - Point-in-polygon (Ray Casting)
    - Overlap resolution (en küçük alan kuralı)
    - Cache entegrasyonu

16. **Frontend - Booking Form**
    - IAT_Booking_Form oluştur
    - IAT_Pricing_Engine oluştur
    - Multi-step form HTML render
    - Vanilla JS: autocomplete, return trip toggle, form validasyon
    - AJAX endpoint'leri (geocode, quote, book)
    - Return trip: 2 linked booking oluşturma
    - public-booking.css + public-booking-form.js

17. **Shortcodes**
    - IAT_Shortcodes oluştur
    - `[iat_booking_form]` shortcode register
    - Asset enqueue (sadece shortcode sayfasında)

18. **Frontend Assets (CSS/JS)**
    - admin-styles.css oluştur (tüm admin sayfaları için)
    - Leaflet.js entegrasyonu (CDN veya NPM)
    - Map preview component
    - Asset enqueue hooks ekle
    - Drag-drop implementasyonu

19. **Varsayılan Veriler**
    - Aktivasyon sırasında varsayılan ayarları ekle
    - 13 bölgeyi otomatik oluştur (IAT_Price_Importer ile)
    - 80+ fiyatı import et (IAT_Price_Importer ile)
    - Varsayılan opsiyonları oluştur (TV Vehicle: +10€)
    - Test rezervasyonları (opsiyonel)

20. **Aktivasyon İle Entegrasyon**
    - IAT_Activator'a price import entegrasyonu
    - Aktivasyon sırasında otomatik import
    - Error handling

21. **Uninstall**
    - uninstall.php oluştur
    - Tüm custom tabloları DROP
    - wp_options'tan iat_* kayıtları sil
    - Cron job'ları kaldır

22. **Test Yazma**
    - Unit testleri oluştur
    - Integration testleri oluştur
    - Admin page testleri
    - Price importer testleri
    - Return trip testleri
    - Testleri çalıştır ve düzelt

23. **Dokümantasyon ve Readme**
    - readme.txt oluştur
    - Kurulum talimatları ekle
    - Admin panel kullanım kılavuzu ekle
    - Changelog ekle

24. **Final Test ve Doğrulama**
    - Manual testing (tüm admin sayfaları + frontend form)
    - WordPress coding standards check
    - PHPStan (static analysis)
    - Eklenti aktivasyon testi
    - Fiyat import testi
    - Return trip çift booking testi
    - Bölge/fiyat/opsiyon CRUD testleri