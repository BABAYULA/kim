# AI Prompt Templates for Vibe Coding

Bu dosya, projenin AI asistanları ile tamamen geliştirilmesi için gerekli tüm prompt şablonlarını içerir.

---

## 📋 PROMPT TEMPLATE 1: PHP Sınıfı Oluşturma

**Kullanım Alanı:** Temel PHP sınıfları için

```
Şu gereksinimlere uygun bir WordPress PHP sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\[Subnamespace]
SINIF ADI: [ClassName]
SINIF AÇIKLAMASI: [Açıklama]

TEKNİK GEREKSİNİMLER:
- PHP 8.0+
- WordPress 6.0+
- PSR-12 coding standards
- PSR-4 autoloading

GEREKLİ METODLAR:
- [public/protected/private] function methodName(): ReturnType { }

VERİTABANI:
- Tablo adı: wp_iat_[table_name]
- Alanlar: [field1, field2, ...]

SECURITY:
- Nonce verification for AJAX
- Prepared statements for DB queries
- Capability checks: [capabilities]
- Input sanitization and output escaping

HOOKS:
- Actions: [list actions]
- Filters: [list filters]

DOKÜMANTASYON:
- PHPDoc blokları tüm metodlar için
- @param, @return, @throws etiketleri kullan

EK BİLGİ:
[Gelen modül dokümantasyonundan ek kontekst]
```

---

## 📋 PROMPT TEMPLATE 2: Repository Sınıfı Oluşturma

**Kullanım Alanı:** Veritabanı işlemleri için

```
Şu gereksinimlere uygun bir Repository sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\[Subnamespace]\Repositories
SINIF ADI: [Entity]Repository

ENTITY BİLGİLERİ:
- Tablo adı: wp_iat_[entity]
- Primary Key: id
- Alanlar: [field1, field2, ...]

GEREKLİ METODLAR:
- findAll(): array - Tüm kayıtları getir
- findById(int $id): ?[Entity] - ID ile getir
- create(array $data): int - Yeni kayıt oluştur
- update(int $id, array $data): bool - Kayıt güncelle
- delete(int $id): bool - Kayıt sil
- findWhere(array $conditions): array - Koşula göre getir

SECURITY:
- $wpdb->prepare() kullan
- Input sanitization
- SQL injection koruması

DOKÜMANTASYON:
- PHPDoc blokları
- @return type etiketleri
```

---

## 📋 PROMPT TEMPLATE 3: Admin Sayfası Oluşturma

**Kullanım Alanı:** Yönetici arayüzleri için

```
Şu gereksinimlere uygun bir Admin Page sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Admin
SINIF ADI: Admin_[Entity]

PAGE BİLGİLERİ:
- Page Title: [Title]
- Menu Title: [Title]
- Capability: manage_options
- Menu Slug: iat-[entity]
- Parent Slug: [parent veya null]

GEREKLİ METODLAR:
- register_menu(): void - Menüyü kaydet
- render_page(): void - Sayfa içeriğini render et
- handle_form_submission(): void - Form işleme
- render_list_table(): void - Liste tablosu
- render_form($item = null): void - Form render et

SECURITY:
- Nonce verification (wp_verify_nonce)
- Capability checks (current_user_can)
- Output escaping (esc_html, esc_attr)
- Sanitization (sanitize_text_field)

HOOKS:
- admin_menu action
- admin_init action

ASSETS:
- CSS dosyası: assets/css/admin-[entity].css
- JS dosyası: assets/js/admin-[entity].js

DOKÜMANTASYON:
- PHPDoc blokları
- Kullanım örnekleri
```

---

## 📋 PROMPT TEMPLATE 4: AJAX Handler Oluşturma

**Kullanım Alanı:** AJAX istekleri için

