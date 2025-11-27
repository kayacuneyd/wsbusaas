#!/bin/bash

# Renkler
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Git kontrolü
if [ ! -d ".git" ]; then
    echo -e "${RED}Hata: Bu klasör bir Git deposu değil.${NC}"
    echo "Lütfen önce 'git init' komutunu çalıştırın ve remote ekleyin."
    exit 1
fi

echo -e "${YELLOW}GitHub'a yükleme işlemi başlatılıyor...${NC}"

# Değişiklikleri ekle
git add .

# Durumu göster
git status

# Commit mesajı iste
echo -e "${GREEN}Lütfen commit mesajını girin (Boş bırakırsanız 'Update' kullanılır):${NC}"
read commit_message

if [ -z "$commit_message" ]; then
    commit_message="Update"
fi

# Commit
git commit -m "$commit_message"

# Push
echo -e "${YELLOW}GitHub'a gönderiliyor...${NC}"
current_branch=$(git rev-parse --abbrev-ref HEAD)
git push -u origin $current_branch

if [ $? -eq 0 ]; then
    echo -e "${GREEN}GitHub yüklemesi başarılı!${NC}"
else
    echo -e "${RED}GitHub yüklemesi sırasında bir hata oluştu.${NC}"
fi
