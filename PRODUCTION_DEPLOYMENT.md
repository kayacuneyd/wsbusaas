# Production Deployment - CORS Fix

## 🚨 Acil CORS Düzeltmesi - Deployment Talimatları

### Sorun
Production'da (bezmidar.de) CORS hatası:
```
Access to fetch at 'https://api.bezmidar.de/api/seo' from origin 'https://www.bezmidar.de'
has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present
```

### Kök Neden
`.htaccess` dosyasındaki CORS header'ları environment variable'a bağlı ve her zaman çalışmıyor.

### Çözüm
PHP tabanlı CORS kontrolü (`api/cors.php`) kullanarak tutarlı davranış sağlamak.

---

## 📋 Deploy Edilecek Dosyalar

### Kritik Değişiklikler
1. ✅ `backend/.htaccess` - CORS Apache katmanından kaldırıldı
2. ✅ `backend/api/cors.php` - Güncellenmiş merkezi CORS
3. ✅ `backend/api/admin/login.php` - Duplikasyon kaldırıldı
4. ✅ `backend/api/admin/seo.php` - Duplikasyon kaldırıldı
5. ✅ `backend/api/admin/unmatched.php` - Duplikasyon kaldırıldı
6. ✅ `backend/api/admin/diagnose.php` - Duplikasyon kaldırıldı
7. ✅ `backend/api/user/orders.php` - Duplikasyon kaldırıldı

---

## 🚀 Deployment Adımları

### Seçenek 1: Otomatik Deployment (Önerilen)

```bash
# 1. Mevcut dizinde olduğunuzdan emin olun
cd /Users/thomasmuentzer/Desktop/wsbusaas

# 2. Deploy script'ini çalıştırın
./deploy_backend.sh
```

**Beklenen Çıktı:**
```
Backend dosyaları Hostinger'a yükleniyor...
sending incremental file list
.htaccess
api/cors.php
api/admin/login.php
api/admin/seo.php
...
Yükleme tamamlandı!
```

### Seçenek 2: Manuel Deployment

Eğer script çalışmazsa manuel olarak:

```bash
# SSH ile bağlan
ssh -p 65002 -i hostinger_key u553245641@185.224.137.82

# Hedef dizine git
cd /home/u553245641/domains/bezmidar.de/public_html/api

# Yedek al
cp .htaccess .htaccess.backup
cp cors.php cors.php.backup
```

Sonra FTP/cPanel File Manager ile dosyaları yükleyin.

---

## ✅ Deployment Sonrası Test

### 1. CORS Header Kontrolü

```bash
# Test 1: Packages endpoint
curl -X GET https://api.bezmidar.de/api/packages \
  -H "Origin: https://bezmidar.de" \
  -v

# BAŞARILI ise görmeli:
# < Access-Control-Allow-Origin: https://bezmidar.de
# < Access-Control-Allow-Credentials: true
```

### 2. Preflight (OPTIONS) Testi

```bash
# Test 2: OPTIONS request
curl -X OPTIONS https://api.bezmidar.de/api/seo \
  -H "Origin: https://www.bezmidar.de" \
  -H "Access-Control-Request-Method: GET" \
  -v

# BAŞARILI ise görmeli:
# < HTTP/1.1 204 No Content
# < Access-Control-Allow-Origin: https://www.bezmidar.de
# < Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS
```

### 3. Browser Test

1. https://www.bezmidar.de adresine git
2. Browser Console'u aç (F12)
3. Console'da CORS hatası **olmamalı**
4. Network tab'de istekleri kontrol et:
   - ✅ Preflight (OPTIONS) requests: 204 status
   - ✅ GET/POST requests: 200 status
   - ✅ Response Headers içinde `Access-Control-Allow-Origin` var

### 4. Test Suite (Production)

```bash
# Test suite'i production için ayarla
vim backend/test-cors-suite.php

# Satır 15'i değiştir:
# 'base_url' => 'https://api.bezmidar.de'

# Testi çalıştır
php backend/test-cors-suite.php

# Beklenen: 100% başarı
```

---

## 🔍 Sorun Giderme

### Sorun: Hala CORS hatası alıyorum

**Çözüm 1: Cache Temizle**
```bash
# Browser cache temizle
# Chrome: Ctrl+Shift+Delete
# Incognito mode'da test et

# Hostinger cache temizle (cPanel > File Manager)
# .htaccess dosyasına dokunarak:
touch /home/u553245641/domains/bezmidar.de/public_html/api/.htaccess
```

**Çözüm 2: PHP Dosyalarının İzinleri**
```bash
# SSH'da:
cd /home/u553245641/domains/bezmidar.de/public_html/api
chmod 644 .htaccess
chmod 644 cors.php
chmod 644 admin/*.php
```

