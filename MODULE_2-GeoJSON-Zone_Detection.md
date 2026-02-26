## TASK: Implement Point-in-Polygon Zone Detection

REQUIREMENTS:
1. Store GeoJSON polygons for 11 Istanbul zones + 2 airports (13 total)
2. Detect which zone a lat/lng coordinate falls into
3. Cache results to reduce calculations
4. Handle undefined zones gracefully
5. Resolve overlapping zones (smallest area wins)

INPUT FORMAT (GeoJSON):
{
  "type": "Feature",
  "properties": {"zone_code": "Bolge-Bir", "zone_name": "Zone 1 - Taksim"},
  "geometry": {
    "type": "Polygon", 
    "coordinates": [[[28.9, 41.0], [28.95, 41.0], [28.95, 41.05], [28.9, 41.05], [28.9, 41.0]]]
  }
}

ALGORITHM:
- Use Ray Casting algorithm OR GeoPHP library
- Check cache first (wp_iat_geocache table)
- Return: zone_code or null

EDGE CASES:
- Coordinate on polygon edge
- Multiple overlapping zones (use smallest area)
- Invalid GeoJSON

CODE STRUCTURE:
class IAT_Zone_Detector {
    public function detect_zone(float $lat, float $lng): ?array
    public function is_in_polygon(array $point, array $polygon): bool
    private function get_cache(string $hash): ?array
    private function set_cache(string $hash, array $data): void
}