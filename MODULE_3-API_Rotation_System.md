## TASK: Build Geocoding API Rotation Manager

PROVIDERS:
- Autocomplete (address suggestions): Yandex Maps (10 keys) + Google Maps (10 keys) = 20 total
- Geocoding (address → lat/lng): Nominatim (free, primary) + Yandex/Google (fallback)

LIMITS:
- Yandex: 25,000 calls/day per key
- Google: 3,000 calls/month per key (approx 100/day)
- Nominatim: 1 request/second (free, no key required)

REQUIREMENTS:
1. Manual ordering via admin UI (drag-drop sort) for autocomplete providers
2. Automatic failover when limit reached
3. Track usage in real-time (wp_iat_api_usage table)
4. Support both providers with unified interface
5. Nominatim as primary geocoder (free), Yandex/Google as fallback
6. Cache all geocoding results in wp_iat_geocache

DATABASE SCHEMA:
wp_iat_api_usage (
  id bigint AUTO_INCREMENT,
  api_provider varchar(20), -- 'yandex', 'google', or 'nominatim'
  api_key_index tinyint, -- 1-10 (0 for nominatim)
  call_date date,
  call_count int DEFAULT 0,
  monthly_count int DEFAULT 0,
  UNIQUE KEY (provider, key_index, call_date)
)

CLASS STRUCTURE:
interface IAT_Geocoder_Interface {
    public function geocode(string $address): array|WP_Error;
    public function check_limit(): bool;
}

class IAT_Nominatim implements IAT_Geocoder_Interface  // Free, primary
class IAT_Yandex_Geocoder implements IAT_Geocoder_Interface
class IAT_Google_Geocoder implements IAT_Geocoder_Interface

class IAT_API_Rotator {
    private array $apis = [];
    
    public function add_api(IAT_Geocoder_Interface $api, int $priority): void
    public function geocode(string $address): array|WP_Error
    public function autocomplete(string $query): array|WP_Error
    private function get_next_available(): ?IAT_Geocoder_Interface
    private function log_usage(string $provider, int $index): void
}

GEOCODING FLOW:
1. Check wp_iat_geocache first
2. Try Nominatim (free)
3. If Nominatim fails, try Yandex/Google rotation
4. Cache successful result
5. Log usage

AUTOCOMPLETE FLOW:
1. Client requests address suggestions
2. Rotator checks API 1 (highest priority, admin-configured)
3. If limit reached, try API 2, etc.
4. If all exhausted, return WP_Error
5. Log successful call immediately