```
Şu gereksinimlere uygun bir AJAX Handler sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Ajax
SINIF ADI: [Action]Handler

ACTION BİLGİLERİ:
- Action Name: iat_[action]
- Method: POST (veya GET)
- Permission: [public, logged_in, admin]

GEREKLİ METODLAR:
- __construct(): void - Constructor
- register_handlers(): void - AJAX kaydı
- handle_request(): void - İsteği işle
- validate_request(): bool - İsteği doğrula
- send_response(mixed $data): void - Yanıt gönder

SECURITY:
- Nonce verification (check_ajax_referer)
- Capability checks (current_user_can)
- Input validation
- Rate limiting (IP bazlı)
- Output escaping

PARAMETRELER:
- [param1]: [type] - description
- [param2]: [type] - description

RESPONSE FORMAT:
{
  "success": true/false,
  "data": {},
  "errors": [],
  "message": ""
}

HOOKS:
- wp_ajax_iat_[action]
- wp_ajax_nopriv_iat_[action]

DOKÜMANTASYON:
- PHPDoc blokları
- Hata kodları
```

---

## 📋 PROMPT TEMPLATE 5: Model/Entity Sınıfı Oluşturma

**Kullanım Alanı:** Varlık sınıfları için

```
Şu gereksinimlere uygun bir Model/Entity sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\[Subnamespace]\Models
SINIF ADI: [Entity]

ENTITY BİLGİLERİ:
- Tablo adı: wp_iat_[entity]
- Primary Key: id

ALANLAR:
- $id: int
- $[field1]: [type]
- $[field2]: [type]
- ...

GEREKLİ METODLAR:
- __construct(array $data = []): void
- toArray(): array - Diziye dönüştür
- __get(string $name): mixed
- __set(string $name, mixed $value): void
- validate(): array - Validasyon

VALIDASYON KURALLARI:
- [field1]: [rules]
- [field2]: [rules]

DOKÜMANTASYON:
- PHPDoc blokları
- Property types
- Method descriptions
```

---

## 📋 PROMPT TEMPLATE 6: Frontend JavaScript Modülü Oluşturma

**Kullanım Alanı:** Frontend JavaScript kodları için

```
Şu gereksinimlere uygun bir JavaScript modülü oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
MODÜL ADI: [ModuleName]

GEREKSİNİMLER:
- ES6+ (no jQuery unless needed)
- WordPress REST API kullan
- Vanilla JS preferred

METODLAR:
- init(): void - Initialize
- [methodName](): ReturnType - Description
- handleEvent(event): void - Event handler

SECURITY:
- Nonce verification
- Input sanitization
- XSS prevention

GLOBAL DEĞİŞKENLER:
- iatSettings: { nonce, ajaxUrl, ... }

AJAX ÇAĞRILARI:
fetch(iatSettings.ajaxUrl, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'iat_[action]',
    nonce: iatSettings.nonce,
    [other_params]
  })
})

DOKÜMANTASYON:
- JSDoc comments
- Usage examples
- Event descriptions
```

---

## 📋 PROMPT TEMPLATE 7: Test Case Oluşturma

**Kullanım Alanı:** PHPUnit testleri için

```
Şu gereksinimlere uygun bir PHPUnit test sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\[Subnamespace]\Tests
SINIF ADI: [ClassName]Test

TEST EDİLEN SINIF: IAT\[Subnamespace]\[ClassName]

GEREKLİ TESTLER:
- setUp(): void - Test setup
- tearDown(): void - Test cleanup
- test[MethodName](): void - Test specific method

TEST SENARYOLARI:
1. [Test senaryosu açıklaması]
   - Input: [input]
   - Expected: [expected output]
   - Assert: [assertion]

2. [Test senaryosu açıklaması]
   - ...

MOCK OBJECTS:
- [mock nesneleri]

DATA PROVIDERS:
- [test data providers]

DOKÜMANTASYON:
- Test descriptions
- Comments for complex logic
```

---

## 📋 PROMPT TEMPLATE 8: Email Template Oluşturma

**Kullanım Alanı:** Email şablonları için

```
Şu gereksinimlere uygun bir email şablonu oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
TEMPLATE ADI: [TemplateName]

EMAIL BİLGİLERİ:
- Subject: [Email subject]
- Recipient: [recipient placeholder]
- Type: [html, text, both]

VARIABLES:
- {[variable1]}: Description
- {[variable2]}: Description
- ...

CONTENT:
[Email içeriği]

STYLE:
- Inline CSS kullan
- Responsive tasarım
- WordPress email styles uygula

DOKÜMANTASYON:
- Değişken açıklamaları
- Kullanım örnekleri
```

