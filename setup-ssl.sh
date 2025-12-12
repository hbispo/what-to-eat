#!/bin/bash

# Setup SSL for whattoeat.lan to enable service workers

echo "Setting up SSL for whattoeat.lan..."

# Enable SSL module
echo "Enabling SSL module..."
sudo a2enmod ssl

# Create SSL directories if they don't exist
sudo mkdir -p /etc/ssl/private /etc/ssl/certs

# Generate self-signed certificate
echo "Generating self-signed certificate..."
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/whattoeat.key \
    -out /etc/ssl/certs/whattoeat.crt \
    -subj "/C=US/ST=State/L=City/O=Organization/CN=whattoeat.lan"

# Create Apache SSL virtual host configuration
echo "Creating Apache SSL virtual host..."
sudo tee /etc/apache2/sites-available/whattoeat-ssl.conf > /dev/null <<EOF
<VirtualHost *:443>
    ServerName whattoeat.lan
    DocumentRoot /var/www/html/what-to-eat/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/whattoeat.crt
    SSLCertificateKeyFile /etc/ssl/private/whattoeat.key

    <Directory /var/www/html/what-to-eat/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/whattoeat-ssl-error.log
    CustomLog \${APACHE_LOG_DIR}/whattoeat-ssl-access.log combined
</VirtualHost>
EOF

# Enable the SSL site
echo "Enabling SSL site..."
sudo a2ensite whattoeat-ssl.conf

# Create HTTP to HTTPS redirect (optional but recommended)
echo "Creating HTTP to HTTPS redirect..."
sudo tee /etc/apache2/sites-available/whattoeat.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName whattoeat.lan
    DocumentRoot /var/www/html/what-to-eat/public

    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    <Directory /var/www/html/what-to-eat/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/whattoeat-error.log
    CustomLog \${APACHE_LOG_DIR}/whattoeat-access.log combined
</VirtualHost>
EOF

# Enable the HTTP site
sudo a2ensite whattoeat.conf

# Test Apache configuration
echo "Testing Apache configuration..."
sudo apache2ctl configtest

if [ $? -eq 0 ]; then
    echo "Configuration is valid. Restarting Apache..."
    sudo systemctl restart apache2
    echo ""
    echo "SSL setup complete!"
    echo ""
    echo "You can now access your app at: https://whattoeat.lan"
    echo ""
    echo "Note: Your browser will show a security warning because this is a self-signed certificate."
    echo "Click 'Advanced' and then 'Proceed to whattoeat.lan' to continue."
    echo ""
    echo "The service worker should now register successfully!"
else
    echo "Configuration test failed. Please check the errors above."
    exit 1
fi

