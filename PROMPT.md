# Website Builder SaaS - Teknik Proje Dokümantasyonu

**Versiyon:** 1.0  
**Tarih:** Kasım 2025  
**Proje Sahibi:** Cüneyt Kaya  
**Durum:** MVP Geliştirme Aşaması

---

## İÇİNDEKİLER

1. [Proje Özeti](#1-proje-özeti)
2. [İş Modeli ve Değer Önerisi](#2-iş-modeli-ve-değer-önerisi)
3. [Kullanıcı Akışı (User Flow)](#3-kullanıcı-akışı-user-flow)
4. [Teknik Mimari](#4-teknik-mimari)
5. [Teknoloji Stack](#5-teknoloji-stack)
6. [Veritabanı Tasarımı](#6-veritabanı-tasarımı)
7. [API Tasarımı](#7-api-tasarımı)
8. [Frontend Yapısı](#8-frontend-yapısı)
9. [Entegrasyonlar](#9-entegrasyonlar)
10. [Güvenlik Önlemleri](#10-güvenlik-önlemleri)
11. [Deployment Stratejisi](#11-deployment-stratejisi)
12. [Geliştirme Yol Haritası](#12-geliştirme-yol-haritası)
13. [Riskler ve Çözümler](#13-riskler-ve-çözümler)

---

## 1. PROJE ÖZETİ

### 1.1 Ne İnşa Ediyoruz?

Almanya'daki küçük işletmelere yönelik **"Website as a Service"** platformu. Müşteri tek bir pakette domain, hosting, website kurulumu ve yıllık bakım hizmeti satın alıyor. Süreç maksimum düzeyde otomatize edilmiş, müşteri deneyimi basit ve hızlı.

### 1.2 Temel Özellikler

| Özellik | Açıklama |
|---------|----------|
| Domain Checker | Müşterinin istediği domain'in müsaitliğini anlık kontrol |
| Tek Paket Satış | Domain + Hosting + Website + Bakım tek fiyatta |
| Otomatik Sipariş Akışı | Ödeme sonrası otomatik bildirim ve takip |
| Template Sistemi | Özelleştirilebilir statik HTML templateler |
| Admin Panel | Sipariş yönetimi ve manuel işlem takibi |

### 1.3 MVP Kapsamı (İlk Versiyon)

MVP'de şunlar **VAR**:
- Landing page (tek paket tanıtımı)
- Domain availability checker
- Sipariş oluşturma ve ruul.io'ya yönlendirme
- Sipariş durumu takip sayfası
- Google Apps Script ile ödeme bildirimi
- Admin panel (sipariş listesi, durum güncelleme)
- Email bildirimleri

MVP'de şunlar **YOK** (sonraki fazlarda):
- Otomatik domain satın alma (manuel yapılacak)
- Otomatik hosting kurulumu (manuel yapılacak)
- Otomatik template deployment (manuel yapılacak)
- Çoklu paket seçenekleri
- Müşteri dashboard'u

---

## 2. İŞ MODELİ VE DEĞER ÖNERİSİ

### 2.1 Neden Bu Model?

```
PROBLEM:
├── Küçük işletmeler website istiyorlar ama...
│   ├── Teknik bilgileri yok
│   ├── Domain, hosting, website ayrı ayrı uğraştırıcı
│   └── Freelancer bulmak ve yönetmek zor
│
ÇÖZÜM:
├── Tek paket, tek fiyat, tek muhatap
│   ├── "X Euro/yıl öde, gerisini biz halledelim"
│   └── Yıllık yenileme = Recurring Revenue
```

### 2.2 Gelir Modeli

```
Yıllık Paket Fiyatı: ~299€ (örnek)
├── Domain maliyeti: ~15€/yıl
├── Hosting maliyeti: ~50€/yıl (Hostinger Business)
├── Kurulum işçiliği: ~1-2 saat
└── Net Kar Marjı: ~200€/müşteri/yıl
```

### 2.3 Ödeme Akışı (ruul.io)

Almanya'da şirket olmadan fatura kesememe problemi ruul.io ile çözülüyor:

```
Müşteri Ödemesi
      │
      ▼
┌─────────────┐
│   ruul.io   │  ← Müşteriye fatura keser
│   (Aracı)   │  ← Vergi/KDV işlemlerini halleder
└─────────────┘
      │
      ▼
Sana Ödeme (Sub-contractor olarak)
```

---

## 3. KULLANICI AKIŞI (USER FLOW)

### 3.1 Müşteri Perspektifi

```
┌────────────────────────────────────────────────────────────────────────────┐
│                        MÜŞTERİ YOLCULUĞU                                   │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ADIM 1: LANDING PAGE                                                      │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • Paket tanıtımı görür                                              │ │
│  │  • Fiyatı görür                                                      │ │
│  │  • "Hemen Başla" butonuna tıklar                                     │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  ADIM 2: DOMAIN CHECKER                                                    │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • İstediği domain'i yazar (örn: "meingeschaeft")                    │ │
│  │  • Uzantı seçer (.de, .com, .eu)                                     │ │
│  │  • "Kontrol Et" butonuna tıklar                                      │ │
│  │  • Sonuç: ✅ Müsait veya ❌ Alınmış                                  │ │
│  │  • Müsaitse "Bu Domain ile Devam Et" butonuna tıklar                 │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  ADIM 3: FORM & YÖNLENDIRME                                                │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • Email adresini girer                                              │ │
│  │  • İşletme adını girer (opsiyonel)                                   │ │
│  │  • "Ödemeye Geç" butonuna tıklar                                     │ │
│  │  • Backend'de sipariş oluşturulur (status: created)                  │ │
│  │  • ruul.io ödeme sayfasına yönlendirilir                             │ │
│  │    URL: ruul.space/payment/xxx?order_id=WB123ABC                     │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  ADIM 4: ÖDEME (ruul.io)                                                   │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • ruul.io ödeme formunu doldurur                                    │ │
│  │  • Kredi kartı / SEPA ile ödeme yapar                                │ │
│  │  • Ödeme başarılı → Bizim siteye geri yönlendirilir                  │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  ADIM 5: SİPARİŞ TAKİP                                                     │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • /order-status/WB123ABC sayfasına gelir                            │ │
│  │  • "Siparişiniz hazırlanıyor" mesajı görür                           │ │
│  │  • Progress bar ile durumu takip eder                                │ │
│  │  • Email ile de bilgilendirilir                                      │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  ADIM 6: TESLİMAT                                                          │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • "Siteniz hazır!" emaili alır                                      │ │
│  │  • Domain'ine girer, sitesini görür                                  │ │
│  │  • 🎉 Mutlu müşteri                                                  │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                                                            │
└────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Admin (Sen) Perspektifi

```
┌────────────────────────────────────────────────────────────────────────────┐
│                         ADMİN İŞ AKIŞI                                     │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  TRİGGER: Gmail'e ruul.io'dan "Ödeme Alındı" emaili gelir                 │
│                                    │                                       │
│                                    ▼                                       │
│  OTOMATİK: Google Apps Script                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • Email'i parse eder                                                │ │
│  │  • order_id'yi çıkarır                                               │ │
│  │  • Backend webhook'a POST atar                                       │ │
│  │  • Backend sipariş durumunu "payment_received" yapar                 │ │
│  │  • Sana admin notification emaili gider                              │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  MANUEL: Admin Panel'e giriş                                               │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • Yeni siparişi görürsün                                            │ │
│  │  • Müşteri bilgileri: email, domain, template tercihi                │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  MANUEL: Hostinger hPanel (Tahmini 5-10 dakika)                            │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  1. hPanel'e giriş yap                                               │ │
│  │  2. Domain satın al (müşterinin seçtiği domain)                      │ │
│  │  3. Hosting planına domain'i ekle                                    │ │
│  │  4. SSL sertifikası aktifleştir                                      │ │
│  │  5. FTP/File Manager ile template dosyalarını yükle                  │ │
│  │  6. Template'i müşteri bilgileriyle özelleştir                       │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                    │                                       │
│                                    ▼                                       │
│  MANUEL: Admin Panel'de Tamamla                                            │
│  ┌──────────────────────────────────────────────────────────────────────┐ │
│  │  • Durumu "completed" olarak işaretle                                │ │
│  │  • Sistem otomatik olarak müşteriye "Siteniz Hazır!" emaili atar     │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                                                            │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## 4. TEKNİK MİMARİ

### 4.1 Sistem Mimarisi Diyagramı

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              SİSTEM MİMARİSİ                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│   ┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐  │
│   │                 │         │                 │         │                 │  │
│   │   MÜŞTERİ       │────────▶│   FRONTEND      │────────▶│   BACKEND       │  │
│   │   (Browser)     │◀────────│   (Vercel)      │◀────────│   (Hostinger)   │  │
│   │                 │         │                 │         │                 │  │
│   └─────────────────┘         └─────────────────┘         └─────────────────┘  │
│                                      │                           │             │
│                                      │                           │             │
│   ┌─────────────────────────────────────────────────────────────────────────┐  │
│   │                         DETAYLI BAĞLANTILAR                             │  │
│   ├─────────────────────────────────────────────────────────────────────────┤  │
│   │                                                                         │  │
│   │   FRONTEND (SvelteKit + TailwindCSS)                                    │  │
│   │   URL: https://websitebuilder.vercel.app                                │  │
│   │   ├── Landing Page (/)                                                  │  │
│   │   ├── Domain Checker (/domain-check)                                    │  │
│   │   ├── Checkout (/checkout)                                              │  │
│   │   ├── Order Status (/order-status/[id])                                 │  │
│   │   └── Admin Panel (/admin/*) [Protected]                                │  │
│   │              │                                                          │  │
│   │              │ API Calls (fetch)                                        │  │
│   │              ▼                                                          │  │
│   │   BACKEND (PHP + MySQL)                                                 │  │
│   │   URL: https://api.yourdomain.com                                       │  │
│   │   ├── POST /api/check-domain                                            │  │
│   │   ├── POST /api/orders                                                  │  │
│   │   ├── GET  /api/orders/{id}                                             │  │
│   │   ├── POST /api/webhook/payment                                         │  │
│   │   └── [Admin endpoints]                                                 │  │
│   │              │                                                          │  │
│   │              │ SQL Queries                                              │  │
│   │              ▼                                                          │  │
│   │   DATABASE (MySQL)                                                      │  │
│   │   ├── orders                                                            │  │
│   │   ├── order_logs                                                        │  │
│   │   ├── templates                                                         │  │
│   │   └── webhook_logs                                                      │  │
│   │                                                                         │  │
│   └─────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│   ┌─────────────────────────────────────────────────────────────────────────┐  │
│   │                         EXTERNAL SERVİSLER                              │  │
│   ├─────────────────────────────────────────────────────────────────────────┤  │
│   │                                                                         │  │
│   │   ruul.io                         Google Apps Script                    │  │
│   │   ┌───────────────┐               ┌───────────────┐                     │  │
│   │   │ Ödeme İşleme  │               │ Email Parser  │                     │  │
│   │   │ Fatura Kesme  │──── Email ───▶│ Webhook       │                     │  │
│   │   │               │               │ Trigger       │                     │  │
│   │   └───────────────┘               └───────┬───────┘                     │  │
│   │          │                                │                             │  │
│   │          │ Redirect                       │ HTTP POST                   │  │
│   │          ▼                                ▼                             │  │
│   │   ┌───────────────┐               ┌───────────────┐                     │  │
│   │   │   Frontend    │               │   Backend     │                     │  │
│   │   │   /callback   │               │   /webhook    │                     │  │
│   │   └───────────────┘               └───────────────┘                     │  │
│   │                                                                         │  │
│   │   Hostinger (Manuel - MVP)                                              │  │
│   │   ┌───────────────┐                                                     │  │
│   │   │ hPanel        │  ◀──── Admin manuel giriş                           │  │
│   │   │ • Domain      │                                                     │  │
│   │   │ • Hosting     │                                                     │  │
│   │   │ • FTP         │                                                     │  │
│   │   └───────────────┘                                                     │  │
│   │                                                                         │  │
│   └─────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Veri Akışı (Data Flow)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              VERİ AKIŞI                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  1. SİPARİŞ OLUŞTURMA                                                           │
│  ════════════════════                                                           │
│                                                                                 │
│  [Müşteri]                                                                      │
│      │                                                                          │
│      │ Form Submit: { email, domain, businessName }                             │
│      ▼                                                                          │
│  [Frontend]                                                                     │
│      │                                                                          │
│      │ POST /api/orders                                                         │
│      │ Body: { customer_email, domain_name, customer_name }                     │
│      ▼                                                                          │
│  [Backend]                                                                      │
│      │                                                                          │
│      ├──▶ Generate unique order_id (WB + timestamp + random)                    │
│      ├──▶ INSERT into orders table                                              │
│      ├──▶ INSERT log: "Sipariş oluşturuldu"                                     │
│      │                                                                          │
│      │ Response: { order_id, ruul_payment_url }                                 │
│      ▼                                                                          │
│  [Frontend]                                                                     │
│      │                                                                          │
│      │ Redirect to: ruul.space/payment/xxx?order_id=WB123ABC                    │
│      ▼                                                                          │
│  [ruul.io]                                                                      │
│                                                                                 │
│                                                                                 │
│  2. ÖDEME SONRASI                                                               │
│  ════════════════                                                               │
│                                                                                 │
│  [ruul.io]                                                                      │
│      │                                                                          │
│      ├──▶ Email to: senin@gmail.com                                             │
│      │    Subject: "Payment received for Order WB123ABC"                        │
│      │                                                                          │
│      │ Redirect to: frontend/callback?order_id=WB123ABC&status=success          │
│      ▼                                                                          │
│  [Gmail]                                                                        │
│      │                                                                          │
│      │ Trigger: Google Apps Script                                              │
│      ▼                                                                          │
│  [Apps Script]                                                                  │
│      │                                                                          │
│      ├──▶ Parse email, extract order_id                                         │
│      │                                                                          │
│      │ POST /api/webhook/payment                                                │
│      │ Headers: { X-Webhook-Secret: "secret-key" }                              │
│      │ Body: { order_id, payment_status: "paid", raw_email }                    │
│      ▼                                                                          │
│  [Backend]                                                                      │
│      │                                                                          │
│      ├──▶ Validate webhook secret                                               │
│      ├──▶ UPDATE orders SET payment_status = 'paid'                             │
│      ├──▶ UPDATE orders SET order_status = 'payment_received'                   │
│      ├──▶ INSERT log: "Ödeme alındı"                                            │
│      ├──▶ Send admin notification email                                         │
│      │                                                                          │
│      │ Response: { success: true }                                              │
│      ▼                                                                          │
│  [Admin Email]                                                                  │
│      │                                                                          │
│      │ "Yeni sipariş: WB123ABC - example.de - customer@email.com"               │
│      ▼                                                                          │
│  [Admin]                                                                        │
│                                                                                 │
│                                                                                 │
│  3. MANUEL İŞLEM & TAMAMLAMA                                                    │
│  ═══════════════════════════                                                    │
│                                                                                 │
│  [Admin]                                                                        │
│      │                                                                          │
│      │ 1. hPanel'de domain satın al                                             │
│      │ 2. Hosting kur, SSL aktifleştir                                          │
│      │ 3. Template yükle ve özelleştir                                          │
│      │                                                                          │
│      │ Admin Panel: Mark as "completed"                                         │
│      ▼                                                                          │
│  [Frontend Admin]                                                               │
│      │                                                                          │
│      │ PATCH /api/admin/orders/WB123ABC                                         │
│      │ Body: { status: "completed" }                                            │
│      ▼                                                                          │
│  [Backend]                                                                      │
│      │                                                                          │
│      ├──▶ UPDATE orders SET order_status = 'completed'                          │
│      ├──▶ INSERT log: "Sipariş tamamlandı"                                      │
│      ├──▶ Send customer email: "Siteniz hazır!"                                 │
│      │                                                                          │
│      │ Response: { success: true }                                              │
│      ▼                                                                          │
│  [Müşteri Email]                                                                │
│                                                                                 │
│      "Merhaba! example.de adresindeki siteniz hazır. Hayırlı olsun!"            │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. TEKNOLOJİ STACK

### 5.1 Stack Özeti

```
┌─────────────────────────────────────────────────────────────────────┐
│                      TEKNOLOJİ STACK                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  FRONTEND                          BACKEND                          │
│  ─────────                         ───────                          │
│  SvelteKit 2.x                     PHP 8.1+                         │
│  TypeScript                        MySQL 8.0                        │
│  TailwindCSS 3.x                   PDO (Database)                   │
│  Vite                              Composer (Dependencies)          │
│                                                                     │
│  DEPLOYMENT                        ENTEGRASYONLAR                   │
│  ──────────                        ─────────────                    │
│  Vercel (Frontend)                 ruul.io (Ödeme)                  │
│  Hostinger (Backend + DB)          Google Apps Script (Email)       │
│                                    WHOIS API (Domain Check)         │
│                                    PHPMailer (Email Gönderimi)      │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 5.2 Neden Bu Teknolojiler?

#### Frontend: SvelteKit + TailwindCSS

```
SvelteKit Avantajları:
├── Compile-time framework → Küçük bundle size
├── Reactive by default → Daha az boilerplate
├── File-based routing → Hızlı geliştirme
├── SSR + SSG desteği → SEO dostu
└── Vercel ile native entegrasyon

TailwindCSS Avantajları:
├── Utility-first → Tutarlı tasarım
├── PurgeCSS → Sadece kullanılan CSS
├── Responsive design → Mobile-first
└── Öğrenme eğrisi düşük (senin için zaten bilinen)

Alternatifler (Neden Seçmedik):
├── React/Next.js → Daha kompleks, bundle büyük
├── Vue/Nuxt → İyi alternatif ama SvelteKit daha performanslı
└── Vanilla JS → Maintainability zorlaşır
```

#### Backend: PHP + MySQL

```
PHP Avantajları:
├── Hostinger native desteği → Deployment kolay
├── Shared hosting uyumlu → Maliyet düşük
├── Olgun ekosistem → Her şey için library var
├── Senin için bilinen teknoloji
└── MySQL ile native entegrasyon

MySQL Avantajları:
├── Hostinger'da ücretsiz
├── Güvenilir ve test edilmiş
├── phpMyAdmin ile kolay yönetim
└── Bu scale için fazlasıyla yeterli

Alternatifler (Neden Seçmedik):
├── Node.js → VPS gerektirir, maliyet artar
├── Python/Django → Hostinger desteği zayıf
├── PostgreSQL → Hostinger'da ek maliyet
└── Serverless → Bu use case için overkill
```

### 5.3 Dependency Liste

#### Frontend (package.json)

```json
{
  "dependencies": {
    "@sveltejs/kit": "^2.0.0",
    "svelte": "^4.0.0"
  },
  "devDependencies": {
    "@sveltejs/adapter-vercel": "^4.0.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0",
    "tailwindcss": "^3.4.0",
    "typescript": "^5.0.0",
    "vite": "^5.0.0"
  }
}
```

#### Backend (composer.json)

```json
{
  "require": {
    "php": ">=8.1",
    "phpmailer/phpmailer": "^6.8",
    "vlucas/phpdotenv": "^5.5"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

---

## 6. VERİTABANI TASARIMI

### 6.1 ER Diyagramı

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           DATABASE SCHEMA                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                              orders                                      │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │  PK  id                    INT AUTO_INCREMENT                           │   │
│  │  UK  order_id              VARCHAR(50)         "WB1ABC2DEF"             │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      customer_email        VARCHAR(255)        NOT NULL                 │   │
│  │      customer_name         VARCHAR(255)                                 │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      domain_name           VARCHAR(255)        "example.de"             │   │
│  │      domain_available      BOOLEAN             TRUE                     │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      package_type          ENUM                'starter'                │   │
│  │      template_config       JSON                {...}                    │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      payment_status        ENUM                'pending'|'paid'|...     │   │
│  │      payment_reference     VARCHAR(255)                                 │   │
│  │      payment_date          DATETIME                                     │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      order_status          ENUM                'created'|'payment_      │   │
│  │                                                received'|'completed'    │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      ruul_email_received_at DATETIME                                    │   │
│  │      domain_purchased_at    DATETIME                                    │   │
│  │      hosting_setup_at       DATETIME                                    │   │
│  │      completed_at           DATETIME                                    │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      admin_notes           TEXT                                         │   │
│  │      created_at            TIMESTAMP           DEFAULT NOW()            │   │
│  │      updated_at            TIMESTAMP           ON UPDATE NOW()          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                          │                                      │
│                                          │ 1:N                                  │
│                                          ▼                                      │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                            order_logs                                    │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │  PK  id                    INT AUTO_INCREMENT                           │   │
│  │  FK  order_id              VARCHAR(50)                                  │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      log_type              ENUM                'info'|'success'|        │   │
│  │                                                'warning'|'error'        │   │
│  │      message               TEXT                "Sipariş oluşturuldu"    │   │
│  │      details               JSON                {extra data}             │   │
│  │      created_at            TIMESTAMP           DEFAULT NOW()            │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                             templates                                    │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │  PK  id                    INT AUTO_INCREMENT                           │   │
│  │  UK  template_id           VARCHAR(50)         "business-starter"       │   │
│  │      ─────────────────────────────────────────────────────────          │   │
│  │      name                  VARCHAR(255)        "İş Başlangıç"           │   │
│  │      description           TEXT                                         │   │
│  │      thumbnail_url         VARCHAR(500)                                 │   │
│  │      default_config        JSON                {colors, fonts, ...}     │   │
│  │      template_path         VARCHAR(500)        "/templates/business/"   │   │
│  │      is_active             BOOLEAN             TRUE                     │   │
│  │      created_at            TIMESTAMP           DEFAULT NOW()            │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                           webhook_logs                                   │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │  PK  id                    INT AUTO_INCREMENT                           │   │
│  │      source                VARCHAR(50)         "google_apps_script"     │   │
│  │      payload               JSON                {raw webhook data}       │   │
│  │      processed             BOOLEAN             FALSE                    │   │
│  │      error_message         TEXT                                         │   │
│  │      created_at            TIMESTAMP           DEFAULT NOW()            │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                           admin_users                                    │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │  PK  id                    INT AUTO_INCREMENT                           │   │
│  │  UK  username              VARCHAR(100)                                 │   │
│  │  UK  email                 VARCHAR(255)                                 │   │
│  │      password_hash         VARCHAR(255)        bcrypt hash              │   │
│  │      is_active             BOOLEAN             TRUE                     │   │
│  │      last_login            DATETIME                                     │   │
│  │      created_at            TIMESTAMP           DEFAULT NOW()            │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 6.2 template_config JSON Yapısı

```json
{
  "template_id": "business-starter",
  "customization": {
    "colors": {
      "primary": "#3B82F6",
      "secondary": "#1E40AF",
      "accent": "#F59E0B",
      "background": "#FFFFFF",
      "text": "#1F2937"
    },
    "fonts": {
      "heading": "Inter",
      "body": "Inter"
    },
    "content": {
      "business_name": "ABC Şirketi",
      "tagline": "Kalite ve Güven",
      "phone": "+49 123 456789",
      "email": "info@example.de",
      "address": "Berlin, Germany"
    },
    "logo": {
      "url": "/uploads/logos/WB123ABC.png",
      "uploaded_at": "2025-01-15T10:30:00Z"
    },
    "sections": {
      "hero": { "enabled": true, "variant": "centered" },
      "services": { "enabled": true, "count": 3 },
      "about": { "enabled": true },
      "contact": { "enabled": true, "show_map": false }
    }
  }
}
```

### 6.3 Sipariş Durumları (State Machine)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         SİPARİŞ DURUM MAKİNESİ                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│                            ┌─────────────┐                                      │
│                            │   created   │  ← Sipariş oluşturuldu               │
│                            └──────┬──────┘                                      │
│                                   │                                             │
│                    Ödeme alındı   │                                             │
│                    (webhook)      │                                             │
│                                   ▼                                             │
│                         ┌─────────────────┐                                     │
│                         │payment_received │  ← ruul.io'dan onay geldi           │
│                         └────────┬────────┘                                     │
│                                  │                                              │
│                   Admin: Domain  │                                              │
│                   satın aldı     │                                              │
│                                  ▼                                              │
│                       ┌──────────────────┐                                      │
│                       │domain_purchased  │  ← Domain alındı                     │
│                       └────────┬─────────┘                                      │
│                                │                                                │
│                   Admin:       │                                                │
│                   Hosting kurdu│                                                │
│                                ▼                                                │
│                        ┌─────────────────┐                                      │
│                        │  hosting_setup  │  ← Hosting hazır                     │
│                        └────────┬────────┘                                      │
│                                 │                                               │
│                   Admin:        │                                               │
│                   Template      │                                               │
│                   yükledi       ▼                                               │
│                      ┌───────────────────┐                                      │
│                      │template_deployed  │  ← Site yüklendi                     │
│                      └─────────┬─────────┘                                      │
│                                │                                                │
│                   Admin:       │                                                │
│                   Tamamlandı   │                                                │
│                                ▼                                                │
│                         ┌─────────────┐                                         │
│                         │  completed  │  ← 🎉 Müşteriye email gider             │
│                         └─────────────┘                                         │
│                                                                                 │
│                                                                                 │
│  HATA DURUMU (herhangi bir adımdan geçilebilir)                                 │
│  ───────────────────────────────────────────────                                │
│                                                                                 │
│      [Herhangi bir durum] ────────▶ ┌──────────┐                                │
│                                     │  failed  │  ← Hata oluştu                 │
│                                     └──────────┘                                │
│                                                                                 │
│                                                                                 │
│  NOTLAR:                                                                        │
│  • MVP'de "domain_purchased", "hosting_setup", "template_deployed"              │
│    adımları manuel takip için var. İsterseniz direkt "completed"a              │
│    geçebilirsiniz.                                                              │
│  • Her durum değişikliği order_logs tablosuna kaydedilir.                       │
│  • "completed" olunca otomatik email gönderilir.                                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 7. API TASARIMI

### 7.1 API Endpoint Listesi

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              API ENDPOINTS                                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  PUBLIC ENDPOINTS (Authentication gerektirmez)                                  │
│  ═══════════════════════════════════════════════                                │
│                                                                                 │
│  POST   /api/check-domain                                                       │
│         Domain müsaitlik kontrolü                                               │
│         Request:  { "domain": "example", "tld": "de" }                          │
│         Response: { "available": true, "domain": "example.de" }                 │
│                                                                                 │
│  POST   /api/orders                                                             │
│         Yeni sipariş oluştur                                                    │
│         Request:  { "customer_email": "...", "domain_name": "...",              │
│                     "customer_name": "...", "package_type": "starter" }         │
│         Response: { "order_id": "WB123ABC", "payment_url": "ruul.space/..." }   │
│                                                                                 │
│  GET    /api/orders/{order_id}                                                  │
│         Sipariş durumu sorgula                                                  │
│         Response: { "order_id": "...", "status": "...", "domain": "..." }       │
│                                                                                 │
│  GET    /api/templates                                                          │
│         Aktif template listesi                                                  │
│         Response: { "templates": [...] }                                        │
│                                                                                 │
│                                                                                 │
│  WEBHOOK ENDPOINTS (Secret key ile korumalı)                                    │
│  ═══════════════════════════════════════════                                    │
│                                                                                 │
│  POST   /api/webhook/payment                                                    │
│         Google Apps Script'ten ödeme bildirimi                                  │
│         Headers: { "X-Webhook-Secret": "your-secret-key" }                      │
│         Request:  { "order_id": "...", "payment_status": "paid", ... }          │
│         Response: { "success": true }                                           │
│                                                                                 │
│                                                                                 │
│  ADMIN ENDPOINTS (Admin authentication gerektirir)                              │
│  ═════════════════════════════════════════════════                              │
│                                                                                 │
│  POST   /api/admin/login                                                        │
│         Admin girişi                                                            │
│         Request:  { "username": "...", "password": "..." }                      │
│         Response: { "token": "jwt-token", "expires_at": "..." }                 │
│                                                                                 │
│  GET    /api/admin/orders                                                       │
│         Tüm siparişleri listele (filtreleme destekli)                           │
│         Query:    ?status=payment_received&page=1&limit=20                      │
│         Response: { "orders": [...], "total": 45, "page": 1 }                   │
│                                                                                 │
│  GET    /api/admin/orders/{order_id}                                            │
│         Sipariş detayı + loglar                                                 │
│         Response: { "order": {...}, "logs": [...] }                             │
│                                                                                 │
│  PATCH  /api/admin/orders/{order_id}                                            │
│         Sipariş güncelle (durum, notlar)                                        │
│         Request:  { "status": "completed", "notes": "..." }                     │
│         Response: { "success": true, "order": {...} }                           │
│                                                                                 │
│  GET    /api/admin/stats                                                        │
│         Dashboard istatistikleri                                                │
│         Response: { "pending": 5, "completed": 120, "revenue_mtd": 2500 }       │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 7.2 Domain Check API Detayı

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  POST /api/check-domain                                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  AMAÇ: Müşterinin istediği domain'in satın alınabilir olup olmadığını kontrol   │
│                                                                                 │
│  REQUEST:                                                                       │
│  ────────                                                                       │
│  {                                                                              │
│    "domain": "meingeschaeft",     // Domain adı (uzantısız)                     │
│    "tld": "de"                    // Uzantı: de, com, eu, net, org              │
│  }                                                                              │
│                                                                                 │
│  RESPONSE (Müsait):                                                             │
│  ──────────────────                                                             │
│  {                                                                              │
│    "success": true,                                                             │
│    "available": true,                                                           │
│    "domain": "meingeschaeft.de",                                                │
│    "message": "Bu domain müsait!"                                               │
│  }                                                                              │
│                                                                                 │
│  RESPONSE (Alınmış):                                                            │
│  ───────────────────                                                            │
│  {                                                                              │
│    "success": true,                                                             │
│    "available": false,                                                          │
│    "domain": "meingeschaeft.de",                                                │
│    "message": "Bu domain zaten alınmış.",                                       │
│    "suggestions": [                 // Opsiyonel: Alternatif öneriler           │
│      "meingeschaeft.com",                                                       │
│      "meingeschaeft.eu",                                                        │
│      "mein-geschaeft.de"                                                        │
│    ]                                                                            │
│  }                                                                              │
│                                                                                 │
│  RESPONSE (Hata):                                                               │
│  ────────────────                                                               │
│  {                                                                              │
│    "success": false,                                                            │
│    "error": "invalid_domain",                                                   │
│    "message": "Geçersiz domain adı. Sadece harf, rakam ve tire kullanın."       │
│  }                                                                              │
│                                                                                 │
│  TEKNİK DETAY:                                                                  │
│  ─────────────                                                                  │
│  • WHOIS lookup kullanılacak (php-whois veya external API)                      │
│  • Rate limiting: IP başına 10 request/dakika                                   │
│  • Önbellekleme: Aynı domain 5 dakika cache'lenir                               │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 7.3 Sipariş Oluşturma API Detayı

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  POST /api/orders                                                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  AMAÇ: Yeni sipariş kaydı oluştur ve ruul.io ödeme URL'i döndür                 │
│                                                                                 │
│  REQUEST:                                                                       │
│  ────────                                                                       │
│  {                                                                              │
│    "customer_email": "kunde@example.com",      // Zorunlu                       │
│    "customer_name": "Max Mustermann",          // Opsiyonel                     │
│    "domain_name": "meingeschaeft.de",          // Zorunlu                       │
│    "package_type": "starter",                  // starter|professional|business │
│    "template_config": {                        // Opsiyonel                     │
│      "template_id": "business-starter",                                         │
│      "colors": {                                                                │
│        "primary": "#3B82F6"                                                     │
│      },                                                                         │
│      "business_name": "Mein Geschäft"                                           │
│    }                                                                            │
│  }                                                                              │
│                                                                                 │
│  BACKEND İŞLEMLER:                                                              │
│  ─────────────────                                                              │
│  1. Email format validasyonu                                                    │
│  2. Domain format validasyonu                                                   │
│  3. Unique order_id oluştur (WB + timestamp + random)                           │
│  4. Database'e INSERT                                                           │
│  5. Log kaydı oluştur                                                           │
│  6. ruul.io payment URL'i oluştur                                               │
│                                                                                 │
│  RESPONSE:                                                                      │
│  ─────────                                                                      │
│  {                                                                              │
│    "success": true,                                                             │
│    "order": {                                                                   │
│      "order_id": "WB1A2B3C4D",                                                  │
│      "domain_name": "meingeschaeft.de",                                         │
│      "customer_email": "kunde@example.com",                                     │
│      "status": "created",                                                       │
│      "created_at": "2025-01-15T10:30:00Z"                                       │
│    },                                                                           │
│    "payment": {                                                                 │
│      "url": "https://ruul.space/payment/cs_live_xxx?order_id=WB1A2B3C4D",       │
│      "amount": 299,                                                             │
│      "currency": "EUR"                                                          │
│    }                                                                            │
│  }                                                                              │
│                                                                                 │
│  HATA DURUMLARI:                                                                │
│  ───────────────                                                                │
│  400: { "error": "validation_error", "fields": { "email": "Geçersiz email" } }  │
│  409: { "error": "duplicate_order", "message": "Bu domain için sipariş var" }   │
│  500: { "error": "server_error", "message": "Bir hata oluştu" }                 │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 8. FRONTEND YAPISI

### 8.1 Sayfa ve Route Yapısı

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         SVELTEKIT ROUTE YAPISI                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  src/                                                                           │
│  ├── routes/                                                                    │
│  │   ├── +layout.svelte              ← Ana layout (header, footer)              │
│  │   ├── +page.svelte                ← Landing page (/)                         │
│  │   │                                                                          │
│  │   ├── domain-check/                                                          │
│  │   │   └── +page.svelte            ← Domain kontrol sayfası                   │
│  │   │                                                                          │
│  │   ├── checkout/                                                              │
│  │   │   └── +page.svelte            ← Sipariş formu + ruul.io redirect         │
│  │   │                                                                          │
│  │   ├── callback/                                                              │
│  │   │   └── +page.svelte            ← ruul.io'dan dönüş sayfası                │
│  │   │                                                                          │
│  │   ├── order-status/                                                          │
│  │   │   └── [orderId]/                                                         │
│  │   │       └── +page.svelte        ← Sipariş takip sayfası                    │
│  │   │                                                                          │
│  │   └── admin/                       ← Admin panel (protected)                 │
│  │       ├── +layout.svelte          ← Admin layout + auth check                │
│  │       ├── +page.svelte            ← Dashboard                                │
│  │       ├── login/                                                             │
│  │       │   └── +page.svelte        ← Admin login                              │
│  │       ├── orders/                                                            │
│  │       │   ├── +page.svelte        ← Sipariş listesi                          │
│  │       │   └── [orderId]/                                                     │
│  │       │       └── +page.svelte    ← Sipariş detay + yönetim                  │
│  │       └── settings/                                                          │
│  │           └── +page.svelte        ← Ayarlar                                  │
│  │                                                                              │
│  ├── lib/                                                                       │
│  │   ├── api.ts                      ← API client fonksiyonları                 │
│  │   ├── stores/                                                                │
│  │   │   ├── order.ts                ← Sipariş state yönetimi                   │
│  │   │   └── auth.ts                 ← Admin auth state                         │
│  │   ├── components/                                                            │
│  │   │   ├── DomainChecker.svelte    ← Domain kontrol komponenti                │
│  │   │   ├── OrderForm.svelte        ← Sipariş formu                            │
│  │   │   ├── OrderStatus.svelte      ← Durum göstergesi                         │
│  │   │   ├── PricingCard.svelte      ← Fiyat kartı                              │
│  │   │   └── admin/                                                             │
│  │   │       ├── OrderTable.svelte   ← Sipariş tablosu                          │
│  │   │       ├── OrderDetail.svelte  ← Sipariş detay                            │
│  │   │       └── StatsCard.svelte    ← İstatistik kartları                      │
│  │   └── utils/                                                                 │
│  │       ├── validation.ts           ← Form validasyonu                         │
│  │       └── formatters.ts           ← Tarih, para formatı                      │
│  │                                                                              │
│  └── app.css                         ← Global styles + Tailwind                 │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 8.2 Sayfa Detayları

#### Landing Page (/)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           LANDING PAGE                                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  HEADER                                                                  │   │
│  │  Logo                                          [Admin Giriş] (gizli)     │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  HERO SECTION                                                            │   │
│  │                                                                          │   │
│  │         Profesyonel Web Siteniz                                          │   │
│  │            Hazır ve Çalışır                                              │   │
│  │                                                                          │   │
│  │   Domain, hosting ve özel tasarım tek pakette.                           │   │
│  │   Teknik bilgi gerektirmez. Biz hallederiz.                              │   │
│  │                                                                          │   │
│  │              [ Hemen Başlayın →]                                         │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  PAKET İÇERİĞİ                                                           │   │
│  │                                                                          │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐                  │   │
│  │  │ 🌐       │  │ 🖥️       │  │ 🎨       │  │ 🔧       │                  │   │
│  │  │ Domain   │  │ Hosting  │  │ Tasarım  │  │ Bakım    │                  │   │
│  │  │ 1 Yıllık │  │ Hızlı    │  │ Modern   │  │ 7/24     │                  │   │
│  │  └──────────┘  └──────────┘  └──────────┘  └──────────┘                  │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  FİYATLANDIRMA                                                           │   │
│  │                                                                          │   │
│  │  ┌────────────────────────────────────────────────────────────────┐      │   │
│  │  │                     STARTER PAKET                              │      │   │
│  │  │                                                                │      │   │
│  │  │                        299€/yıl                                │      │   │
│  │  │                                                                │      │   │
│  │  │  ✓ .de veya .com domain                                        │      │   │
│  │  │  ✓ Hızlı SSD hosting                                           │      │   │
│  │  │  ✓ SSL sertifikası                                             │      │   │
│  │  │  ✓ Profesyonel tasarım                                         │      │   │
│  │  │  ✓ Mobil uyumlu                                                │      │   │
│  │  │  ✓ 1 yıl teknik destek                                         │      │   │
│  │  │                                                                │      │   │
│  │  │              [ Domain Seçin →]                                 │      │   │
│  │  │                                                                │      │   │
│  │  └────────────────────────────────────────────────────────────────┘      │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  NASIL ÇALIŞIR?                                                          │   │
│  │                                                                          │   │
│  │  1️⃣ Domain Seçin    2️⃣ Ödeme Yapın    3️⃣ Siteniz Hazır               │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  FOOTER                                                                  │   │
│  │  © 2025 | İletişim | Impressum | Datenschutz                             │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

#### Domain Checker (/domain-check)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         DOMAIN CHECKER SAYFASI                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │              Hayalinizdeki Domain'i Kontrol Edin                         │   │
│  │                                                                          │   │
│  │  ┌────────────────────────────────────┬─────────┬──────────────┐         │   │
│  │  │  meingeschaeft                     │  .de ▼  │  Kontrol Et  │         │   │
│  │  └────────────────────────────────────┴─────────┴──────────────┘         │   │
│  │                                                                          │   │
│  │                         🔄 Kontrol ediliyor...                           │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  SONUÇ (Müsait):                                                                │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │     ✅  meingeschaeft.de MÜSAİT!                                         │   │
│  │                                                                          │   │
│  │         Bu domain sizin olabilir.                                        │   │
│  │                                                                          │   │
│  │              [ Bu Domain ile Devam Et →]                                 │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  SONUÇ (Alınmış):                                                               │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │     ❌  meingeschaeft.de ALINMIŞ                                         │   │
│  │                                                                          │   │
│  │         Alternatif öneriler:                                             │   │
│  │         ○ meingeschaeft.com      [Kontrol Et]                            │   │
│  │         ○ meingeschaeft.eu       [Kontrol Et]                            │   │
│  │         ○ mein-geschaeft.de      [Kontrol Et]                            │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

#### Order Status (/order-status/[orderId])

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                        SİPARİŞ TAKİP SAYFASI                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │              Sipariş: WB1A2B3C4D                                          │   │
│  │              Domain: meingeschaeft.de                                    │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  PROGRESS BAR:                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │  ●━━━━━━━━●━━━━━━━━●━━━━━━━━○━━━━━━━━○                                    │   │
│  │  Sipariş    Ödeme     Domain   Kurulum   Tamamlandı                      │   │
│  │  Alındı     Onaylandı  Hazır                                             │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  MEVCUT DURUM:                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │     🔄 Siteniz Hazırlanıyor                                              │   │
│  │                                                                          │   │
│  │     Domain adınız satın alınıyor ve hosting                              │   │
│  │     ortamınız oluşturuluyor. Bu işlem genellikle                         │   │
│  │     birkaç saat sürer.                                                   │   │
│  │                                                                          │   │
│  │     Tamamlandığında email ile bilgilendirileceksiniz.                    │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  TAMAMLANDIĞINDA:                                                               │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                          │   │
│  │     🎉 Siteniz Hazır!                                                    │   │
│  │                                                                          │   │
│  │     Tebrikler! Web siteniz artık yayında.                                │   │
│  │                                                                          │   │
│  │              [ meingeschaeft.de'yi Ziyaret Et →]                         │   │
│  │                                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 9. ENTEGRASYONLAR

### 9.1 ruul.io Entegrasyonu

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          ruul.io ENTEGRASYONU                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  GENEL BAKIŞ                                                                    │
│  ────────────                                                                   │
│  ruul.io, Almanya'da şirket olmadan fatura kesmeyi sağlayan bir platform.       │
│  Sub-contractor olarak tanımlanan ürünler satılır, ruul.io fatura keser         │
│  ve komisyon düşerek ödemeyi yapar.                                             │
│                                                                                 │
│                                                                                 │
│  ÖDEME AKIŞI                                                                    │
│  ───────────                                                                    │
│                                                                                 │
│  1. Sipariş oluşturulunca, payment URL oluştur:                                 │
│                                                                                 │
│     BASE URL: https://ruul.space/payment/cs_live_xxx                            │
│     PARAMS:   ?order_id=WB123ABC                                                │
│     FULL URL: https://ruul.space/payment/cs_live_xxx?order_id=WB123ABC          │
│                                                                                 │
│  2. Müşteri bu URL'e yönlendirilir                                              │
│                                                                                 │
│  3. Ödeme sonrası, ruul.io müşteriyi geri yönlendirir:                          │
│     → https://yoursite.com/callback?order_id=WB123ABC&status=success            │
│                                                                                 │
│  4. Aynı zamanda sana email gelir (bu email Apps Script'i tetikler)             │
│                                                                                 │
│                                                                                 │
│  KONFIGÜRASYON GEREKSİNİMLERİ                                                   │
│  ────────────────────────────                                                   │
│                                                                                 │
│  ruul.io'da yapılması gerekenler:                                               │
│                                                                                 │
│  □ Ürün tanımı: "Website Starter Paket - 299€"                                  │
│  □ Ürün açıklaması: Domain + Hosting + Website + 1 Yıl Bakım                    │
│  □ Redirect URL'i ayarla (varsa): https://yoursite.com/callback                 │
│  □ Email bildirimlerinin aktif olduğundan emin ol                               │
│                                                                                 │
│                                                                                 │
│  ÖNEMLİ NOTLAR                                                                  │
│  ─────────────                                                                  │
│                                                                                 │
│  • ruul.io'nun direkt webhook desteği olmadığından, email-based trigger         │
│    kullanıyoruz.                                                                │
│  • Ödeme kesin onayı için email'i beklemek gerekiyor.                           │
│  • Callback sayfası sadece "Siparişiniz işleniyor" gösterir, kesin              │
│    onay email-webhook sonrası gelir.                                            │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 9.2 Google Apps Script Entegrasyonu

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     GOOGLE APPS SCRIPT ENTEGRASYONU                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  AMAÇ                                                                           │
│  ────                                                                           │
│  Gmail'e gelen ruul.io ödeme bildirim emaillerini yakala ve backend             │
│  webhook'a POST at.                                                             │
│                                                                                 │
│                                                                                 │
│  AKIŞ                                                                           │
│  ────                                                                           │
│                                                                                 │
│  [ruul.io] ──email──▶ [Gmail] ──trigger──▶ [Apps Script] ──POST──▶ [Backend]    │
│                                                                                 │
│                                                                                 │
│  SCRIPT YAPISI                                                                  │
│  ─────────────                                                                  │
│                                                                                 │
│  // 1. Time-based trigger (her 1-5 dakikada bir çalışır)                        │
│  function checkForPaymentEmails() {                                             │
│    // Son 10 dakikadaki ruul.io emaillerini ara                                 │
│    const threads = GmailApp.search(                                             │
│      'from:ruul.io subject:"payment" newer_than:10m is:unread'                  │
│    );                                                                           │
│                                                                                 │
│    threads.forEach(thread => {                                                  │
│      const messages = thread.getMessages();                                     │
│      messages.forEach(message => {                                              │
│        processPaymentEmail(message);                                            │
│        message.markRead();                                                      │
│      });                                                                        │
│    });                                                                          │
│  }                                                                              │
│                                                                                 │
│  // 2. Email'i parse et ve webhook'a gönder                                     │
│  function processPaymentEmail(message) {                                        │
│    const body = message.getPlainBody();                                         │
│    const subject = message.getSubject();                                        │
│                                                                                 │
│    // order_id'yi çıkar (email içeriğine göre regex ayarlanacak)                │
│    const orderIdMatch = body.match(/order_id[=:]\s*(\w+)/i);                    │
│    if (!orderIdMatch) return;                                                   │
│                                                                                 │
│    const orderId = orderIdMatch[1];                                             │
│                                                                                 │
│    // Backend'e POST at                                                         │
│    const response = UrlFetchApp.fetch(                                          │
│      'https://api.yourdomain.com/api/webhook/payment',                          │
│      {                                                                          │
│        method: 'POST',                                                          │
│        contentType: 'application/json',                                         │
│        headers: {                                                               │
│          'X-Webhook-Secret': 'your-secret-key'                                  │
│        },                                                                        │
│        payload: JSON.stringify({                                                │
│          order_id: orderId,                                                     │
│          payment_status: 'paid',                                                │
│          email_subject: subject,                                                │
│          email_date: message.getDate().toISOString(),                           │
│          raw_body: body.substring(0, 1000)  // İlk 1000 karakter                │
│        })                                                                       │
│      }                                                                          │
│    );                                                                           │
│                                                                                 │
│    Logger.log('Webhook response: ' + response.getContentText());                │
│  }                                                                              │
│                                                                                 │
│                                                                                 │
│  KURULUM ADIMLARI                                                               │
│  ────────────────                                                               │
│                                                                                 │
│  1. Google Apps Script'e git (script.google.com)                                │
│  2. Yeni proje oluştur                                                          │
│  3. Yukarıdaki kodu yapıştır                                                    │
│  4. Triggers > Add Trigger:                                                     │
│     - Function: checkForPaymentEmails                                           │
│     - Event source: Time-driven                                                 │
│     - Type: Minutes timer                                                       │
│     - Interval: Every 1 minute (veya 5 minute)                                  │
│  5. Authorize (Gmail erişimi için izin ver)                                     │
│                                                                                 │
│                                                                                 │
│  DEBUG İPUÇLARI                                                                 │
│  ──────────────                                                                 │
│                                                                                 │
│  • Apps Script'te Executions sekmesinden çalışma loglarını gör                  │
│  • Test için: manuel olarak checkForPaymentEmails() çalıştır                    │
│  • Email gelmiyor gibi görünüyorsa: search query'yi kontrol et                  │
│  • Backend'e ulaşamıyorsa: CORS ve webhook secret kontrol et                    │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 9.3 Domain Availability Check

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     DOMAIN AVAILABILITY CHECK                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  SEÇENEKLER                                                                     │
│  ──────────                                                                     │
│                                                                                 │
│  1. WHOIS Lookup (Ücretsiz, yavaş)                                              │
│     ├── php-whois library kullan                                                │
│     ├── Avantaj: Ücretsiz                                                       │
│     └── Dezavantaj: Yavaş, rate limit sorunu olabilir                           │
│                                                                                 │
│  2. External API (Hızlı, ücretli)                                               │
│     ├── Namecheap API                                                           │
│     ├── GoDaddy API                                                             │
│     ├── Domainr API                                                             │
│     └── Whoisxml API                                                            │
│                                                                                 │
│  3. Hostinger Affiliate/Reseller (Eğer varsa)                                   │
│     └── Direkt Hostinger üzerinden kontrol                                      │
│                                                                                 │
│                                                                                 │
│  ÖNERİ: MVP için WHOIS + Cache                                                  │
│  ───────────────────────────────                                                │
│                                                                                 │
│  class DomainChecker {                                                          │
│      private $cache;  // Redis veya file cache                                  │
│      private $cacheTTL = 300;  // 5 dakika                                      │
│                                                                                 │
│      public function check(string $domain): array {                             │
│          // 1. Cache kontrol                                                    │
│          $cached = $this->cache->get("domain:$domain");                         │
│          if ($cached) return $cached;                                           │
│                                                                                 │
│          // 2. WHOIS lookup                                                     │
│          $whois = new Whois();                                                  │
│          $result = $whois->lookup($domain);                                     │
│                                                                                 │
│          // 3. Parse result                                                     │
│          $available = $this->parseAvailability($result);                        │
│                                                                                 │
│          // 4. Cache'le                                                         │
│          $response = [                                                          │
│              'domain' => $domain,                                               │
│              'available' => $available,                                         │
│              'checked_at' => time()                                             │
│          ];                                                                     │
│          $this->cache->set("domain:$domain", $response, $this->cacheTTL);       │
│                                                                                 │
│          return $response;                                                      │
│      }                                                                          │
│  }                                                                              │
│                                                                                 │
│                                                                                 │
│  DESTEKLENEN UZANTILAR (MVP)                                                    │
│  ──────────────────────────                                                     │
│                                                                                 │
│  .de   → Almanya (primary)                                                      │
│  .com  → Uluslararası                                                           │
│  .eu   → Avrupa                                                                 │
│  .net  → Network                                                                │
│  .org  → Organizasyon                                                           │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 10. GÜVENLİK ÖNLEMLERİ

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          GÜVENLİK ÖNLEMLERİ                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  1. API GÜVENLİĞİ                                                               │
│  ─────────────────                                                              │
│                                                                                 │
│  □ CORS: Sadece izinli originler (Vercel frontend URL'i)                        │
│  □ Rate Limiting: IP başına request limiti                                      │
│  □ Input Validation: Tüm inputlar sanitize edilmeli                             │
│  □ SQL Injection: PDO prepared statements kullan                                │
│  □ XSS: Output encoding                                                         │
│                                                                                 │
│                                                                                 │
│  2. WEBHOOK GÜVENLİĞİ                                                           │
│  ─────────────────────                                                          │
│                                                                                 │
│  □ Secret Key: X-Webhook-Secret header ile doğrulama                            │
│  □ IP Whitelist: Google Apps Script IP'leri (opsiyonel)                         │
│  □ Replay Protection: İşlenmiş webhook'ları tekrar işleme                       │
│  □ Logging: Tüm webhook'ları logla (debug için)                                 │
│                                                                                 │
│  // Webhook doğrulama örneği                                                    │
│  function validateWebhook($request) {                                           │
│      $secret = $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '';                         │
│      if ($secret !== getenv('WEBHOOK_SECRET')) {                                │
│          http_response_code(401);                                               │
│          exit('Unauthorized');                                                  │
│      }                                                                          │
│  }                                                                              │
│                                                                                 │
│                                                                                 │
│  3. ADMIN PANEL GÜVENLİĞİ                                                       │
│  ─────────────────────────                                                      │
│                                                                                 │
│  □ Strong Password: Minimum 12 karakter, mixed case, numbers, symbols           │
│  □ JWT Token: Short expiry (1-4 saat)                                           │
│  □ HTTPS Only: Secure cookie flag                                               │
│  □ Session Timeout: Inaktivitede otomatik logout                                │
│  □ Login Attempts: 5 başarısız denemede 15 dk kilitle                           │
│                                                                                 │
│                                                                                 │
│  4. VERİ GÜVENLİĞİ                                                              │
│  ─────────────────                                                              │
│                                                                                 │
│  □ Müşteri emaillerini hashle (arama için ayrı index)                           │
│  □ Hassas logları 30 gün sonra sil                                              │
│  □ Database backup: Günlük otomatik backup                                      │
│  □ GDPR: Müşteri veri silme endpoint'i (ileride)                                │
│                                                                                 │
│                                                                                 │
│  5. HOSTINGER SPESİFİK                                                          │
│  ─────────────────────                                                          │
│                                                                                 │
│  □ SSL: Let's Encrypt aktifleştir                                               │
│  □ PHP Version: 8.1+ kullan                                                     │
│  □ File Permissions: 644 files, 755 directories                                 │
│  □ .htaccess: Sensitive dosyaları koru                                          │
│                                                                                 │
│  # .htaccess örneği                                                             │
│  <FilesMatch "\.(env|config\.php|sql)$">                                        │
│      Order allow,deny                                                           │
│      Deny from all                                                              │
│  </FilesMatch>                                                                  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 11. DEPLOYMENT STRATEJİSİ

### 11.1 Ortamlar

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         DEPLOYMENT ORTAMLARI                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  DEVELOPMENT (Local)                                                            │
│  ────────────────────                                                           │
│  Frontend: http://localhost:5173 (SvelteKit dev server)                         │
│  Backend:  http://localhost:8000 (PHP built-in server)                          │
│  Database: Local MySQL veya Docker                                              │
│                                                                                 │
│  STAGING (Opsiyonel - MVP'de atlayabilirsin)                                    │
│  ────────────────────────────────────────────                                   │
│  Frontend: https://staging-websitebuilder.vercel.app                            │
│  Backend:  https://staging-api.yourdomain.com                                   │
│  Database: Ayrı staging DB                                                      │
│                                                                                 │
│  PRODUCTION                                                                     │
│  ──────────                                                                     │
│  Frontend: https://websitebuilder.de (veya Vercel subdomain)                    │
│  Backend:  https://api.websitebuilder.de (Hostinger)                            │
│  Database: Hostinger MySQL                                                      │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 11.2 Frontend Deployment (Vercel)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     VERCEL DEPLOYMENT                                           │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ADIMLAR                                                                        │
│  ───────                                                                        │
│                                                                                 │
│  1. GitHub'a push et                                                            │
│     git push origin main                                                        │
│                                                                                 │
│  2. Vercel'de import et                                                         │
│     - vercel.com > New Project > Import Git Repository                          │
│     - Framework: SvelteKit (auto-detect)                                        │
│                                                                                 │
│  3. Environment Variables ayarla                                                │
│     PUBLIC_API_URL = https://api.yourdomain.com                                 │
│                                                                                 │
│  4. Deploy                                                                      │
│     - Her push'ta otomatik deploy                                               │
│     - Preview deployments for PRs                                               │
│                                                                                 │
│                                                                                 │
│  DOSYA: vercel.json (opsiyonel)                                                 │
│  ─────────────────────────────                                                  │
│  {                                                                              │
│    "framework": "sveltekit"                                                     │
│  }                                                                              │
│                                                                                 │
│                                                                                 │
│  DOSYA: svelte.config.js                                                        │
│  ───────────────────────                                                        │
│  import adapter from '@sveltejs/adapter-vercel';                                │
│                                                                                 │
│  export default {                                                               │
│    kit: {                                                                       │
│      adapter: adapter()                                                         │
│    }                                                                            │
│  };                                                                             │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 11.3 Backend Deployment (Hostinger)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                    HOSTINGER DEPLOYMENT                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ADIMLAR                                                                        │
│  ───────                                                                        │
│                                                                                 │
│  1. Hostinger'da subdomain oluştur                                              │
│     api.yourdomain.com → public_html/api/                                       │
│                                                                                 │
│  2. Dosya yapısını yükle (FTP veya File Manager)                                │
│                                                                                 │
│     public_html/api/                                                            │
│     ├── .htaccess              ← URL rewriting + security                       │
│     ├── index.php              ← Entry point                                    │
│     ├── api/                   ← API endpoints                                  │
│     │   ├── check-domain.php                                                    │
│     │   ├── orders.php                                                          │
│     │   └── webhook/                                                            │
│     │       └── payment.php                                                     │
│     ├── config/                                                                 │
│     │   ├── config.php         ← Production config                              │
│     │   └── Database.php                                                        │
│     ├── services/                                                               │
│     │   └── OrderService.php                                                    │
│     └── vendor/                ← Composer dependencies                          │
│                                                                                 │
│  3. MySQL database oluştur                                                      │
│     - hPanel > Databases > Create new                                           │
│     - Schema'yı import et                                                       │
│                                                                                 │
│  4. Config dosyasını düzenle                                                    │
│     - Database credentials                                                      │
│     - Webhook secret                                                            │
│     - Frontend URL (CORS için)                                                  │
│                                                                                 │
│  5. SSL aktifleştir                                                             │
│     - hPanel > SSL > Force HTTPS                                                │
│                                                                                 │
│                                                                                 │
│  .htaccess                                                                      │
│  ─────────                                                                      │
│  RewriteEngine On                                                               │
│  RewriteBase /                                                                  │
│                                                                                 │
│  # Force HTTPS                                                                  │
│  RewriteCond %{HTTPS} off                                                       │
│  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]                │
│                                                                                 │
│  # API routing                                                                  │
│  RewriteCond %{REQUEST_FILENAME} !-f                                            │
│  RewriteCond %{REQUEST_FILENAME} !-d                                            │
│  RewriteRule ^api/(.*)$ api/index.php?route=$1 [QSA,L]                          │
│                                                                                 │
│  # Security                                                                     │
│  <FilesMatch "\.(env|config\.php|sql|log)$">                                    │
│      Order allow,deny                                                           │
│      Deny from all                                                              │
│  </FilesMatch>                                                                  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 12. GELİŞTİRME YOL HARİTASI

### 12.1 Fazlar

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          GELİŞTİRME FAZLARI                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ═══════════════════════════════════════════════════════════════════════════    │
│  FAZ 1: MVP (Hafta 1-2)                                                         │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                                                                                 │
│  □ Database schema oluştur ve test et                                           │
│  □ PHP backend temel yapısı                                                     │
│     □ Database class                                                            │
│     □ OrderService                                                              │
│     □ CORS middleware                                                           │
│  □ API Endpoints                                                                │
│     □ POST /api/check-domain (basit WHOIS)                                      │
│     □ POST /api/orders                                                          │
│     □ GET  /api/orders/{id}                                                     │
│     □ POST /api/webhook/payment                                                 │
│  □ SvelteKit frontend                                                           │
│     □ Landing page                                                              │
│     □ Domain checker                                                            │
│     □ Checkout → ruul.io redirect                                               │
│     □ Order status page                                                         │
│  □ Google Apps Script                                                           │
│     □ Email parser                                                              │
│     □ Webhook trigger                                                           │
│  □ Deployment                                                                   │
│     □ Vercel (frontend)                                                         │
│     □ Hostinger (backend)                                                       │
│  □ Test: Uçtan uca bir sipariş akışı                                            │
│                                                                                 │
│  ═══════════════════════════════════════════════════════════════════════════    │
│  FAZ 2: ADMIN PANEL (Hafta 3)                                                   │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                                                                                 │
│  □ Admin authentication                                                         │
│     □ Login page                                                                │
│     □ JWT token sistemi                                                         │
│  □ Admin dashboard                                                              │
│     □ Sipariş listesi                                                           │
│     □ Filtreleme (status, tarih)                                                │
│     □ Sipariş detay sayfası                                                     │
│     □ Durum güncelleme                                                          │
│  □ Email bildirimleri                                                           │
│     □ Admin: Yeni sipariş bildirimi                                             │
│     □ Müşteri: Sipariş tamamlandı bildirimi                                     │
│                                                                                 │
│  ═══════════════════════════════════════════════════════════════════════════    │
│  FAZ 3: İYİLEŞTİRMELER (Hafta 4+)                                               │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                                                                                 │
│  □ Template seçimi (müşteri farklı template seçebilsin)                         │
│  □ Temel customization (renk, logo upload)                                      │
│  □ Daha iyi domain checker (external API)                                       │
│  □ Admin: Toplu işlemler                                                        │
│  □ Admin: İstatistikler ve raporlar                                             │
│  □ Müşteri: Sipariş geçmişi (email ile giriş)                                   │
│                                                                                 │
│  ═══════════════════════════════════════════════════════════════════════════    │
│  FAZ 4: OTOMASYON (Gelecek - Opsiyonel)                                         │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                                                                                 │
│  □ Puppeteer ile Hostinger otomasyonu                                           │
│  □ Veya: Cloudflare + VPS'e geçiş (tam API kontrolü)                            │
│  □ Otomatik template deployment                                                 │
│  □ Müşteri self-service portal                                                  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 12.2 Sprint 1 Detaylı Task List

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                    SPRINT 1: MVP BACKEND (3-4 gün)                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  GÜN 1: Database & Temel Yapı                                                   │
│  ─────────────────────────────                                                  │
│  [2 saat] □ MySQL database oluştur (Hostinger veya local)                       │
│  [1 saat] □ Schema'yı çalıştır, test verisi ekle                                │
│  [2 saat] □ PHP project structure oluştur                                       │
│  [1 saat] □ Database.php ve config.php tamamla                                  │
│  [2 saat] □ OrderService.php tamamla                                            │
│                                                                                 │
│  GÜN 2: API Endpoints                                                           │
│  ────────────────────                                                           │
│  [2 saat] □ index.php (router) oluştur                                          │
│  [2 saat] □ POST /api/check-domain endpoint                                     │
│  [2 saat] □ POST /api/orders endpoint                                           │
│  [1 saat] □ GET /api/orders/{id} endpoint                                       │
│  [1 saat] □ CORS middleware                                                     │
│                                                                                 │
│  GÜN 3: Webhook & Email                                                         │
│  ──────────────────────                                                         │
│  [2 saat] □ POST /api/webhook/payment endpoint                                  │
│  [3 saat] □ Google Apps Script yazımı ve test                                   │
│  [2 saat] □ PHPMailer kurulumu ve email gönderimi                               │
│  [1 saat] □ Postman/curl ile tüm endpoint'leri test et                          │
│                                                                                 │
│                                                                                 │
│                    SPRINT 2: MVP FRONTEND (3-4 gün)                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  GÜN 4: SvelteKit Setup & Landing                                               │
│  ────────────────────────────────                                               │
│  [1 saat] □ SvelteKit projesi oluştur (npm create svelte)                       │
│  [1 saat] □ TailwindCSS kurulumu                                                │
│  [1 saat] □ API client (lib/api.ts) oluştur                                     │
│  [3 saat] □ Landing page tasarımı ve kodlaması                                  │
│  [2 saat] □ Header/Footer components                                            │
│                                                                                 │
│  GÜN 5: Domain Checker & Checkout                                               │
│  ────────────────────────────────                                               │
│  [3 saat] □ Domain checker sayfası ve komponenti                                │
│  [3 saat] □ Checkout sayfası (form + ruul.io redirect)                          │
│  [2 saat] □ Form validation                                                     │
│                                                                                 │
│  GÜN 6: Order Status & Callback                                                 │
│  ──────────────────────────────                                                 │
│  [2 saat] □ Callback sayfası (ruul.io'dan dönüş)                                │
│  [3 saat] □ Order status sayfası (progress bar, durum gösterimi)                │
│  [2 saat] □ Responsive tasarım kontrolü                                         │
│  [1 saat] □ Error handling ve loading states                                    │
│                                                                                 │
│  GÜN 7: Deployment & Test                                                       │
│  ────────────────────────────                                                   │
│  [2 saat] □ Vercel'e deploy et                                                  │
│  [2 saat] □ Hostinger'a backend deploy et                                       │
│  [2 saat] □ CORS ve environment variables kontrol                               │
│  [2 saat] □ Uçtan uca test (gerçek sipariş akışı)                               │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 13. RİSKLER VE ÇÖZÜMLER

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          RİSKLER VE ÇÖZÜMLER                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  RİSK 1: Email-based webhook güvenilirliği                                      │
│  ─────────────────────────────────────────                                      │
│  Problem: Gmail/Apps Script gecikebilir, email spam'e düşebilir                 │
│  Etki: Ödeme alındı ama sistem bilmiyor                                         │
│  Çözüm:                                                                         │
│  • Callback sayfasında da sipariş güncelle (double-check)                       │
│  • Admin panelde "ödeme bekleyenler" listesi                                    │
│  • Manuel kontrol mekanizması                                                   │
│  • Günde 1 kez ruul.io dashboard kontrolü                                       │
│                                                                                 │
│  RİSK 2: Domain checker yanlış sonuç                                            │
│  ───────────────────────────────────                                            │
│  Problem: WHOIS yavaş/hatalı olabilir                                           │
│  Etki: Müşteri alınmış domain için sipariş verir                                │
│  Çözüm:                                                                         │
│  • Sipariş sonrası tekrar kontrol (backend'de)                                  │
│  • Hata durumunda müşteriye bildirim + alternatif öner                          │
│  • İleride: Hostinger/external API'ye geç                                       │
│                                                                                 │
│  RİSK 3: Manuel işlem gecikmeleri                                               │
│  ─────────────────────────────────                                              │
│  Problem: Sipariş geldi ama sen müsait değilsin                                 │
│  Etki: Müşteri bekliyor, memnuniyetsizlik                                       │
│  Çözüm:                                                                         │
│  • Bekleme süresi beklentisini yönet (landing page'de "24-48 saat")             │
│  • Order status sayfasında gerçekçi timeline                                    │
│  • Email ile düzenli bilgilendirme                                              │
│  • Tatil/meşgul dönemlerde satışı geçici durdur                                 │
│                                                                                 │
│  RİSK 4: Hostinger hesap limitleri                                              │
│  ─────────────────────────────────                                              │
│  Problem: Business plan limitleri aşılır                                        │
│  Etki: Yeni domain/hosting ekleyemezsin                                         │
│  Çözüm:                                                                         │
│  • Hosting planını takip et                                                     │
│  • Gerekirse plan yükselt                                                       │
│  • Uzun vadede VPS'e geçiş planla                                               │
│                                                                                 │
│  RİSK 5: ruul.io bağımlılığı                                                    │
│  ────────────────────────────                                                   │
│  Problem: ruul.io kapanır veya koşulları değişir                                │
│  Etki: Ödeme alamazsın                                                          │
│  Çözüm:                                                                         │
│  • Şirket kurma planını paralel olarak araştır                                  │
│  • Alternatif: Stripe Atlas (US LLC)                                            │
│  • Veya: Almanya'da Kleinunternehmer statüsü                                    │
│                                                                                 │
│  RİSK 6: Ölçekleme                                                              │
│  ─────────────────                                                              │
│  Problem: Çok sipariş gelirse manuel yetişemezsin                               │
│  Etki: Müşteri kaybı, kötü review                                               │
│  Çözüm:                                                                         │
│  • Bu güzel bir problem! İlk 10-20 siparişten sonra otomasyon yatırımı          │
│  • Gerekirse sipariş kabul limitini koy                                         │
│  • Veya: Part-time yardımcı bul                                                 │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## EK: HIZLI REFERANS

### Önemli URL'ler (Production)

```
Frontend:        https://websitebuilder.vercel.app (veya custom domain)
Backend API:     https://api.yourdomain.com
ruul.io Space:   https://ruul.space/cuneytkaya/products/13347
Hostinger hPanel: https://hpanel.hostinger.com
```

### Kritik Credentials (Güvenli yerde sakla!)

```
□ Hostinger hPanel login
□ MySQL database credentials
□ Webhook secret key
□ Admin panel credentials
□ Gmail (Apps Script) hesabı
□ ruul.io login
□ Vercel login
□ GitHub repo access
```

### Destek ve Kaynaklar

```
SvelteKit Docs:     https://kit.svelte.dev/docs
TailwindCSS Docs:   https://tailwindcss.com/docs
PHP PDO:            https://www.php.net/manual/en/book.pdo.php
Hostinger Docs:     https://support.hostinger.com
Vercel Docs:        https://vercel.com/docs
Apps Script:        https://developers.google.com/apps-script
```

---

**Doküman Sonu**

*Bu doküman, projenin teknik referansı olarak kullanılmak üzere hazırlanmıştır. Geliştirme sürecinde güncellenmelidir.*