**Çözüm 3: Error Log Kontrol**
```bash
# Hostinger error log:
tail -f ~/domains/bezmidar.de/logs/error.log

# PHP hatalarını ara:
grep CORS ~/domains/bezmidar.de/logs/error.log
```

### Sorun: 404 Not Found

**Neden:** `.htaccess` rewrite rules çalışmıyor

**Çözüm:**
```bash
# .htaccess'in doğru yerde olduğunu kontrol et:
ls -la /home/u553245641/domains/bezmidar.de/public_html/api/.htaccess

# mod_rewrite aktif mi kontrol et (cPanel > Apache Handler)
# Veya .htaccess'e ekle:
RewriteEngine On
```

### Sorun: Header'lar duplike görünüyor

**Neden:** Hem `.htaccess` hem `cors.php` header set ediyor

**Çözüm:**
```bash
# .htaccess'ten CORS header'larını tamamen kaldır
# Sadece şu satırlar olmalı:
RewriteEngine On
RewriteBase /
# ... (route rules)
```

---

## 📊 Deployment Checklist

Deploy öncesi:
- [ ] Local testler %100 başarılı
- [ ] Değişiklikler git'te commit edildi
- [ ] SSH key'i test edildi
- [ ] Backup alındı

Deploy sırasında:
- [ ] `./deploy_backend.sh` çalıştırıldı
- [ ] Dosya yükleme başarılı

Deploy sonrası:
- [ ] CORS header testi başarılı
- [ ] OPTIONS request başarılı
- [ ] Browser console temiz (CORS hatası yok)
- [ ] Anasayfa yükleniyor
- [ ] SEO verileri yükleniyor
- [ ] Packages yükleniyor
- [ ] Admin panel çalışıyor

---

## 🔐 Güvenlik Notları

### Production Origin'leri

`backend/api/cors.php` dosyasında sadece şunlar olmalı:

```php
$allowedOrigins = [
    'https://bezmidar.de',
    'https://www.bezmidar.de',
    // Development origin'leri PRODUCTION'da OLMAMALI:
    // 'http://localhost:5173',  // ❌ KALDIR
    // 'http://localhost:4173',  // ❌ KALDIR
];
```

**ÖNEMLİ:** Eğer local test için production'a istek atıyorsanız, geçici olarak ekleyip sonra kaldırın.

### Test Endpoint'leri

Production'da test endpoint'leri kapatılmalı:

```bash
# Bu dosyaları production'a YÜKLEME:
backend/test-cors-suite.php
backend/api/test/cors-diagnostics.php
backend/api/test/cors-endpoints.php
```

---

## 📝 Rollback Planı

Eğer deployment sonrası sorun çıkarsa:

### Hızlı Rollback (SSH)

```bash
# SSH ile bağlan
ssh -p 65002 -i hostinger_key u553245641@185.224.137.82

# Backup'tan geri yükle
cd /home/u553245641/domains/bezmidar.de/public_html/api
cp .htaccess.backup .htaccess
cp cors.php.backup cors.php
cp admin/login.php.backup admin/login.php

# Apache'yi restart et (opsiyonel)
# Genelde .htaccess değişiklikleri otomatik yüklenir
```

### Git Rollback (Local)

```bash
# Son commit'i geri al
git log --oneline | head -5
git revert <commit-hash>

# Tekrar deploy et
./deploy_backend.sh
```

---

## 🎯 Beklenen Sonuç

Deploy başarılı olduktan sonra:

1. ✅ https://www.bezmidar.de anasayfası CORS hatası olmadan açılır
2. ✅ SEO verileri yüklenir (console'da hata yok)
3. ✅ Packages listesi yüklenir
4. ✅ Contact form çalışır
5. ✅ Admin panel login olur
6. ✅ Tüm API endpoint'leri düzgün response verir

### Success Metrics

- **Before:** CORS errors in console
- **After:** No CORS errors ✅
- **Before:** API requests fail
- **After:** All API requests succeed ✅
- **Before:** 404 errors
- **After:** 200/204 responses ✅

---

## 📞 Yardım

Sorun devam ederse:

1. Browser Console screenshot'unu al
2. Network tab'den başarısız request'i kopyala:
   - Request Headers
   - Response Headers
   - Status Code
3. Error log'u kontrol et:
   ```bash
   tail -50 ~/domains/bezmidar.de/logs/error.log
   ```

---

**Son Güncelleme:** 2025-11-30
**Deploy Ortamı:** Hostinger Shared Hosting
**Domain:** bezmidar.de / www.bezmidar.de
