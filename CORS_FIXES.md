# CORS Sorunları - Tespit ve Düzeltme Planı

## 🔴 Kritik Sorunlar

### 1. Duplikasyon: Çift CORS Header Ayarları

**Etkilenen Dosyalar:**
- `backend/api/admin/login.php` (Satır 2-17)
- `backend/api/admin/settings.php`
- `backend/api/admin/orders.php`
- Diğer admin endpoint'leri

**Sorun:**
```php
// YANLIŞ: Hem cors.php include ediliyor, hem de manuel header'lar var
require_once __DIR__ . '/../cors.php';  // ✓ CORS header'larını set eder

// ❌ Aynı header'lar tekrar set ediliyor (GEREKSIZ ve POTANSİYEL SORUN)
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

**Neden Sorun?**
- Header'lar birden fazla kez set edildiğinde son set edilen geçerli olur
- Tutarsızlıklara neden olabilir
- Kod tekrarı ve bakım zorluğu
- Farklı dosyalarda farklı CORS ayarları kullanılabilir

**Düzeltme:**
```php
// DOĞRU
require_once __DIR__ . '/../cors.php';
header('Content-Type: application/json');

// Başka CORS header'ı eklemeyin!
```

### 2. Tutarsız CORS Konfigürasyonları

**Sorun:**
- `backend/api/cors.php`: Merkezi konfigürasyon
- `backend/test_cors.php`: Farklı konfigürasyon
- Manuel header'lar: Yine farklı ayarlar

**Karşılaştırma:**

| Dosya | Allowed Origins | Credentials | Max-Age | Vary Header |
|-------|----------------|-------------|---------|-------------|
| cors.php | 4 origin | ✓ true | ✗ yok | ✗ yok |
| test_cors.php | * (all) | ✗ yok | ✗ yok | ✗ yok |
| admin/login.php | dynamic | ✓ true | ✗ yok | ✓ var |

**Düzeltme:**
Tek bir standart konfigürasyon kullanın.

### 3. Eksik Header'lar

**Eksik Vary Header:**
- Caching sorunlarına neden olabilir
- Farklı origin'lerden gelen isteklerin cache'lenmesi problematik
- CDN ve proxy'ler için önemli

**Eksik Max-Age:**
- Preflight isteklerinin her seferinde tekrarlanması
- Performans kaybı
- Gereksiz network trafiği

**Düzeltme:**
```php
header('Vary: Origin');
header('Access-Control-Max-Age: 86400');  // 24 saat
```

## 🟡 Orta Seviye Sorunlar

### 4. OPTIONS Handler Tutarsızlığı

**Sorun:**
Bazı dosyalarda OPTIONS handler var, bazılarında yok.

**Var:**
- `backend/api/cors.php` (✓)
- `backend/api/admin/login.php` (✓)

**Yok veya farklı:**
- Bazı endpoint'ler

**Düzeltme:**
Tüm CORS dosyasında merkezi OPTIONS handler:
```php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}
```

### 5. Frontend Fetch Ayarları

**Sorun:**
Bazı fetch isteklerinde credentials ayarı eksik.

**Kontrol Edilmesi Gerekenler:**
```typescript
// src/lib/stores/auth.ts
// src/routes/admin/login/+page.svelte
// Diğer auth işlemleri

// Gerekli ayar
fetch(url, {
  credentials: 'include',  // ← Cookie'ler için gerekli
  // ...
})
```

## 📋 Düzeltme Planı

### Adım 1: Merkezi CORS Dosyasını Güncelle

**Dosya:** `backend/api/cors.php`

```php
<?php
/**
 * Centralized CORS Configuration
 * Include this file at the top of all API endpoints
 */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Allowed origins list
$allowedOrigins = [
    'https://bezmidar.de',
    'https://www.bezmidar.de',
    'http://localhost:5173',
    'http://localhost:4173'
];

// Set CORS headers
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Fallback for development or if origin is missing
    header("Access-Control-Allow-Origin: *");
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}
```

### Adım 2: Tüm Endpoint'lerde Manuel Header'ları Kaldır

**Düzeltilecek Dosyalar:**
1. `backend/api/admin/login.php`
2. `backend/api/admin/settings.php`
3. `backend/api/admin/orders.php`
4. `backend/api/admin/messages.php`
5. `backend/api/admin/stats.php`
6. `backend/api/admin/packages.php`
7. `backend/api/admin/unmatched.php`
8. `backend/api/admin/deploy.php`
9. `backend/api/admin/seo.php`

**Her dosya için:**
```php
// ÖNCE
require_once __DIR__ . '/../cors.php';
header('Access-Control-Allow-Origin: ' . $allowedOrigin);  // ← KALDIR
header('Access-Control-Allow-Credentials: true');          // ← KALDIR
header('Access-Control-Allow-Methods: POST, OPTIONS');     // ← KALDIR
header('Access-Control-Allow-Headers: ...');               // ← KALDIR

