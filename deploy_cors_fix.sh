#!/bin/bash

# Quick CORS Fix Deployment Script
# Sadece değiştirilen dosyaları deploy eder

# Hostinger SSH Bilgileri
SSH_USER="u553245641"
SSH_HOST="185.224.137.82"
SSH_PORT="65002"
REMOTE_PATH="/home/u553245641/domains/bezmidar.de/public_html/api"
SSH_KEY_PATH="hostinger_key"

# Renkler
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}🚀 CORS Fix Deployment Başlatılıyor...${NC}\n"

# Değiştirilen dosyalar
FILES=(
    ".htaccess"
    "api/cors.php"
    "api/admin/login.php"
    "api/admin/seo.php"
    "api/admin/unmatched.php"
    "api/admin/diagnose.php"
    "api/user/orders.php"
)

echo -e "${GREEN}📦 Deploy edilecek dosyalar:${NC}"
for file in "${FILES[@]}"; do
    echo "  - $file"
done
echo ""

# Production için cors.php'yi kopyala
echo -e "${YELLOW}🔧 Production CORS dosyası hazırlanıyor...${NC}"
if [ -f "backend/api/cors.production.php" ]; then
    cp backend/api/cors.production.php backend/api/cors.php.prod
    echo -e "${GREEN}✓ Production CORS hazır${NC}\n"
else
    echo -e "${YELLOW}⚠ Production CORS dosyası bulunamadı, mevcut cors.php kullanılıyor${NC}\n"
fi

# Confirm
# Confirmation skipped for automation
echo -e "${GREEN}🚀 Auto-confirming deployment...${NC}"

echo -e "\n${GREEN}🚀 Dosyalar yükleniyor...${NC}\n"

# Her dosyayı tek tek deploy et
for file in "${FILES[@]}"; do
    if [ -f "backend/$file" ]; then
        echo -e "${YELLOW}Uploading: $file${NC}"

        # Dosyanın remote dizinini oluştur
        REMOTE_DIR=$(dirname "$file")
        ssh -p $SSH_PORT -i $SSH_KEY_PATH $SSH_USER@$SSH_HOST "mkdir -p $REMOTE_PATH/$REMOTE_DIR" 2>/dev/null

        # Dosyayı yükle
        scp -P $SSH_PORT -i $SSH_KEY_PATH "backend/$file" "$SSH_USER@$SSH_HOST:$REMOTE_PATH/$file"

        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ $file başarıyla yüklendi${NC}"
        else
            echo -e "${RED}✗ $file yüklenemedi!${NC}"
        fi
    else
        echo -e "${RED}✗ Dosya bulunamadı: backend/$file${NC}"
    fi
    echo ""
done

# Production CORS dosyasını da yükle
if [ -f "backend/api/cors.php.prod" ]; then
    echo -e "${YELLOW}Production CORS dosyası yükleniyor...${NC}"
    scp -P $SSH_PORT -i $SSH_KEY_PATH "backend/api/cors.php.prod" "$SSH_USER@$SSH_HOST:$REMOTE_PATH/api/cors.php"
    rm backend/api/cors.php.prod
    echo -e "${GREEN}✓ Production CORS yüklendi${NC}\n"
fi

echo -e "${GREEN}✅ Deployment tamamlandı!${NC}\n"

# Test önerileri
echo -e "${YELLOW}📋 Test Adımları:${NC}"
echo "1. Browser'da https://www.bezmidar.de adresine git"
echo "2. Console'u aç (F12)"
echo "3. CORS hatası olmamalı"
echo ""
echo -e "${YELLOW}🔍 Manuel Test:${NC}"
echo "curl -H 'Origin: https://bezmidar.de' https://api.bezmidar.de/api/packages -v"
echo ""
echo -e "${YELLOW}📊 Detaylı test için:${NC}"
echo "Tarayıcı: https://www.bezmidar.de"
echo "Console: Herhangi bir CORS hatası görünmemeli"
echo ""
echo -e "${GREEN}Deployment başarıyla tamamlandı! 🎉${NC}"
