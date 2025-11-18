terraform {
  required_version = ">= 1.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = var.aws_region

  default_tags {
    tags = {
      Environment = var.environment
      Project     = "FitFab"
      ManagedBy   = "Terraform"
      Purpose     = "Demo"
    }
  }
}

# Data sources
data "aws_ami" "ubuntu" {
  most_recent = true
  owners      = ["099720109477"] # Canonical

  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-jammy-22.04-amd64-server-*"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }
}

# Elastic IP for the instance
resource "aws_eip" "main" {
  domain   = "vpc"
  instance = aws_instance.main.id

  tags = {
    Name = "${var.environment}-fitfab-eip"
  }
}

# User data script - installs everything needed
locals {
  user_data = <<-EOF
#!/bin/bash
set -e

# Log all output
exec > >(tee /var/log/user-data.log)
exec 2>&1

echo "=== Starting FitFab Demo Setup ==="
echo "Started at: $(date)"

# Update system
apt-get update
apt-get upgrade -y

# Install required packages
apt-get install -y \
    software-properties-common \
    ca-certificates \
    lsb-release \
    apt-transport-https \
    curl \
    git \
    unzip \
    supervisor \
    nginx

# Install PHP 8.2
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-redis \
    php8.2-xml \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install Node.js 20.x
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Install MySQL 8
debconf-set-selections <<< 'mysql-server mysql-server/root_password password ${var.db_password}'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password ${var.db_password}'
apt-get install -y mysql-server mysql-client

# Configure MySQL
mysql -uroot -p${var.db_password} -e "CREATE DATABASE IF NOT EXISTS fitfab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot -p${var.db_password} -e "CREATE USER IF NOT EXISTS 'fitfab'@'localhost' IDENTIFIED BY '${var.db_password}';"
mysql -uroot -p${var.db_password} -e "GRANT ALL PRIVILEGES ON fitfab.* TO 'fitfab'@'localhost';"
mysql -uroot -p${var.db_password} -e "FLUSH PRIVILEGES;"

# Install Redis
apt-get install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# Create application directory
mkdir -p /var/www/fitfab
chown -R www-data:www-data /var/www/fitfab

# Clone application (placeholder - replace with actual repo)
# git clone https://github.com/yourusername/fitfab.git /var/www/fitfab
# For now, create placeholder
cat > /var/www/fitfab/index.php <<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>FitFab Demo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #4F46E5; }
        .status { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .status h2 { margin-top: 0; }
        .ok { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🎉 FitFab Demo Environment</h1>
    <p>Welcome to the FitFab demo environment. This instance is ready to deploy your Laravel application!</p>

    <div class="status">
        <h2>System Status</h2>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>MySQL:</strong> <span class="ok">✓ Running</span></p>
        <p><strong>Redis:</strong> <span class="ok">✓ Running</span></p>
        <p><strong>Nginx:</strong> <span class="ok">✓ Running</span></p>
    </div>

    <div class="status">
        <h2>Next Steps</h2>
        <ol>
            <li>SSH into the server: <code>ssh -i your-key.pem ubuntu@${aws_eip.main.public_ip}</code></li>
            <li>Clone your repository to /var/www/fitfab</li>
            <li>Run <code>composer install</code></li>
            <li>Configure <code>.env</code> file with database credentials</li>
            <li>Run <code>php artisan migrate --seed</code></li>
            <li>Build assets: <code>npm install && npm run build</code></li>
            <li>Restart services: <code>sudo systemctl restart php8.2-fpm nginx supervisor</code></li>
        </ol>
    </div>

    <div class="status">
        <h2>Database Credentials</h2>
        <p><strong>Host:</strong> localhost</p>
        <p><strong>Database:</strong> fitfab</p>
        <p><strong>Username:</strong> fitfab</p>
        <p><strong>Password:</strong> (check terraform outputs)</p>
    </div>
</body>
</html>
HTML

mkdir -p /var/www/fitfab/public
mv /var/www/fitfab/index.php /var/www/fitfab/public/

# Configure Nginx
cat > /etc/nginx/sites-available/fitfab <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name _;
    root /var/www/fitfab/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/fitfab /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Configure PHP-FPM
sed -i 's/^;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.2/fpm/php.ini
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini

# Configure Supervisor for Laravel Queue
cat > /etc/supervisor/conf.d/laravel-worker.conf <<'SUPERVISOR'
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/fitfab/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=false
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel-worker.log
stopwaitsecs=3600
SUPERVISOR

# Configure Supervisor for Laravel Horizon
cat > /etc/supervisor/conf.d/laravel-horizon.conf <<'SUPERVISOR'
[program:laravel-horizon]
process_name=%(program_name)s
command=php /var/www/fitfab/artisan horizon
autostart=false
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel-horizon.log
stopwaitsecs=3600
SUPERVISOR

# Set proper permissions
chown -R www-data:www-data /var/www/fitfab
chmod -R 755 /var/www/fitfab

# Restart services
systemctl enable nginx
systemctl enable php8.2-fpm
systemctl enable supervisor
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart supervisor

echo "=== FitFab Demo Setup Complete ==="
echo "Completed at: $(date)"
echo "Access at: http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4)"
EOF
}

# EC2 Instance
resource "aws_instance" "main" {
  ami           = data.aws_ami.ubuntu.id
  instance_type = var.instance_type
  key_name      = var.key_name

  vpc_security_group_ids = [aws_security_group.main.id]

  root_block_device {
    volume_size           = 30
    volume_type           = "gp3"
    encrypted             = true
    delete_on_termination = true
  }

  user_data = local.user_data

  tags = {
    Name = "${var.environment}-fitfab-demo"
  }

  lifecycle {
    ignore_changes = [ami]
  }
}
