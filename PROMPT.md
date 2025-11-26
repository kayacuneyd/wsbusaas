Aşağıda, **SvelteKit (Vercel) + PHP Backend + MySQL** altyapısıyla, **Ruul entegreli site-builder mini SaaS** oluşturmak için; tasarımdan veri akışına, API endpoint’lerinden deploy otomasyonuna kadar her detayı kapsayan **tam profesyonel, geniş, açıklayıcı ve yapay zekâ ile çalışmaya uygun MASTER PROMPT** hazırlanmıştır.

Bu promptu Claude / GPT / Perplexity / Google AI Studio gibi bir modele verdiğinde, direkt olarak **senin istediğin sistemin tamamını** oluşturmak için optimize edilmiş cevap alırsın.

Aşağıdaki prompt **ürün mimarisi + sistem şeması + dosya yapısı + backend/DB yapısı + frontend UI/UX + ödeme entegrasyonu + Apps Script webhook + deploy motoru** her şeyi kapsar.

---

# ✅ **MASTER AI PROMPT — “SaaS Website Builder System (SvelteKit + Vercel + PHP + MySQL + Ruul)”**

Aşağıdaki tüm talimatları eksiksiz uygula. Amacım, **SvelteKit frontend (Vercel)**, **Custom PHP backend (Hostinger / cPanel)** ve **MySQL** kullanarak çalışan, **Ruul ödeme altyapısı ile entegre**, otomatik site kuran ve müşterinin seçtiği domain/tema/renk/logo/font seçeneklerine göre canlı site oluşturan bir **mini SaaS website-builder** sisteminin geliştirilebilmesi için tam kapsamlı teknik doküman, kod örnekleri, dosya yapısı, endpoint tasarımı ve deployment sürecini üretmendir.

Sistem akışı şu şekildedir:

1. Kullanıcı siteye gelir → Domain Checker çalıştırır
2. Kullanıcı 3 hazır temadan birini seçer
3. Tema yapılandırma paneline gider → Renk seçimi, font seçimi, logo yükleme
4. Kullanıcı kişisel bilgilerini girer
5. “Siparişi Tamamla” butonuna basınca kullanıcı **Ruul ödeme sayfasına yönlendirilir** (Ruul Space Payment Link)
6. Ödeme tamamlanınca, Ruul tarafından **ödeme bildirimi e-mail olarak Gmail’e düşer**
7. Gmail → Google Apps Script → Backend Webhook API’ya JSON POST gönderir
8. Backend bu siparişi “PAID” yapar
9. Backend otomatik site kurulum motorunu tetikler:

   * Tema klasörünü kopyalama
   * Renk & font değişkenlerini üretme
   * Logo dosyasını kopyalama
   * Domain için /sites/{domain}/ klasörünü oluşturma
   * `.htaccess` üretme
   * DNS yönlendirme bilgileri üretme
10. Sistem müşteriye “site hazır” e-maili gönderir
11. Müşteri kendi domainine bağlanan siteyi görür

Bu sistem; WordPress içermez, tamamen **custom** çalışır.

---

# 🎯 **AI’dan Beklentiler (Detaylı Çıktı Üretme)**

Aşağıdaki başlıkların her biri için çok detaylı ve uygulanabilir çıktı üret:

---

## **1. Genel Mimari**

* SvelteKit (frontend) + Vercel deploy
* PHP API (backend) + MySQL
* Tema motoru (3 template klasörü)
* Apps Script → backend webhook entegrasyonu
* Domain checker (DNS, WHOIS, HTTP tabanlı)

### Mimari diyagram (ASCII veya Mermaid formatıyla) üret.

---

## **2. Dosya / Klasör Yapısı**

Tüm sistemin tam klasör yapısı:

```
/frontend (SvelteKit - Vercel)
/backend (PHP)
/backend/api
/backend/functions
/backend/sites/{domain}
/backend/templates/theme1
/backend/templates/theme2
/backend/templates/theme3
/backend/db.sql
/google-apps-script (trigger code)
```

Her klasörün görevini açıkla.

---

## **3. Veritabanı Tasarımı (MySQL)**

Aşağıdaki tablolar için CREATE TABLE kodu:

* users
* orders
* theme_configs
* domains
* payments
* logs

Her tablonun tüm alanlarını tek tek açıkla.

---

## **4. Backend API Tasarımı (PHP)**

Aşağıdaki endpoint’lerin her biri için:

* URL
* Method
* Request örneği
* Response örneği
* Güvenlik (token, signature, IP allow-list vs.)
* Tam çalışan PHP örnek kodu