---

## 📋 PROMPT TEMPLATE 9: Migration Script Oluşturma

**Kullanım Alanı:** Veritabanı migration'ları için

```
Şu gereksinimlere uygun bir migration script'i oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
MIGRATION ADI: [MigrationName]
VERSİYON: [x.y.z]

GÖREV:
[Migration açıklaması]

TABLO DEĞİŞİKLİKLERİ:
- [Tablo]: [ADD/DROP/MODIFY] [field] [type]

VERİ MİGRASYONU:
[Veri migrasyonu açıklaması]

SECURITY:
- $wpdb->prepare kullan
- Backup tavsiyesi
- Transaction kullan (varsa)

GERİ ALMA (ROLLBACK):
[Rollback adımları]

DOKÜMANTASYON:
- Migration açıklaması
- Etkilenen tablolar
- Test adımları
```

---

## 📋 PROMPT TEMPLATE 10: Shortcode Oluşturma

**Kullanım Alanı:** WordPress shortcodes için

```
Şu gereksinimlere uygun bir shortcode handler oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
SHORTCODE ADI: [iat_shortcode_name]

SHORTCODE ATTRIBUTES:
- [attr1]: [type] - default: [value] - description
- [attr2]: [type] - default: [value] - description

GEREKLİ METODLAR:
- __construct(): void - Constructor
- register(): void - Shortcode kaydı
- render(array $atts, string $content = ''): string - Render

SECURITY:
- Sanitize attributes
- Escape output
- Capability checks (gerekirse)

ASSETS:
- CSS: assets/css/shortcode-[name].css
- JS: assets/js/shortcode-[name].js

KULLANIM:
[iat_shortcode_name attr1="value" attr2="value"]

DOKÜMANTASYON:
- PHPDoc blokları
- Usage examples
- Attribute descriptions
```

---

## 📋 PROMPT TEMPLATE 11: API Provider Sınıfı Oluşturma

**Kullanım Alanı:** Harici API sağlayıcıları için

```
Şu gereksinimlere uygun bir API Provider sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Geocoding\Providers
SINIF ADI: [ProviderName]Provider

API BİLGİLERİ:
- Provider Name: [Yandex/Google/Nominatim]
- API Endpoint: [URL]
- Rate Limit: [limit per day/hour]
- Authentication: [API key required]

GEREKLİ METODLAR:
- __construct(array $api_keys): void - Constructor
- geocode(string $address): array - Adresi koordinata çevir
- reverseGeocode(float $lat, float $lng): array - Koordinatı adrese çevir
- validateResponse(array $response): bool - Response doğrula
- parseResponse(array $response): array - Response parse et

CACHE:
- Cache TTL: [seconds]
- Cache key: [pattern]

ERROR HANDLING:
- Retry logic: [attempts]
- Fallback: [fallback provider]
- Logging: [log errors]

SECURITY:
- API key masking
- Rate limiting
- Input sanitization

RESPONSE FORMAT:
{
  "latitude": float,
  "longitude": float,
  "formatted_address": string,
  "components": {}
}

DOKÜMANTASYON:
- PHPDoc blokları
- API documentation references
- Error codes
```

---

## 📋 PROMPT TEMPLATE 12: Pricing Engine Sınıfı Oluştura

**Kullanım Alanı:** Fiyatlandırma motoru için