// SONRA
require_once __DIR__ . '/../cors.php';
header('Content-Type: application/json');  // Sadece bu kalabilir
```

### Adım 3: Test Dosyasını Güncelle

**Dosya:** `backend/test_cors.php`

Bu dosyayı da standart `cors.php` kullanacak şekilde güncelleyin:

```php
<?php
require_once __DIR__ . '/api/cors.php';

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'CORS is working!',
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? '*',
    'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown'
]);
```

### Adım 4: Frontend Credentials Ayarları

**Kontrol edilecek dosyalar:**
- `src/lib/stores/auth.ts`
- `src/routes/admin/login/+page.svelte`
- `src/routes/login/+page.svelte`
- `src/lib/api.ts`

**Eklenecek ayar:**
```typescript
// Token kullanan istekler için
fetch(API_URL + '/endpoint', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  credentials: 'include',  // ← EKLE
  body: JSON.stringify(data)
})
```

### Adım 5: .htaccess Kontrolü (Hostinger)

**Dosya:** `backend/.htaccess`

CORS header'larının .htaccess'de tekrarlanmadığından emin olun:

```apache
# CORS header'ları .htaccess'de OLMAMALI
# PHP tarafında hallediliyor

# Sadece rewrite rules olmalı
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## ✅ Doğrulama Checklist'i

Düzeltmelerden sonra:

- [ ] CLI test suite çalıştırıldı: `php backend/test-cors-suite.php`
- [ ] Tüm testler başarılı (100% success rate)
- [ ] Frontend dashboard kontrolü: `/admin/cors-test`
- [ ] Browser console'da CORS hatası yok
- [ ] Network tab'de header'lar doğru
- [ ] Localhost'ta çalışıyor
- [ ] Production'da çalışıyor (bezmidar.de)
- [ ] Login işlemi başarılı
- [ ] Authenticated istekler çalışıyor
- [ ] Cookie'ler set ediliyor
- [ ] CORS preflight (OPTIONS) başarılı

## 🔍 Detaylı Test Senaryoları

### Test 1: Basic GET
```bash
curl -X GET http://localhost:8000/api/packages \
  -H "Origin: http://localhost:5173" \
  -v

# Kontrol edilecekler:
# ✓ Status: 200
# ✓ Access-Control-Allow-Origin: http://localhost:5173
# ✓ Access-Control-Allow-Credentials: true
# ✓ Vary: Origin
```

### Test 2: Preflight OPTIONS
```bash
curl -X OPTIONS http://localhost:8000/api/auth/login \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  -v

# Kontrol edilecekler:
# ✓ Status: 204 veya 200
# ✓ Access-Control-Allow-Methods: GET, POST, ...
# ✓ Access-Control-Allow-Headers: Content-Type, Authorization, ...
# ✓ Access-Control-Max-Age: 86400
```

### Test 3: POST with Auth
```bash
curl -X POST http://localhost:8000/api/admin/login \
  -H "Origin: http://localhost:5173" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer test-token" \
  -d '{"username":"admin","password":"test"}' \
  -v

# Kontrol edilecekler:
# ✓ CORS header'ları var
# ✓ İstek işlendi
# ✓ Response alındı
```

### Test 4: Production Test
```bash
curl -X GET https://api.bezmidar.de/packages \
  -H "Origin: https://bezmidar.de" \
  -v

# Kontrol edilecekler:
# ✓ SSL çalışıyor
# ✓ CORS header'ları doğru
# ✓ Response alınıyor
```

## 📊 Beklenen Test Sonuçları

### CLI Test Suite
```
Total Tests:  25
Passed:       25
Failed:       0
Success Rate: 100.0%

🎉 All tests passed!
```

### Frontend Dashboard
```
Total: 11
Passed: 11
Failed: 0
Success Rate: 100%
```

### Browser Console
```
✓ No CORS errors
✓ All API requests successful
✓ Authentication working
✓ Cookies being set
```

## 🚨 Dikkat Edilmesi Gerekenler

1. **Production'a Deploy Etmeden Önce:**
   - Local'de tüm testler geçmeli
   - Farklı tarayıcılarda test et (Chrome, Firefox, Safari)
   - Incognito mode'da test et

2. **Deploy Sonrası:**
   - Production URL'leri ile testleri çalıştır
   - Gerçek kullanıcı flow'unu test et
   - Monitor logs for CORS errors

3. **Güvenlik:**
   - Production'da `Access-Control-Allow-Origin: *` kullanma
   - Sadece güvenilir origin'lere izin ver
   - Credentials ile `*` origin birlikte kullanılamaz

4. **Performance:**
   - `Access-Control-Max-Age` ayarını kullan
   - Gereksiz preflight isteklerini önle
   - CDN cache ayarlarını kontrol et

## 📝 Notlar

- Bu düzeltmeler backward compatible
- Mevcut fonksiyonalite etkilenmeyecek
- Sadece CORS konfigürasyonu standardize ediliyor
- Tüm değişiklikler git ile takip edilebilir

## 🔄 Rollback Planı

Eğer sorun çıkarsa:

1. Git ile önceki commit'e dön:
   ```bash
   git revert HEAD
   ```

2. Veya manuel olarak eski dosyaları geri yükle

3. CORS yapılandırmasını tek tek dosyalarda kontrol et

4. Test suite ile sorunlu dosyaları tespit et
