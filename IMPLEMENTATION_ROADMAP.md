# 🚀 Automated Website Builder - Implementation Roadmap

**Proje:** Ruul.io ödeme → Otomatik website deployment
**Tarih:** 30 Kasım 2025
**Süre:** 14 gün (Fast MVP)

---

## 📋 Genel Bakış

### Mimari
```
Ruul.io Payment Email
    ↓
Gmail Inbox (forwarded)
    ↓
Google Apps Script (5-min polling)
    ↓
POST /api/webhook/payment.php (Backend PHP)
    ↓
Create Deployment Job in Database
    ↓
Cron Worker (her dakika çalışır)
    ↓
DeploymentService.php (9 adımlı otomasyon)
    ↓
HostingerApiService.php (MCP Client)
    ↓
🌐 Vercel MCP Server (wsbusaas.vercel.app/api/mcp)
    ↓
Hostinger API (Domain + Hosting)
    ↓
FTP Deployment
    ↓
Email Notification (Customer + Admin)
```

### Teknoloji Stack
- **Backend:** PHP 8.0+
- **MCP Server:** Node.js Serverless (Vercel)
- **Database:** MySQL
- **Queue:** MySQL-based job queue + Cron worker
- **Email:** PHPMailer (Gmail SMTP)
- **Deployment:** FTP (Hostinger Business Account)
- **Templates:** Static HTML/CSS/JS

---

## 🗓️ 14 Günlük Implementation Plan

### HAFTA 1: Foundation & Core Logic

#### 📅 Gün 1-2: Database & Infrastructure Setup

**Yapılacaklar:**
1. ✅ Database migration dosyası oluştur
2. ✅ Migration'ı local ve production'da çalıştır
3. ✅ `.env` dosyasını güncelle
4. ✅ Boş service class'ları oluştur

**Dosyalar:**

1. `/backend/migrations/001_create_deployment_system.sql`
```sql
-- 6 yeni tablo:
-- deployment_jobs (job queue)
-- domain_registrations (domain tracking)
-- website_deployments (deployment tracking)
-- deployment_steps (step-by-step tracking)
-- email_notifications (email log)
-- template_registry (template management)

-- orders tablosuna yeni kolonlar:
-- deployment_type, business_name, business_phone, etc.
```

2. `/backend/.env` güncellemesi:
```bash
# MCP Server (Vercel)
MCP_SERVER_URL=https://wsbusaas.vercel.app/api/mcp

# FTP Deployment (Hostinger Business Account)
FTP_HOST=ftp.yourdomain.com
FTP_PORT=21
FTP_USERNAME=u123456789
FTP_PASSWORD=your-ftp-password
FTP_BASE_PATH=/public_html/clients

# Encryption
CREDENTIALS_ENCRYPTION_KEY=your-32-character-key-here

# Template storage
TEMPLATE_STORAGE_PATH=/home/user/public_html/backend/templates/websites

# Worker
WORKER_ENABLED=true
WORKER_MAX_JOBS_PER_RUN=3
```

**Test:**
```bash
# Migration çalıştır
mysql -u root -p website_builder < backend/migrations/001_create_deployment_system.sql

# Tabloları kontrol et
mysql -u root -p website_builder -e "SHOW TABLES;"
```

---

#### 📅 Gün 3-4: Vercel MCP Server Setup

**Yapılacaklar:**
1. ✅ Vercel projesine MCP server endpoint ekle
2. ✅ Hostinger API Key'i Vercel'e ekle
3. ✅ Deploy et
4. ✅ Test et
5. ✅ PHP MCP client oluştur
6. ✅ End-to-end test

**Dosyalar:**

1. `/api/mcp.js` (Vercel projesinde)
```javascript
// Vercel serverless function
export default async function handler(req, res) {
  const { tool, params } = req.body;

  // Tools:
  // - check_domain_availability
  // - create_whois_profile
  // - purchase_domain
  // - verify_domain

  // Hostinger API'yi çağır ve sonuç döndür
}
```

2. `vercel.json` güncelle:
```json
{
  "env": {
    "HOSTINGER_API_KEY": "@hostinger-api-key"
  },
  "functions": {
    "api/mcp.js": {
      "memory": 1024,
      "maxDuration": 30
    }
  }
}
```

3. `/backend/services/HostingerApiService.php`
```php
<?php
namespace App\Services;

class HostingerApiService
{
    private string $mcpEndpoint;

    public function checkDomainAvailability(string $domain): array;
    public function createWhoisProfile(array $contactData): array;
    public function purchaseDomain(string $domain, string $whoisProfileId): array;
    public function verifyDomainOwnership(string $domainId): array;

    private function callMcp(string $tool, array $params): array;
}
```

