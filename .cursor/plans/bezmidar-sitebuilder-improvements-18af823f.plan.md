<!-- 18af823f-498a-42e0-8af4-59d655b47dc1 01a8d9d7-12ad-4d43-9501-ddebd73aa074 -->
# Bezmidar Sitebuilder İyileştirme Planı

## 1. Sipariş Takip UI/UX İyileştirmeleri

### 1.1 Müşteri Hesabındaki Sipariş Takibi

- **Dosya**: `frontend/src/routes/dashboard/+page.svelte`
- Sipariş listesindeki her siparişe tıklanınca açılan detay sayfasını iyileştir
- **Dosya**: `frontend/src/routes/order-status/[orderId]/+page.svelte` - Bu sayfada hata var, düzeltilecek
- **Dosya**: `frontend/src/lib/components/OrderStatus.svelte` - Tasarımı geliştir:
  - Daha görsel adım göstergeleri (timeline)
  - Her adım için net görsel durumlar (tamamlandı, aktif, bekliyor)
  - Müşterinin hangi aşamada olduğunu ve ne yapması gerektiğini net gösteren kartlar
  - Tema ile uyumlu renkler ve ikonlar

### 1.2 Ödeme Uyarı Animasyonu

- **Dosya**: `frontend/src/routes/order-status/[orderId]/+page.svelte`
- `order_status === 'pending_confirmation'` durumunda yanıp sönen animasyonlu uyarı göster
- Mesaj: "Ödeme İşleminizi Tamamlayınız"
- Google Apps Script webhook tetiklendiğinde (ödeme maili alındığında) bu uyarı kaldırılacak
- **Backend**: `backend/api/webhook/payment.php` - Ödeme alındığında status'u `payment_received` yapıyor (mevcut)

### 1.3 Google Apps Script Email Trigger Yapılandırması

- **Dosya**: `google_apps_script.js` (güncellenecek)
- **Akış Detayları**:

  1. Ruul.io ödeme onayı `kayacuneyd@gmail.com` adresine gönderilecek
  2. Hostinger auto-forward ile bu mail admin e-postasına (ör: admin@bezmidar.de) yönlendirilecek
  3. Google Apps Script admin e-postasını izleyecek
  4. Ruul.io'dan gelen ödeme e-postaları admin kutusuna düştüğünde otomatik webhook tetiklenecek

- E-posta query: `from:ruul.io OR from:noreply@ruul.io subject:"Payment" OR subject:"Ödeme" is:unread`
- E-posta body'sinden order_id ve domain bilgisini çıkar
- E-posta geldiğinde webhook'u tetikle ve domain bilgisini de gönder
- **Not**: Hostinger auto-forward ayarları manuel yapılacak (dokümantasyonda belirtilecek)

### 1.4 Otomatik Website Yükleme Süreci Hazırlığı

- **Dosya**: `backend/api/webhook/payment.php` (güncellenecek)
- Webhook ödeme onayını aldığında:

  1. Sipariş durumunu `payment_received` yap
  2. Domain bilgisini al ve logla
  3. Website yükleme sürecini başlatma hazırlığı yap (şimdilik log olarak kaydet)
  4. Admin dashboard'da bu süreç görülebilir olmalı

- **Not**: Tam otomatik website yükleme için Hostinger API entegrasyonu gerekebilir. Şimdilik manuel adımlar için hazırlık yapılacak ve admin'e bildirim gönderilecek.

### 1.5 Admin Manuel Ödeme Durumu Değiştirme

- **Dosya**: `frontend/src/routes/admin/orders/[orderId]/+page.svelte`
- Admin sipariş detay sayfasında "Ödeme Alındı" butonu ekle
- Bu buton tıklandığında sipariş durumunu `payment_received` yap
- **Backend**: `backend/api/admin/orders.php` - PATCH endpoint'ine ödeme durumu güncelleme özelliği ekle

## 2. Navbar Düzenlemeleri

### 2.1 Müşteri Navbar'ından Admin Butonunu Kaldır

- **Dosya**: `frontend/src/lib/components/Header.svelte`
- `$customerAuth.isAuthenticated` durumunda "Admin" linkini gösterme
- Sadece admin kullanıcıları admin paneline erişebilir

### 2.2 Admin Navbar'ını Yeniden Düzenle

- **Dosya**: `frontend/src/routes/admin/+layout.svelte`
- "Hesabım", "Çıkış", "Admin" butonlarını kaldır
- "Siteyi Gör" butonu ekle (ana siteye yönlendirir: `/`)
- Admin panel içinde gezinme için mevcut menü yapısını koru

## 3. Admin Özellikleri

### 3.1 Ürün Paketi Yönetimi

- **Yeni Dosya**: `frontend/src/routes/admin/packages/+page.svelte`
- **Yeni Dosya**: `backend/api/admin/packages.php`
- **Veritabanı**: `packages` tablosu oluştur:
  ```sql
  CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    payment_link TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );
  ```

