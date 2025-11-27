#!/bin/bash

# Hostinger SSH Bilgileri
# Bu bilgileri Hostinger panelinizden (Advanced > SSH Access) alabilirsiniz.
SSH_USER="u553245641" # Tahmini kullanıcı adı (DB kullanıcısından alındı)
SSH_HOST="185.224.137.82" # BURAYI GÜNCELLEYİN (Hostinger IP veya Hostname)
SSH_PORT="65002" # Hostinger genelde 65002 portunu kullanır
REMOTE_PATH="/home/u553245641/domains/bezmidar.de/public_html" # BURAYI GÜNCELLEYİN (Hedef klasör)
SSH_KEY_PATH="hostinger_key" # SSH Key dosyası proje ana dizininde

# Renkler
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Backend dosyaları Hostinger'a yükleniyor...${NC}"

# Rsync Komutu
# -a: Archive mode (permissions, times, etc.)
# -v: Verbose
# -z: Compress
# --exclude: Gereksiz dosyaları yükleme
rsync -avz -e "ssh -p $SSH_PORT -i $SSH_KEY_PATH" \
    --exclude '.git' \
    --exclude '.env' \
    --exclude 'node_modules' \
    --exclude 'setup_local_db.php' \
    --exclude 'router.php' \
    backend/ \
    $SSH_USER@$SSH_HOST:$REMOTE_PATH

echo -e "${GREEN}Yükleme tamamlandı!${NC}"
echo "Vendor klasörü de yüklendiği için sunucuda 'composer install' yapmanıza gerek yoktur."