**Deploy:**
```bash
# Vercel projesinde
cd /path/to/wsbusaas-vercel-repo
vercel env add HOSTINGER_API_KEY
# API key'i gir

vercel --prod
```

**Test:**
```bash
# MCP endpoint test
curl -X POST https://wsbusaas.vercel.app/api/mcp \
  -H "Content-Type: application/json" \
  -d '{"tool":"check_domain_availability","params":{"domain":"test123.de"}}'

# PHP'den test
php -r "
require 'backend/services/HostingerApiService.php';
\$api = new \App\Services\HostingerApiService();
\$result = \$api->checkDomainAvailability('test123.de');
print_r(\$result);
"
```

---

#### 📅 Gün 5-7: Core Deployment Service

**Yapılacaklar:**
1. ✅ `DeploymentService.php` tüm 9 step ile
2. ✅ Job queue logic (create, process, retry)
3. ✅ Step-by-step execution + database tracking
4. ✅ Retry logic (exponential backoff)

**Dosyalar:**

1. `/backend/services/DeploymentService.php`
```php
<?php
namespace App\Services;

class DeploymentService
{
    const STEPS = [
        1 => 'check_domain_availability',
        2 => 'create_whois_profile',
        3 => 'purchase_domain',
        4 => 'verify_domain_ownership',
        5 => 'create_website_directory',
        6 => 'prepare_template',
        7 => 'customize_template',
        8 => 'deploy_via_ftp',
        9 => 'send_customer_notification'
    ];

    public function createDeploymentJob(string $orderId): string;
    public function processQueue(): void;

    private function processJob(string $jobId): void;
    private function executeStep(string $jobId, string $stepName, array &$payload): void;

    // Her step için method:
    private function stepCheckDomainAvailability(array $payload): array;
    private function stepCreateWhoisProfile(array $payload): array;
    // ... diğer 7 step
}
```

**Test:**
```php
// Job oluşturma testi
$deploymentService = new DeploymentService();
$jobId = $deploymentService->createDeploymentJob('WB20251130123456789');
echo "Job created: $jobId\n";

// Queue işleme testi
$deploymentService->processQueue();
```

---

### HAFTA 2: Template System & Deployment

#### 📅 Gün 8-9: Template System

**Yapılacaklar:**
1. ✅ `TemplateService.php` oluştur
2. ✅ Starter template tasarla (HTML/CSS/JS)
3. ✅ Variable replacement sistemi
4. ✅ Template test et

**Dosyalar:**

1. `/backend/services/TemplateService.php`
```php
<?php
namespace App\Services;

class TemplateService
{
    public function getTemplate(string $packageType, string $deploymentType): ?array;
    public function customizeTemplate(string $templatePath, array $customData): string;
    public function verifyTemplateChecksum(string $templatePath, string $checksum): bool;
}
```

2. `/backend/templates/websites/starter-static-v1.0.0/`
```
starter-static-v1.0.0/
├── index.html          ({{BUSINESS_NAME}}, {{PAGE_TITLE}} placeholders)
├── css/
│   └── style.css       ({{PRIMARY_COLOR}} placeholder)
├── js/
│   └── main.js
└── images/
    └── logo-placeholder.png
```

**Template customization:**
```html
<!-- index.html -->
<title>{{PAGE_TITLE}} - {{BUSINESS_NAME}}</title>
<h1>{{BUSINESS_NAME}}</h1>
<p>{{ABOUT_TEXT}}</p>
<a href="mailto:{{BUSINESS_EMAIL}}">{{BUSINESS_EMAIL}}</a>
<p>{{BUSINESS_PHONE}}</p>
```

**Test:**
```php
$templateService = new TemplateService();
$customData = [
    'business_name' => 'Test GmbH',
    'business_email' => 'info@test.de',
    'page_title' => 'Welcome',
    'primary_color' => '#ff6600'
];

$customizedPath = $templateService->customizeTemplate(
    '/backend/templates/websites/starter-static-v1.0.0',
    $customData
);

echo "Customized template: $customizedPath\n";
```

---

#### 📅 Gün 10-11: FTP Deployment & Email

**Yapılacaklar:**
1. ✅ `FtpDeploymentService.php` oluştur
2. ✅ Hostinger'da directory oluşturma
3. ✅ `EmailService.php` + PHPMailer
4. ✅ Email templates (EN/DE/TR)
5. ✅ FTP + email test