```
Şu gereksinimlere uygun bir Pricing Engine sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Pricing
SINIF ADI: PricingEngine

GEREKLİ METODLAR:
- calculatePrice(array $data): array - Fiyat hesapla
- getBasePrice(string $fromZone, string $toZone): float - Taban fiyat
- addExtras(float $basePrice, array $extras): float - Ekstraları ekle
- applyDiscount(float $price, array $discount): float - İndirim uygula
- validatePricing(array $data): bool - Fiyatlandırma doğrula

PARAMETRELER:
- $fromZone: string - Pickup zone code
- $toZone: string - Dropoff zone code
- $isReturnTrip: bool - Return trip flag
- $extras: array - Selected extras (TV, child seat, etc.)
- $passengers: int - Number of passengers
- $luggage: int - Number of luggage

PRICING LOGIC:
- [Business rules]

ERROR HANDLING:
- Invalid zone: throw exception
- Missing pricing: return default or error

RESPONSE FORMAT:
{
  "base_price": float,
  "extras_price": float,
  "total_price": float,
  "currency": "EUR",
  "breakdown": {}
}

DOKÜMANTASYON:
- PHPDoc blokları
- Pricing rules documentation
- Error codes
```

---

## 📋 PROMPT TEMPLATE 13: State Machine Sınıfı Oluşturma

**Kullanım Alanı:** Booking state machine için

```
Şu gereksinimlere uygun bir State Machine sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Booking
SINIF ADI: BookingStateMachine

STATE'LER:
- pending: Yeni rezervasyon
- confirmed: Admin onaylandı
- auto_confirmed: Otomatik onaylandı (24 saat sonra)
- cancelled: İptal edildi
- completed: Tamamlandı

STATE GEÇİŞLERİ:
- pending -> confirmed: Admin onayı
- pending -> auto_confirmed: 24 saat sonra
- pending -> cancelled: İptal
- confirmed -> cancelled: İptal
- confirmed -> completed: Tamamlandı

GEREKLİ METODLAR:
- __construct(string $currentState): void - Constructor
- transition(string $newState): bool - State geçişi
- canTransition(string $newState): bool - Geçiş kontrolü
- getNextStates(): array - Mümkün sonraki state'ler
- onTransition(string $fromState, string $toState): void - Geçiş callback

HOOKS:
- iat_booking_state_transition: [from, to, booking_id]

VALIDATION:
- [Geçiş kuralları]

LOGGING:
- State geçişlerini logla

DOKÜMANTASYON:
- PHPDoc blokları
- State diagram
- Geçiş kuralları
```

---

## 📋 PROMPT TEMPLATE 14: Security Utility Sınıfı Oluşturma

**Kullanım Alanı:** Security yardımcı fonksiyonları için

```
Şu gereksinimlere uygun bir Security Utility sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Security
SINIF ADI: SecurityHelper

GEREKLİ METODLAR:
- createNonce(string $action): string - Nonce oluştur
- verifyNonce(string $nonce, string $action): bool - Nonce doğrula
- sanitizeInput(mixed $input): mixed - Input sanitize
- escapeOutput(mixed $output): mixed - Output escape
- checkCapability(string $capability): bool - Capability kontrol
- validateEmail(string $email): bool - Email validasyonu
- validatePhone(string $phone): bool - Telefon validasyonu
- generateToken(): string - Token oluştur
- encryptData(string $data): string - Veri şifreleme
- decryptData(string $encrypted): string - Veri deşifre

RATE LIMITING:
- checkRateLimit(string $identifier, int $limit, int $window): bool
- incrementRateLimit(string $identifier): void

CAPTCHA:
- verifyRecaptcha(string $token): bool

LOGGING:
- logSecurityEvent(string $event, array $context): void

DOKÜMANTASYON:
- PHPDoc blokları
- Security best practices
- Usage examples
```

---

## 📋 PROMPT TEMPLATE 15: Email Manager Sınıfı Oluşturma

**Kullanım Alanı:** Email gönderimi için

```
Şu gereksinimlere uygun bir Email Manager sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Email
SINIF ADI: EmailManager

EMAIL TEMPLATES:
- booking_new_admin: Yeni rezervasyon (admin)
- booking_pending_customer: Rezervasyon pending (müşteri)
- booking_confirmed: Rezervasyon onaylandı
- booking_auto_confirmed: Otomatik onaylandı
- booking_cancelled: İptal edildi

GEREKLİ METODLAR:
- sendEmail(string $template, array $data, array $recipients): bool - Email gönder
- renderTemplate(string $template, array $data): string - Template render
- addAttachment(string $path): void - Dosya ekle
- setHeaders(array $headers): void - Header set et
- queueEmail(string $template, array $data, array $recipients): void - Email kuyruğa al

HOOKS:
- iat_before_send_email
- iat_after_send_email
- iat_email_failed

ERROR HANDLING:
- Send failure: log and retry
- Invalid template: throw exception

DOKÜMANTASYON:
- PHPDoc blokları
- Template documentation
- Email configuration
```

