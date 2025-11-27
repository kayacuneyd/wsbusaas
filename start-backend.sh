#!/bin/bash

# Navigate to backend
cd backend

# Install dependencies if vendor missing
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install
fi

# Setup DB
echo "Setting up database..."
php setup_local_db.php

# Start Server
echo "Starting PHP Development Server at http://localhost:8000"
php -S localhost:8000 router.php
