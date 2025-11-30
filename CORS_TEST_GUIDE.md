# CORS Test ve Tanılama Kılavuzu

Bu belge, uygulamanızdaki CORS sorunlarını tespit etmek ve çözmek için oluşturulan kapsamlı test sisteminin kullanım kılavuzudur.

## 🎯 Oluşturulan Test Araçları

### 1. Backend Test Endpoint'leri

#### `/api/test/cors-diagnostics.php`
Kapsamlı CORS tanılama endpoint'i. Tüm CORS konfigürasyonlarını test eder ve sorunları tespit eder.

**Test Tipleri:**
```bash
# Temel bağlantı testi
GET /api/test/cors-diagnostics.php?test=basic

# Preflight OPTIONS testi
OPTIONS /api/test/cors-diagnostics.php?test=preflight

# Kimlik bilgileri testi
GET /api/test/cors-diagnostics.php?test=credentials

# Özel header testi
GET /api/test/cors-diagnostics.php?test=custom-headers

# HTTP method testi
GET /api/test/cors-diagnostics.php?test=methods

# Origin validasyon testi
GET /api/test/cors-diagnostics.php?test=origin-validation

# POST data testi
POST /api/test/cors-diagnostics.php?test=post-data
```

**Özellikler:**
- Tüm request header'larını yakalar
- Response header'larını analiz eder
- Tüm API endpoint'lerini tarar
- CORS konfigürasyon sorunlarını tespit eder
- Detaylı diagnostik bilgi sağlar

#### `/api/test/cors-endpoints.php`
Farklı CORS senaryolarını test etmek için özel endpoint'ler.

**Mevcut Endpoint'ler:**
```bash
# Basit GET isteği
GET /api/test/cors-endpoints.php?endpoint=simple

# Authorization header ile
GET /api/test/cors-endpoints.php?endpoint=with-auth
Headers: Authorization: Bearer YOUR_TOKEN

# Cookie testi
GET /api/test/cors-endpoints.php?endpoint=with-cookies

# JSON POST testi
POST /api/test/cors-endpoints.php?endpoint=post-json
Body: {"test": "data"}

# Veritabanı bağlantısı ile CORS testi
GET /api/test/cors-endpoints.php?endpoint=database

# Yavaş istek testi (2 saniye)
GET /api/test/cors-endpoints.php?endpoint=slow

# Hata işleme testi
GET /api/test/cors-endpoints.php?endpoint=error

# Büyük payload testi
GET /api/test/cors-endpoints.php?endpoint=large-payload

# Özel header'lar
GET /api/test/cors-endpoints.php?endpoint=custom-headers
```

### 2. Frontend Test Araçları

#### Test Utility Library (`src/lib/utils/corsTest.ts`)
Frontend'den CORS testleri çalıştırmak için kapsamlı utility fonksiyonları.

**Kullanım:**
```typescript
import {
  runCorsTestSuite,
  quickCorsCheck,
  getCorsdiagnostics
} from '$lib/utils/corsTest';

// Hızlı CORS kontrolü
const isWorking = await quickCorsCheck();

// Tam test suite'i çalıştır
const results = await runCorsTestSuite(authToken);

// Detaylı diagnostikler
const diagnostics = await getCorsdiagnostics();
```

#### Admin Dashboard (`/admin/cors-test`)
Görsel CORS test paneli. Admin kullanıcıları için kullanımı kolay test arayüzü.

**Özellikler:**
- ✅ Hızlı CORS kontrolü
- 🧪 11 farklı CORS testi
- 📊 Görsel test sonuçları
- 📥 JSON formatında sonuç indirme
- 🔍 Detaylı diagnostik bilgiler
- 📋 Endpoint tarama sonuçları

**Erişim:**
```
http://localhost:5173/admin/cors-test
```

### 3. CLI Test Suite (`test-cors-suite.php`)
Terminal'den otomatik CORS testleri çalıştırmak için CLI aracı.

**Kullanım:**
```bash
# Temel kullanım
php backend/test-cors-suite.php

# Detaylı çıktı
php backend/test-cors-suite.php --verbose

# JSON formatında çıktı
php backend/test-cors-suite.php --output=json

# JSON çıktıyı dosyaya kaydet
php backend/test-cors-suite.php --output=json > cors-results.json
```