**Dosyalar:**

1. `/backend/services/FtpDeploymentService.php`
```php
<?php
namespace App\Services;

class FtpDeploymentService
{
    public function deploy(
        string $localPath,
        string $domainName,
        string $ftpUsername,
        string $ftpPassword
    ): array;

    private function uploadDirectory($ftpConn, string $localDir, string $remoteDir): array;
}
```

2. `/backend/services/EmailService.php`
```php
<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    public function sendDeploymentCompleteEmail(
        string $email,
        string $name,
        string $domain,
        string $websiteUrl,
        string $orderId
    ): bool;

    public function sendDeploymentFailedEmail(
        string $email,
        string $name,
        string $domain,
        string $errorMessage,
        string $orderId
    ): bool;
}
```

3. Email templates:
```
/backend/templates/emails/
├── en/
│   ├── deployment_complete.html
│   └── deployment_failed.html
├── de/
│   ├── deployment_complete.html
│   └── deployment_failed.html
└── tr/
    ├── deployment_complete.html
    └── deployment_failed.html
```

**FTP Deployment Strategy:**
```
/public_html/clients/
├── test-de/
│   ├── index.html
│   ├── css/
│   ├── js/
│   └── images/
├── example-com/
└── business-net/
```

**Test:**
```php
// FTP test
$ftpService = new FtpDeploymentService();
$result = $ftpService->deploy(
    '/tmp/customized-template',
    'test.de',
    'u123456789',
    'password'
);
print_r($result);

// Email test
$emailService = new EmailService();
$emailService->sendDeploymentCompleteEmail(
    'test@example.com',
    'Test User',
    'test.de',
    'https://test.de',
    'WB20251130123456'
);
```

---

#### 📅 Gün 12-13: Worker & Webhook Integration

**Yapılacaklar:**
1. ✅ `deployment-worker.php` cron script oluştur
2. ✅ `payment.php` webhook'u güncelle
3. ✅ Cron job kur
4. ✅ End-to-end test
5. ✅ Error handling

**Dosyalar:**

1. `/backend/workers/deployment-worker.php`
```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/DeploymentService.php';

// Lock file (prevent concurrent execution)
$lockFile = sys_get_temp_dir() . '/deployment-worker.lock';
$fp = fopen($lockFile, 'w');

if (!flock($fp, LOCK_EX | LOCK_NB)) {
    exit(0); // Already running
}

try {
    $deploymentService = new \App\Services\DeploymentService();
    $deploymentService->processQueue();
} finally {
    flock($fp, LOCK_UN);
    fclose($fp);
}
```

2. `/backend/api/webhook/payment.php` güncelle:
```php
<?php
// ... mevcut kod ...

// YENI: Payment confirmed olunca deployment job oluştur
try {
    require_once __DIR__ . '/../../services/DeploymentService.php';
    $deploymentService = new DeploymentService();

    $jobId = $deploymentService->createDeploymentJob($orderId);

    // Job ID'yi orders tablosuna kaydet
    $query = "UPDATE orders SET deployment_job_id = :job_id WHERE order_id = :order_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':job_id' => $jobId, ':order_id' => $orderId]);

    $orderService->logOrder($orderId, 'info', "Deployment job created: $jobId");
} catch (Exception $e) {
    $orderService->logOrder($orderId, 'error', "Failed to create deployment job: " . $e->getMessage());
}
```

**Cron Job Kurulumu:**
```bash
# Crontab düzenle
crontab -e

# Bu satırı ekle (her dakika çalışır)
* * * * * /usr/bin/php /home/user/public_html/backend/workers/deployment-worker.php >> /home/user/logs/deployment-worker.log 2>&1
```

**Test:**
```bash
# Worker'ı manuel çalıştır
php backend/workers/deployment-worker.php

# Log kontrol et
tail -f /home/user/logs/deployment-worker.log

# Webhook test et
curl -X POST https://bezmidar.de/api/webhook/payment \
  -H "X-Webhook-Secret: your-secret" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "WB20251130123456",
    "email": "test@example.com",
    "payment_status": "paid"
  }'
```

---

#### 📅 Gün 14: Testing & Production Deployment

**Yapılacaklar:**
1. ✅ End-to-end integration test
2. ✅ Failure scenario testing
3. ✅ Email delivery test (tüm diller)
4. ✅ Admin dashboard endpoint
5. ✅ Production deployment

**Test Checklist:**

