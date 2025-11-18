output "instance_id" {
  description = "ID of the EC2 instance"
  value       = aws_instance.main.id
}

output "instance_public_ip" {
  description = "Public IP address of the instance"
  value       = aws_eip.main.public_ip
}

output "instance_public_dns" {
  description = "Public DNS name of the instance"
  value       = aws_eip.main.public_dns
}

output "application_url" {
  description = "URL to access the application"
  value       = "http://${aws_eip.main.public_ip}"
}

output "ssh_command" {
  description = "SSH command to connect to the instance"
  value       = "ssh -i ${var.key_name}.pem ubuntu@${aws_eip.main.public_ip}"
}

output "database_credentials" {
  description = "Database connection information"
  value = {
    host     = "localhost"
    database = "fitfab"
    username = "fitfab"
    password = "<SENSITIVE>"
  }
  sensitive = true
}

output "setup_instructions" {
  description = "Instructions to complete the setup"
  value = <<-EOT
FitFab Demo Environment Setup Instructions:

1. Wait 5-10 minutes for the instance to complete setup

2. Access the welcome page:
   http://${aws_eip.main.public_ip}

3. SSH into the server:
   ssh -i ${var.key_name}.pem ubuntu@${aws_eip.main.public_ip}

4. Clone your FitFab repository:
   cd /var/www
   sudo rm -rf fitfab/*
   sudo git clone <your-repo> fitfab
   sudo chown -R www-data:www-data fitfab

5. Install dependencies:
   cd /var/www/fitfab
   sudo -u www-data composer install --no-dev --optimize-autoloader
   sudo -u www-data npm install
   sudo -u www-data npm run build

6. Configure environment:
   sudo cp .env.example .env
   sudo -u www-data php artisan key:generate

   Edit .env with:
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=fitfab
   DB_USERNAME=fitfab
   DB_PASSWORD=<your-db-password>

   REDIS_HOST=localhost
   REDIS_PORT=6379

7. Run migrations:
   sudo -u www-data php artisan migrate --seed

8. Set permissions:
   sudo chown -R www-data:www-data /var/www/fitfab
   sudo chmod -R 755 /var/www/fitfab
   sudo chmod -R 775 /var/www/fitfab/storage
   sudo chmod -R 775 /var/www/fitfab/bootstrap/cache

9. Start queue workers:
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-worker:*
   sudo supervisorctl start laravel-horizon

10. Restart services:
    sudo systemctl restart php8.2-fpm nginx

11. Access your application:
    http://${aws_eip.main.public_ip}

Database Password: Run 'terraform output -raw database_password' to see it

EOT
}

output "database_password" {
  description = "Database password"
  value       = var.db_password
  sensitive   = true
}

output "monitoring_commands" {
  description = "Useful monitoring commands"
  value = <<-EOT
Monitor your demo instance:

View logs:
  sudo tail -f /var/log/nginx/access.log
  sudo tail -f /var/log/nginx/error.log
  sudo journalctl -u php8.2-fpm -f
  sudo tail -f /var/www/fitfab/storage/logs/laravel.log

Check services:
  sudo systemctl status nginx
  sudo systemctl status php8.2-fpm
  sudo systemctl status mysql
  sudo systemctl status redis-server
  sudo supervisorctl status

Resource usage:
  htop
  df -h
  free -h

Database:
  mysql -u fitfab -p fitfab
EOT
}