**Test Kategorileri:**
1. 📡 Temel Bağlantı
2. 🔍 Preflight İstekleri
3. 🌐 Origin Validasyonu
4. 🔧 HTTP Methodları
5. 📋 Özel Header'lar
6. 🔐 Kimlik Bilgileri
7. 🎯 Mevcut API Endpoint'leri

## 🔍 Tespit Edilen Sorunlar

### 1. Çift CORS Header Sorunu
**Dosyalar:** `backend/api/admin/login.php`, `backend/api/admin/settings.php`

**Sorun:** Hem `cors.php` include ediliyor hem de manuel olarak header'lar tekrar set ediliyor.

**Çözüm:**
```php
// YANLIŞ ❌
require_once __DIR__ . '/../cors.php';
header('Access-Control-Allow-Origin: ' . $allowedOrigin);  // Duplikasyon!

// DOĞRU ✅
require_once __DIR__ . '/../cors.php';
// Başka bir şey yapma, cors.php halledecek
```

### 2. Tutarsız Konfigürasyon
**Dosyalar:** `backend/api/cors.php` vs `backend/test_cors.php`

**Sorun:** Farklı dosyalarda farklı CORS ayarları kullanılıyor.

**Çözüm:** Tüm CORS ayarlarını `backend/api/cors.php` içinde merkezileştirin ve her yerde bunu kullanın.

### 3. Vary Header Eksikliği
**Sorun:** Bazı endpoint'lerde `Vary: Origin` header'ı eksik.

**Önemi:** Caching sorunlarını önlemek için gerekli.

**Çözüm:**
```php
header('Vary: Origin');
```

### 4. Credentials Eksikliği
**Sorun:** Bazı endpoint'lerde `Access-Control-Allow-Credentials` header'ı yok.

**Çözüm:**
```php
header('Access-Control-Allow-Credentials: true');
```

## 🛠️ Önerilen Düzeltmeler

### 1. Merkezi CORS Konfigürasyonu
Tüm endpoint'lerde tutarlı CORS kullanımı için:

```php
// Her API endpoint'inin en başında
require_once __DIR__ . '/../cors.php';  // veya doğru path
require_once __DIR__ . '/../../cors.php';

// Sonra başka header'lar eklenebilir
header('Content-Type: application/json');
```

### 2. Standardize Edilmiş `cors.php`
`backend/api/cors.php` dosyasını tüm gereksinimleri karşılayacak şekilde güncelleyin:

```php
<?php
// Centralized CORS Handling
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://bezmidar.de',
    'https://www.bezmidar.de',
    'http://localhost:5173',
    'http://localhost:4173'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}
```

### 3. Manuel Header'ları Kaldırın
`cors.php` include edilen dosyalarda manuel CORS header'larını kaldırın:

```php
// backend/api/admin/login.php
require_once __DIR__ . '/../cors.php';

// BU SATIRLARI KALDIR ❌
// header('Access-Control-Allow-Origin: ' . $allowedOrigin);
// header('Access-Control-Allow-Credentials: true');
// header('Access-Control-Allow-Methods: POST, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');

header('Content-Type: application/json');  // Sadece content-type kalabilir
```

### 4. Frontend Fetch Ayarları
Credentials gerektiren isteklerde:

```typescript
fetch(url, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  credentials: 'include',  // Cookie'ler için gerekli
  body: JSON.stringify(data)
})
```

## 📋 Test Senaryoları

### Senaryo 1: Yerel Geliştirme Testi
```bash
# Backend'i başlat
cd backend
php -S localhost:8000

# CLI test suite'i çalıştır
php test-cors-suite.php --verbose

# Frontend'den test et
# Tarayıcıda http://localhost:5173/admin/cors-test
```

### Senaryo 2: Production Testi
```bash
# Production URL'leri güncelle
# test-cors-suite.php içinde base_url'i değiştir:
$config = [
    'base_url' => 'https://api.bezmidar.de',
    // ...
];

# Testleri çalıştır
php test-cors-suite.php --output=json > production-cors-test.json
```

### Senaryo 3: Spesifik Endpoint Testi
```bash
# Curl ile manuel test
curl -X OPTIONS https://api.bezmidar.de/auth/login \
  -H "Origin: https://bezmidar.de" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  -v

# Header'ları kontrol et:
# - Access-Control-Allow-Origin
# - Access-Control-Allow-Credentials
# - Access-Control-Allow-Methods
# - Access-Control-Allow-Headers
```

## 🎨 Frontend Test Dashboard Kullanımı

