# Vibe Coding Guide for Istanbul Airport Transfer Plugin

Bu doküman, projenin AI asistanları ile tamamen geliştirilmesi (Vibe Coding) için gerekli yapı ve yönergeleri içerir.

## 🎯 Vibe Coding Uygunluk Analizi

### ✅ Güçlü Yanlar

1. **Mükemmel Dokümantasyon**
   - Memory Bank tamamen kurulmuş ve güncel
   - 12 modül dosyası her alanı detaylı bir şekilde tanımlıyor
   - README.md kapsamlı ve anlaşılır

2. **Modüler Mimari**
   - Her modül bağımsız olarak geliştirilebilir
   - Tek sorumluluk prensibi uygulanmış
   - Singleton, Factory, Repository desenleri tanımlı

3. **Açık Teknoloji Stack**
   - PHP 8.0+, WordPress 6.0+
   - Vanilla ES6+ JavaScript
   - Composer, NPM, Webpack build pipeline

4. **Tanımlı Kod Standartları**
   - PSR-12 PHP coding standards
   - WordPress coding standards
   - PSR-4 autoloading

### ⚠️ Geliştirme Alanları

1. **AI Prompt Şablonları** - Eksik
2. **Adım Adım Geliştirme Rehberi** - Eksik
3. **Kod Üretim Şablonları** - Eksik
4. **Test Promptları** - Eksik

---

## 📋 Vibe Coding İçin Gerekli Yapı

### Phase 1: AI Prompt Şablonları Oluşturuldu

Her modül ve özellik için hazır prompt şablonları eklendi.

### Phase 2: Geliştirme Rehberi Hazırlanıyor

Adım adım geliştirme süreci için rehber.

### Phase 3: Kod Üretim Şablonları

Hazır kod şablonları ve boilerplate dosyaları.

---

## 🚀 Vibe Coding Akışı

### 1. Modül Seçimi
```yaml
Sıralama:
  1. Core Architecture (MODULE_1)
  2. Database Manager
  3. Security Layer
  4. Geocoding Layer
  5. Zone Detection
  6. Pricing Engine
  7. Booking Flow
  8. Admin Interface
  9. Frontend
  10. Testing
```

### 2. Kod Üretim Süreci

Her modül için:
1. Modül dokümantasyonunu oku
2. Prompt şablonunu uygula
3. Kodu üret ve kaydet
4. Testleri yaz
5. Kod review yap
6. Memory Bank'i güncelle

### 3. Kalite Kontrol

Her aşamada:
- PSR-12 uyumluluğu kontrolü
- WordPress coding standards
- Security kontrolleri
- Documentation kontrolü

---

## 📝 AI İstem (Prompt) Prensipleri

### 1. Kontekst Sağlama
Her prompt şu konteksti içermeli:
- Proje adı ve amacı
- İlgili modül dokümantasyonu
- Teknoloji stack
- Kod standartları
- Önceki kod örnekleri

### 2. Net Görev Tanımı
- Sınıf adı ve namespace
- Gerekli metodlar
- Veritabanı tabloları
- Security gereksinimleri
- WordPress hooks

### 3. Çıktı Formatı
- Tam PHP sınıfı
- PHPDoc blokları
- Hata işleme
- Loglama
- Test senaryoları

---

## 🔧 Vibe Coding Araçları

### 1. Prompt Şablonları
- `MODULE-10-AI_Prompt_Templates.md` - Tüm şablonlar

### 2. Kod Şablonları
- Boilerplate sınıflar
- Test şablonları
- Hook örnekleri

### 3. Memory Bank
- `memory-bank/` - Proje konteksti

### 4. Modül Dokümantasyonları
- `MODULE_*.md` - Her modül için detaylı açıklama

---

## ✅ Başarı Kriterleri

1. **Dokümantasyon Tutarlılığı**
   - [ ] Memory Bank her adımda güncel
   - [ ] Kod yorumları açıklayıcı
   - [ ] PHPDoc blokları tam

