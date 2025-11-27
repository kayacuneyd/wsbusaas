#!/bin/bash

# Renkler
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}=== Website Builder Deployment Yöneticisi ===${NC}"
echo "1) Sadece GitHub'a Yükle"
echo "2) Sadece Backend'i Hostinger'a Yükle"
echo "3) Her İkisini de Yap (GitHub + Hostinger)"
echo "4) Çıkış"

read -p "Seçiminiz (1-4): " choice

case $choice in
    1)
        ./push_to_github.sh
        ;;
    2)
        ./deploy_backend.sh
        ;;
    3)
        echo -e "${BLUE}Adım 1: GitHub'a Yükleme${NC}"
        ./push_to_github.sh
        
        echo ""
        echo -e "${BLUE}Adım 2: Hostinger'a Yükleme${NC}"
        ./deploy_backend.sh
        ;;
    4)
        echo "Çıkış yapılıyor."
        exit 0
        ;;
    *)
        echo "Geçersiz seçim."
        exit 1
        ;;
esac