1. Admin olarak giriş yapın
2. `/admin/cors-test` sayfasına gidin
3. Test butonlarını kullanın:
   - **Quick CORS Check**: Hızlı bağlantı kontrolü
   - **Run Full Test Suite**: Tüm testleri çalıştır
   - **Load Diagnostics**: Detaylı sistem bilgileri

4. Sonuçları inceleyin:
   - Yeşil ✓: Test başarılı
   - Kırmızı ✗: Test başarısız
   - Test detaylarını görmek için tıklayın

5. Sonuçları kaydedin:
   - "Download Results" butonu ile JSON formatında indir

## 🔧 Sorun Giderme

### Problem: "CORS policy" hatası
**Çözüm:**
1. Backend'in çalıştığından emin olun
2. Origin'in allowed origins listesinde olduğunu kontrol edin
3. Diagnostics endpoint'ini çağırıp detayları inceleyin

### Problem: Credentials çalışmıyor
**Çözüm:**
1. `Access-Control-Allow-Credentials: true` header'ının olduğunu kontrol edin
2. Frontend'de `credentials: 'include'` kullanıldığından emin olun
3. `Access-Control-Allow-Origin` header'ı `*` olmamalı, spesifik origin olmalı

### Problem: OPTIONS isteği başarısız
**Çözüm:**
1. CORS dosyasında OPTIONS handler'ın olduğunu kontrol edin
2. Doğru HTTP status code dönüldüğünden emin olun (204 veya 200)
3. Gerekli header'ların hepsinin set edildiğini doğrulayın

### Problem: Custom header'lar gönderilemiyor
**Çözüm:**
1. `Access-Control-Allow-Headers` içinde header'ın listelendiğini kontrol edin
2. Preflight isteğinin başarılı olduğunu doğrulayın
3. Header adının doğru yazıldığından emin olun

## 📊 Test Raporları

### Örnek CLI Çıktısı
```
🚀 Starting CORS Test Suite
Base URL: http://localhost:8000/api

📡 Test Category: Basic Connectivity
  ✓ Basic GET request

🔍 Test Category: Preflight (OPTIONS) Requests
  ✓ OPTIONS request from http://localhost:5173
  ✓ OPTIONS request from https://bezmidar.de

============================================================
📊 Test Summary
============================================================

Total Tests:  25
Passed:       24
Failed:       1
Success Rate: 96.0%

⚠️  Some tests failed. Run with --verbose for details.
```

### Örnek JSON Raporu
```json
{
  "total": 25,
  "passed": 24,
  "failed": 1,
  "success_rate": 96,
  "results": [
    {
      "test": "Basic GET request",
      "passed": true,
      "details": {
        "status": 200,
        "headers": {
          "access-control-allow-origin": "http://localhost:5173"
        }
      }
    }
  ]
}
```

## 🚀 Sonraki Adımlar

1. **Test Suite'i Çalıştırın:**
   ```bash
   php backend/test-cors-suite.php --verbose
   ```

2. **Frontend Dashboard'u Kullanın:**
   - http://localhost:5173/admin/cors-test

3. **Tespit Edilen Sorunları Düzeltin:**
   - Çift header'ları kaldırın
   - Merkezi CORS dosyasını kullanın
   - Tüm endpoint'lerde tutarlılığı sağlayın

4. **Testleri Tekrar Çalıştırın:**
   - Düzeltmelerden sonra tüm testlerin geçtiğini doğrulayın

5. **Production'da Test Edin:**
   - Production URL'leri ile testleri tekrarlayın
   - Gerçek kullanıcı senaryolarını test edin

## 📞 Yardım

Test sonuçlarında sorunlar görürseniz:

1. `--verbose` flag'i ile detaylı logları inceleyin
2. Diagnostics endpoint'inden sistem bilgilerini alın
3. Frontend dashboard'dan endpoint scan sonuçlarını kontrol edin
4. Header'ları manuel olarak curl ile test edin

## ✅ Başarı Kriterleri

CORS konfigürasyonunuz şu kriterleri karşılamalı:

- [ ] Tüm CLI testleri geçiyor (100% başarı oranı)
- [ ] Frontend dashboard tüm testleri başarılı gösteriyor
- [ ] Endpoint scan'de "issue" uyarısı yok
- [ ] Production'da gerçek kullanıcı senaryoları çalışıyor
- [ ] Tarayıcı console'da CORS hatası yok
- [ ] Network tab'de tüm isteklerin doğru header'ları var