---

## 📋 PROMPT TEMPLATE 16: Cron Job Oluşturma

**Kullanım Alanı:** Scheduled tasks için

```
Şu gereksinimlere uygun bir Cron Job sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Cron
SINIF ADI: [JobName]Cron

CRON BİLGİLERİ:
- Schedule: [hourly, daily, twicedaily, weekly]
- Hook Name: iat_[job_name]

GEREKLİ METODLAR:
- __construct(): void - Constructor
- schedule(): void - Cron schedule
- unschedule(): void - Cron iptal
- execute(): void - Job çalıştır
- isRunning(): bool - Çalışıyor mu kontrolü

TASK DESCRIPTION:
[Job açıklaması]

LOCKING:
- Lock mechanism: [prevent concurrent runs]

LOGGING:
- Execution log
- Error log

ERROR HANDLING:
- Retry logic: [attempts]
- Error notifications: [email/slack]

DOKÜMANTASYON:
- PHPDoc blokları
- Job description
- Scheduling information
```

---

## 📋 PROMPT TEMPLATE 17: Validation Sınıfı Oluşturma

**Kullanım Alanı:** Veri validasyonu için

```
Şu gereksinimlere uygun bir Validation sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Validation
SINIF ADI: Validator

VALIDASYON KURALLARI:
- required: Alan zorunlu
- email: Email formatı
- phone: Telefon formatı
- numeric: Sayısal değer
- min: Minimum değer
- max: Maksimum değer
- length: Uzunluk kontrolü
- regex: Regular expression
- date: Tarih formatı
- datetime: Tarih-saat formatı

GEREKLİ METODLAR:
- validate(array $data, array $rules): array - Validasyon yap
- addRule(string $field, string $rule, array $params = []): void - Kural ekle
- getErrors(): array - Hataları getir
- hasErrors(): bool - Hata var mı
- clearErrors(): void - Hataları temizle

RESPONSE FORMAT:
{
  "valid": bool,
  "errors": {
    "field1": ["error message"],
    "field2": ["error message"]
  }
}

SANITIZATION:
- sanitizeInput(array $data, array $rules): array - Input sanitize

DOKÜMANTASYON:
- PHPDoc blokları
- Validation rules documentation
- Error messages
```

---

## 📋 PROMPT TEMPLATE 18: Logger Sınıfı Oluşturma

**Kullanım Alanı:** Loglama için

```
Şu gereksinimlere uygun bir Logger sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Logging
SINIF ADI: Logger

LOG LEVELS:
- DEBUG: Detaylı debug bilgileri
- INFO: Bilgilendirme mesajları
- WARNING: Uyarı mesajları
- ERROR: Hata mesajları
- CRITICAL: Kritik hatalar

GEREKLİ METODLAR:
- log(string $level, string $message, array $context = []): void - Log kaydet
- debug(string $message, array $context = []): void - Debug log
- info(string $message, array $context = []): void - Info log
- warning(string $message, array $context = []): void - Warning log
- error(string $message, array $context = []): void - Error log
- critical(string $message, array $context = []): void - Critical log

LOG FORMAT:
[timestamp] [level] [message] [context]

LOG STORAGE:
- WordPress error_log
- Özel log dosyası (logs/)
- Database log table

LOG ROTATION:
- Log dosya boyutu: [MB]
- Log saklama süresi: [days]

DOKÜMANTASYON:
- PHPDoc blokları
- Log levels documentation
- Usage examples
```

---

## 📋 PROMPT TEMPLATE 19: Helper/Utility Sınıfı Oluşturma

**Kullanım Alanı:** Yardımcı fonksiyonlar için