- Paket ekleme, düzenleme, silme özellikleri
- Her paket için özel ödeme linki tanımlama ve değiştirme

### 3.2 İletişim Sayfası ve Mesaj Yönetimi

- **Yeni Dosya**: `frontend/src/routes/contact/+page.svelte`
- **Yeni Dosya**: `backend/api/contact.php` (POST - mesaj gönderme)
- **Yeni Dosya**: `frontend/src/routes/admin/messages/+page.svelte`
- **Yeni Dosya**: `backend/api/admin/messages.php` (GET - mesajları listele, PATCH - cevapla/okundu işaretle)
- **Veritabanı**: `contact_messages` tablosu oluştur:
  ```sql
  CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    reply TEXT,
    replied_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  ```


### 3.3 Admin Menüsüne Yeni Sayfalar Ekle

- **Dosya**: `frontend/src/routes/admin/+layout.svelte`
- "Paketler" ve "Mesajlar" menü öğeleri ekle

## 4. SEO Ayarları

### 4.1 SEO Meta Tag Yönetimi

- **Yeni Dosya**: `frontend/src/routes/admin/seo/+page.svelte`
- **Yeni Dosya**: `backend/api/admin/seo.php`
- **Veritabanı**: `settings` tablosuna SEO ayarları ekle (mevcut tablo kullanılacak)
- Meta title, description, keywords, og:image gibi ayarlar
- **Dosya**: `frontend/src/app.html` - Dinamik meta tag'ler ekle
- **Dosya**: `frontend/src/routes/+layout.svelte` - SEO meta tag'lerini yönet

## 5. Site İsmi Güncellemeleri

### 5.1 Site İsmini "BEZMİDAR SITEBUILDER" Olarak Değiştir

- **Dosya**: `frontend/src/lib/components/Header.svelte` - Logo/metin güncelle
- **Dosya**: `frontend/src/lib/components/Footer.svelte` - Footer'da site ismi
- **Dosya**: `frontend/src/app.html` - Varsayılan title
- **Dosya**: Tüm sayfa title'ları (`<svelte:head>` içinde) - "WebsiteBuilder" yerine "Bezmidar Sitebuilder"
- **Dosya**: `frontend/src/routes/+page.svelte` - Ana sayfa içeriği

## 6. Veritabanı Değişiklikleri

- `packages` tablosu oluştur
- `contact_messages` tablosu oluştur
- `orders` tablosundaki `package_type` ENUM'u kaldırıp `package_id` INT (foreign key) yap (opsiyonel - mevcut yapı korunabilir)

## 7. Ödeme Linki Yönetimi

- Paket bazlı ödeme linkleri `packages` tablosunda saklanacak
- Sipariş oluştururken paket ID'sine göre ödeme linki alınacak
- **Dosya**: `backend/api/orders.php` - Paket ID'ye göre ödeme linki çekme mantığı güncellenecek

## Notlar

- Google Apps Script için Hostinger auto-forward ayarları manuel yapılacak (kayacuneyd@gmail.com -> admin@bezmidar.de)
- Mevcut `package_type` ENUM yapısı korunabilir veya `packages` tablosuna geçiş yapılabilir (kullanıcı tercihine göre)
- Tüm yeni admin sayfaları için authentication kontrolü eklenecek
- Website yükleme süreci şimdilik manuel adımlar için hazırlık yapılacak, ileride Hostinger API ile otomatikleştirilebilir

### To-dos

- [ ] Sipariş takip sayfası UI/UX iyileştirmeleri - OrderStatus component ve order detail sayfası tasarım geliştirmeleri
- [ ] Ödeme beklenirken yanıp sönen 'Ödeme İşleminizi Tamamlayınız' uyarısı ekle
- [ ] Google Apps Script'i Hostinger e-posta hesabı ile çalışacak şekilde güncelle
- [ ] Müşteri navbar'ından Admin butonunu kaldır
- [ ] Admin navbar'ını yeniden düzenle - Hesabım/Çıkış/Admin kaldır, Siteyi Gör ekle
- [ ] packages tablosunu oluştur ve migration script hazırla
- [ ] Paket yönetimi backend API (CRUD işlemleri)
- [ ] Admin paket yönetimi sayfası (ekle, düzenle, sil, ödeme linki yönetimi)
- [ ] contact_messages tablosunu oluştur
- [ ] İletişim sayfası oluştur (frontend form + backend API)
- [ ] Admin mesaj yönetimi sayfası (listele, cevapla, okundu işaretle)
- [ ] SEO ayarları yönetimi (admin panel + frontend meta tag entegrasyonu)
- [ ] Tüm site içinde 'WebsiteBuilder' -> 'Bezmidar Sitebuilder' değişiklikleri
- [ ] Sipariş oluştururken paket bazlı ödeme linki kullanımı