**Local Development:**
- [ ] Migration çalışıyor
- [ ] Service class'ları instantiate oluyor
- [ ] Vercel MCP endpoint çalışıyor
- [ ] Template customization doğru HTML üretiyor
- [ ] Email gönderimi çalışıyor

**Integration Testing:**
- [ ] Frontend'den test order oluştur
- [ ] Webhook'u manuel tetikle
- [ ] Database'de job oluştuğunu doğrula
- [ ] Worker'ı manuel çalıştır: `php backend/workers/deployment-worker.php`
- [ ] `deployment_steps` tablosunda step'leri kontrol et
- [ ] Hostinger'da domain kaydını doğrula
- [ ] FTP'ye dosyaların yüklendiğini doğrula
- [ ] Customer'a email gittiğini doğrula

**Failure Scenarios:**
- [ ] Unavailable domain test
- [ ] Invalid FTP credentials
- [ ] Corrupted template (checksum fail)
- [ ] Retry logic test
- [ ] Admin failure notification

**Production Deployment:**
```bash
# 1. Vercel MCP server deploy
cd /path/to/vercel-project
vercel env add HOSTINGER_API_KEY
vercel --prod

# 2. Backend migration
mysql -u prod_user -p production_db < backend/migrations/001_create_deployment_system.sql

# 3. .env güncelle
# Production values ekle

# 4. Cron job kur
crontab -e
# Worker cron line ekle

# 5. Template'leri upload et
scp -r backend/templates/websites user@server:/home/user/public_html/backend/templates/

# 6. Template registry seed et
php backend/scripts/seed-templates.php

# 7. Test order ile dene
```

---

## 📁 Dosya Yapısı

### Oluşturulacak Yeni Dosyalar

```
/backend/
  ├── migrations/
  │   └── 001_create_deployment_system.sql        ✅ Yeni
  │
  ├── services/
  │   ├── HostingerApiService.php                 ✅ Yeni (MCP Client)
  │   ├── DeploymentService.php                   ✅ Yeni (Core)
  │   ├── TemplateService.php                     ✅ Yeni
  │   ├── FtpDeploymentService.php                ✅ Yeni
  │   ├── EmailService.php                        ✅ Yeni
  │   └── EncryptionService.php                   ✅ Yeni
  │
  ├── workers/
  │   └── deployment-worker.php                   ✅ Yeni
  │
  ├── templates/
  │   ├── websites/
  │   │   └── starter-static-v1.0.0/             ✅ Yeni
  │   │       ├── index.html
  │   │       ├── css/style.css
  │   │       ├── js/main.js
  │   │       └── images/
  │   └── emails/
  │       ├── en/
  │       │   ├── deployment_complete.html        ✅ Yeni
  │       │   └── deployment_failed.html          ✅ Yeni
  │       ├── de/
  │       │   ├── deployment_complete.html        ✅ Yeni
  │       │   └── deployment_failed.html          ✅ Yeni
  │       └── tr/
  │           ├── deployment_complete.html        ✅ Yeni
  │           └── deployment_failed.html          ✅ Yeni
  │
  ├── scripts/
  │   └── seed-templates.php                      ✅ Yeni
  │
  └── api/
      ├── webhook/
      │   └── payment.php                         🔧 Güncelle
      └── admin/
          └── deployments.php                     ✅ Yeni (Optional)

/vercel-project/  (wsbusaas Vercel repository)
  ├── api/
  │   └── mcp.js                                  ✅ Yeni
  └── vercel.json                                 🔧 Güncelle
```

### Güncellenecek Mevcut Dosyalar

```
/backend/
  ├── .env                                        🔧 Yeni env variables
  └── api/webhook/payment.php                     🔧 Job creation ekle
```

---

## 🔑 Kritik Environment Variables

### Backend `.env`
```bash
# MCP Server
MCP_SERVER_URL=https://wsbusaas.vercel.app/api/mcp

# FTP
FTP_HOST=ftp.yourdomain.com
FTP_PORT=21
FTP_USERNAME=u123456789
FTP_PASSWORD=your-ftp-password
FTP_BASE_PATH=/public_html/clients

# Email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
FROM_EMAIL=noreply@bezmidar.de
FROM_NAME="Bezmidar Website Builder"

# Security
CREDENTIALS_ENCRYPTION_KEY=your-32-char-key-here
WEBHOOK_SECRET=your-webhook-secret

# Template
TEMPLATE_STORAGE_PATH=/home/user/public_html/backend/templates/websites

# Worker
WORKER_ENABLED=true
WORKER_MAX_JOBS_PER_RUN=3
```