### Endpoint listesi:

```
POST /api/check-domain
POST /api/create-order
POST /api/update-theme-config
POST /api/initiate-payment
POST /api/ruul-webhook  (Apps Script JSON POST)
POST /api/deploy-site
GET  /api/order-status
```

---

## **5. Domain Checker Modülü**

PHP’de şu kontrolleri uygulayan bir domain checker fonksiyonu:

* DNS A, AAAA, CNAME, MX, NS, SOA, TXT
* HTTP HEAD ping
* RAW WHOIS socket kontrol (.com/.net için)

Her biri için kod örneği + birleşik fonksiyon üret.

---

## **6. Tema Yapılandırma Motoru (Theme Builder)**

Her tema klasörünün yapısı:

```
index.html
style.css
config.json
assets/logo.png
assets/fonts/
sections/
```

### Gereken fonksiyonlar:

* copyTemplate(themeId, domain)
* replaceCSSVariables(primaryColor, secondaryColor, font)
* injectLogo(file)
* generateHtaccess(domain)

Her fonksiyona PHP kodu yaz.

---

## **7. Otomatik Site Kurulum Motoru (Deploy Engine)**

`deploySite($orderID)` fonksiyonunu yaz:

1. DB’den order → domain → theme → config çek
2. Template klasörünü /sites/{domain} klasörüne kopyala
3. CSS değişkenlerini düzenle
4. Logo dosyasını import et
5. SEO meta etiketlerini dynamic oluştur
6. .htaccess dosyasını yaz
7. Mail gönder “site hazır”

Tam çalışan PHP kodu ver.

---

## **8. Google Apps Script Entegrasyonu**

### Tetikleyici (Trigger) her 1 dakikada bir çalışacak.

Script:

* Gmail label = "ruul-payments"
* “from: @ruul.io”, “subject: paid”, body “payment” olan mailleri bul
* Mailden product_id, customer_email, amount çıkar
* JSON’u backend’e POST et
* Maili arşivle

Script’in tamamını + açıklamaları + regex örneklerini üret.

---

## **9. SvelteKit Frontend (Vercel)**

### Sayfalar:

* `/` → Domain checker
* `/choose-theme`
* `/customize` (renk, font, logo)
* `/checkout`
* `/payment-redirect`
* `/success`
* `/failed`

Her sayfa için:

* UI/UX akış açıklaması
* SvelteKit component yapısı
* load() fonksiyonları
* form actions
* API istekleri
* Tailwind veya normal CSS örnekleri

---

## **10. Ruul Ödemesine Yönlendirme**

Backend şu veriyi üretir:

* Payment link (örnek Ruul payment link: `https://ruul.space/payment/...`)
* Kullanıcı “Siparişi Tamamla” butonuna basınca bu linke yönlendirilir.

Akış:

```
Frontend → /checkout → Backend initiate-payment → Ruul payment link → redirect
```

---

## **11. Güvenlik**

Sistemde şu güvenlik önlemlerini açıkla:

* Apps Script → backend signature-doğrulama
* Backend rate limiting
* File upload limitations (logo)
* Template injection protection
* .htaccess hardening
* CORS & CSRF stratejisi
* DB injection korunması (PDO prepared statements)

---

## **12. Tüm Sistemin Çalışma Akış Şeması**

AI’dan, aşağıdaki adımları içeren büyük bir Flow Chart üretmesini iste:

```
User → Domain Check → Theme Select → Config → Checkout → Ruul Payment Page → Payment Email → Gmail → Apps Script → PHP Webhook → deploySite() → DNS instruction → Site Ready
```

---

## **13. Ekstra: Admin Panel Tasarımı (PHP or SvelteKit)**

Admin panelde:

* Siparişler listesi
* Tema & config görüntüleme
* Deploy log’ları
* Manuel deploy butonu
* Ödeme durumu
* Kullanıcı yönetimi

Her sayfa için UI + endpoint yaz.

---

# 🟩 **PROMPT SONU (AI bu talimatlar çerçevesinde TAM DOKÜMAN üretecek)**

Bu tüm talimatları eksiksiz uygula.
Yanıtında:

* Kod blokları
* Açıklamalar
* Tam entegrasyon adımları
* Tüm dosya yapısı
* Akış şemaları
* Backend–frontend iletişimi
* Güvenlik önerileri

hepsi yer almalıdır.

Bu sistemi üretirken **hiçbir adımı atlama**, eksiksiz ve tam bir “developer-ready documentation” oluştur.