2. **Kod Kalitesi**
   - [ ] PSR-12 uyumlu
   - [ ] WordPress standards
   - [ ] Security best practices
   - [ ] Hata işleme tam

3. **Test Kapsamı**
   - [ ] Unit testler > 80%
   - [ ] Integration testler
   - [ ] End-to-end testler

4. **Deployment Hazırlığı**
   - [ ] Build script çalışıyor
   - [ ] CI/CD pipeline
   - [ ] Deployment rehberi

---

## 🎨 Vibe Coding İçin İdeal Ortam

### Gerekli Dosyalar
- ✅ Memory Bank (memory-bank/)
- ✅ Modül dokümantasyonları (MODULE_*.md)
- ✅ Prompt şablonları (MODULE-10-AI_Prompt_Templates.md)
- ✅ Teknik dokümantasyon (README.md, implementation_plan.md)
- ✅ Kod örnekleri (includes/)

### Geliştirme Ortamı
- PHP 8.0+
- WordPress 6.0+
- MySQL 5.7+
- Node.js (NPM)
- VSCode with PHP, JS extensions

### Build Araçları
- Composer
- NPM/webpack
- PHPUnit
- PHP_CodeSniffer
- PHPStan

---

## 🚨 Uyarılar ve Notlar

1. **Sırayla Geliştirme**
   - Modüller tanımlı sıraya göre geliştirilmeli
   - Bağımlılıkları kontrol et

2. **Test-Driven Development**
   - Önce test, sonra kod
   - Her commit'te testleri çalıştır

3. **Security Öncelikli**
   - Her kod güvenlik kontrollerini içermeli
   - Sensitive data encryption

4. **Dokümantasyon**
   - Her değişiklik Memory Bank'e yansıtılmalı
   - ActiveContext güncel tutulmalı

---

## 📊 İlerleme Takibi

### Mevcut Durum
- Planning: ✅ 100%
- Infrastructure: 🔄 50%
- Core Architecture: ⏳ 0%
- Geocoding: ⏳ 0%
- Zone Detection: ⏳ 0%
- Pricing: ⏳ 0%
- Booking Flow: ⏳ 0%
- Admin Interface: ⏳ 0%
- Frontend: ⏳ 0%
- Testing: ⏳ 0%
- Deployment: ⏳ 0%

### Hedefler
- Q1: Core + Geocoding + Zone Detection
- Q2: Pricing + Booking Flow
- Q3: Admin + Frontend
- Q4: Testing + Deployment

---

## 💡 Vibe Coding İpuçları

1. **Prompt Uzunluğu**: Her prompt yeterli kontekst içermeli ama çok uzun olmamalı
2. **Incremental Geliştirme**: Küçük adımlar, sık iterasyonlar
3. **Feedback Loop**: Her adımda output'u kontrol et ve düzelt
4. **Template Reuse**: Hazır şablonları kullan ve customize et
5. **Memory Bank Updating**: Her değişiklikten sonra Memory Bank'i güncelle

## 🤖 Multi-AI Collaboration Guidelines

1. **File Locking**: Always check and create lock files in `.locks/` before modifying files
2. **Handoff Markers**: Use handoff comments when switching between AIs:
   ```php
   // AI Handoff: [AI Name] completed [feature/module] at [timestamp]
   // Next AI should continue with [next task]
   ```
3. **Communication**: Keep `memory-bank/activeContext.md` updated with progress
4. **Consistency**: Maintain consistent naming, styling, and documentation across all AIs
5. **Conflict Prevention**: Use atomic changes and avoid overlapping work on same files

---

## 🔄 Sürekli İyileştirme

Vibe coding süreci sürekli iyileştirilmeli:
1. Prompt şablonlarını optimize et
2. Kod kalitesini izle
3. Geliştirme hızını ölç
4. Hata oranını takip et
5. Memory Bank'i güncel tut