### Vercel Environment Variables
```bash
HOSTINGER_API_KEY=your-hostinger-api-key
```

---

## 🧪 Test Scenarios

### 1. Happy Path Test
```
1. Frontend'den order oluştur
2. Payment webhook tetikle
3. Worker çalışsın (cron veya manuel)
4. Domain satın alınsın
5. Website deploy olsun
6. Email gitsin
```

### 2. Domain Unavailable
```
1. Unavailable domain ile order
2. Step 1'de fail olmalı
3. Retry yapılmalı
4. Max retry'dan sonra customer + admin'e email
```

### 3. FTP Failure
```
1. Wrong FTP credentials
2. Step 8'de fail
3. Retry yapılmalı
4. Exponential backoff: 5min → 15min → 45min
```

### 4. Template Corruption
```
1. Template checksum mismatch
2. Step 6'da fail
3. Error log'lanmalı
4. Admin'e notification
```

---

## 📊 Database Tables - Quick Reference

| Tablo | Amaç |
|-------|------|
| `deployment_jobs` | Job queue (status, retry_count, error_message) |
| `domain_registrations` | Domain satın alma tracking |
| `website_deployments` | Website deployment tracking |
| `deployment_steps` | Her step'in detaylı logu |
| `email_notifications` | Email gönderim logu |
| `template_registry` | Template management |
| `orders` | ✏️ Yeni kolonlar eklendi (deployment_type, business_name, etc.) |

---

## 🚨 Kritik Noktalar

### Security
- ✅ Webhook signature validation
- ✅ API keys `.env`'de
- ✅ FTP credentials encrypted
- ✅ Template checksum verification
- ✅ Lock file (worker concurrent execution prevention)

### Performance
- ✅ Cron her dakika (max 3 job per run)
- ✅ Exponential backoff retry
- ✅ Background processing (non-blocking)
- ✅ Vercel serverless (auto-scale)

### Error Handling
- ✅ Step-by-step error logging
- ✅ Max 3 retry attempts
- ✅ Failure notifications (customer + admin)
- ✅ Order status tracking

---

## 📈 Success Criteria

MVP tamamlanmış sayılır eğer:

1. ✅ Payment webhook → Job created
2. ✅ Worker processes queue
3. ✅ Domain successfully registered (Hostinger)
4. ✅ Website deployed and accessible
5. ✅ Customer receives email with URL
6. ✅ Failed deployments notify both customer + admin
7. ✅ All steps logged in database

---

## 🎯 Next Steps After MVP

1. **WordPress Support** - WordPress auto-install
2. **Multiple Templates** - Template marketplace
3. **SSL Certificates** - Auto Let's Encrypt
4. **Email Accounts** - Create @domain.de emails
5. **Admin Dashboard** - Visual monitoring UI
6. **Direct Ruul.io API** - Replace email parsing
7. **Custom Logo Upload** - Customer branding
8. **Multi-language Templates** - Separate DE/TR/EN templates

---

## 📞 Support & Resources

- **Hostinger API Docs:** https://developers.hostinger.com/
- **MCP Protocol:** https://modelcontextprotocol.io/
- **Vercel Serverless:** https://vercel.com/docs/functions
- **PHPMailer:** https://github.com/PHPMailer/PHPMailer

---

## ✅ Checklist - Implementation Progress

### Week 1
- [ ] Day 1-2: Database migrations + .env setup
- [ ] Day 3-4: Vercel MCP server deployment
- [ ] Day 5-7: DeploymentService.php core logic

### Week 2
- [ ] Day 8-9: Template system
- [ ] Day 10-11: FTP deployment + Email
- [ ] Day 12-13: Worker + Webhook integration
- [ ] Day 14: Testing + Production deployment

---

**Son Güncelleme:** 30 Kasım 2025
**Versiyon:** 1.0.0
**Durum:** ✅ Plan Onaylandı - Implementation Başlayabilir

---

## 🎬 İlk Adım: Şimdi Ne Yapmalı?

1. ✅ Bu roadmap'i oku
2. ✅ Hostinger API Key al
3. ✅ Vercel'de MCP server deploy et
4. ✅ Backend migration'ı çalıştır
5. ✅ İlk service class'ı yaz (`HostingerApiService.php`)
6. ✅ Test et!

**Komut:**
```bash
# Başla!
cd /Users/thomasmuentzer/Desktop/wsbusaas
git checkout -b feature/automated-deployment
# Implementation'a başla...
```

🚀 **Haydi başlayalım!**
