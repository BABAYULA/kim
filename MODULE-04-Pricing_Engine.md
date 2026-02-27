## TASK: Build Zone-to-Zone Pricing Calculator

PRICING MATRIX (Sample):
| From Zone | To Zone | Price (EUR) |
|-----------|---------|-------------|
| IST | Bolge-Yarim | 25 |
| IST | Bolge-Bir | 40 |
| SAW | Anadolu-Yarim | 25 |
| Bolge-Yarim | Bolge-Bir | 40 |
| ... (total 50+ combinations) |

REQUIREMENTS:
1. Bidirectional pricing (A→B = B→A)
2. Same-zone pricing (intrazonal)
3. Airport special zones (IST/SAW)
4. Return undefined for non-existent routes

LOGIC FLOW:
1. Geocode pickup address → detect zone A
2. Geocode dropoff address → detect zone B
3. If either zone undefined → return error with contact info
4. Lookup price in iat_pricings table
5. If not found, check reverse (B→A)
6. Add extras: +10€ if "TV Vehicle" selected
7. Return final price + metadata

CLASS STRUCTURE:
class IAT_Pricing_Engine {
    public function calculate(string $from_addr, string $to_addr): array {
        // Returns: [
        //   'success' =&gt; true,
        //   'price' =&gt; 45.00,
        //   'from_zone' =&gt; ['code' =&gt; 'Bolge-Bir', 'name' =&gt; 'Zone 1'],
        //   'to_zone' =&gt; ['code' =&gt; 'Bolge-Iki', 'name' =&gt; 'Zone 2'],
        //   'currency' =&gt; 'EUR'
        // ]
        // OR
        //   'success' =&gt; false,
        //   'error' =&gt; 'undefined_zone',
        //   'contact' =&gt; ['email' =&gt; '...', 'whatsapp' =&gt; '...']
    }
    
    private function get_zone_from_address(string $address): ?array
    private function lookup_price(string $from_code, string $to_code): ?float
}