```
Şu gereksinimlere uygun bir Helper/Utility sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Helpers
SINIF ADI: [HelperName]Helper

GEREKLİ METODLAR:
- [methodName]([params]): [ReturnType] - Description

[Metod listesi ve açıklamaları]

DOKÜMANTASYON:
- PHPDoc blokları
- Usage examples
- Parameter descriptions
```

---

## 📋 PROMPT TEMPLATE 20: Integration Test Oluşturma

**Kullanım Alanı:** Entegrasyon testleri için

```
Şu gereksinimlere uygun bir Integration Test sınıfı oluştur:

PROJE: Istanbul Airport Transfer WordPress Plugin
NAMESPACE: IAT\Tests\Integration
SINIF ADI: [Feature]IntegrationTest

TEST EDİLEN ÖZELLİK:
[Feature açıklaması]

TEST AKIŞI:
1. [Adım 1]
2. [Adım 2]
3. [Adım 3]

GEREKLİ TESTLER:
- setUp(): void - Test setup
- tearDown(): void - Test cleanup
- test[Scenario](): void - Test scenario

FIXTURES:
- [Test verileri]

ASSERTIONS:
- [Beklenen sonuçlar]

DOKÜMANTASYON:
- Test descriptions
- Scenario documentation
```

---

## 💡 PROMPT İPUÇLARI

### 1. Kontekst Sağlama
Her prompt şu konteksti içermeli:
- Proje adı ve amacı
- İlgili modül dokümantasyonu
- Teknoloji stack
- Kod standartları

### 2. Net Görev Tanımı
- Sınıf adı ve namespace
- Gerekli metodlar
- Veritabanı tabloları
- Security gereksinimleri

### 3. Çıktı Formatı
- Tam PHP sınıfı
- PHPDoc blokları
- Hata işleme
- Loglama

### 4. Prompt Uzunluğu
- Yeterli kontekst ama çok uzun değil
- Modül dokümantasyonuna referans ver
- Örnek kod ekle

### 5. Iterative Geliştirme
- Küçük adımlar
- Sık iterasyonlar
- Feedback loop

---

## 🔄 KULLANIM ÖRNEĞİ

```
Şu prompt şablonunu kullanarak bir Repository sınıfı oluştur:

TEMPLATE: PROMPT TEMPLATE 2

PROJE DEĞİŞİKLİKLERİ:
- Entity: Booking
- Tablo: wp_iat_bookings
- Primary Key: id
- Alanlar: id, pickup_address, dropoff_address, pickup_zone, dropoff_zone, 
           pickup_datetime, return_datetime, passenger_count, luggage_count,
           flight_code, customer_name, customer_email, customer_phone,
           total_price, currency, status, cancellation_token, linked_booking_id,
           is_return_trip, created_at, updated_at

EK BİLGİLER:
- Status değerleri: pending, confirmed, auto_confirmed, cancelled, completed
- Currency: EUR
- DateTime format: Y-m-d H:i:s
```

---

## 📚 MODÜL BAĞLANTILARI

Her prompt hangi modülden kontekst alacağını belirtmelidir:

- **Core Architecture**: MODULE_1-Core_Architecture.md
- **GeoJSON Zone Detection**: MODULE_2-GeoJSON-Zone_Detection.md
- **API Rotation System**: MODULE_3-API_Rotation_System.md
- **Pricing Engine**: MODULE-4-Pricing_Engine.md
- **Booking Flow**: MODULE-5-Booking_Flow-State_Machine.md
- **Frontend**: MODULE-6-Frontend_Implementation.md
- **Admin Interface**: MODULE-7-Admin_Interface.md
- **Testing**: MODULE_8-Testing_Scenarios.md
- **Security**: MODULE_11-Security.md
- **Error Handling**: MODULE_12-Error-Handling.md

---

## ✅ QUALITY CHECKLIST

Her prompt sonrası şu kontroller yapılmalı:

- [ ] PSR-12 uyumluluğu
- [ ] WordPress coding standards
- [ ] PHPDoc blokları tam
- [ ] Security kontrolleri var
- [ ] Hata işleme var
- [ ] Loglama var
- [ ] Testler yazıldı
- [ ] Memory Bank güncellendi