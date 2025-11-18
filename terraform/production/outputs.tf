output "vpc_id" {
  description = "VPC ID"
  value       = aws_vpc.main.id
}

output "alb_dns_name" {
  description = "DNS name of the Application Load Balancer"
  value       = aws_lb.main.dns_name
}

output "alb_zone_id" {
  description = "Zone ID of the Application Load Balancer"
  value       = aws_lb.main.zone_id
}

output "rds_endpoint" {
  description = "RDS database endpoint"
  value       = aws_db_instance.main.endpoint
  sensitive   = true
}

output "rds_database_name" {
  description = "RDS database name"
  value       = aws_db_instance.main.db_name
}

output "redis_endpoint" {
  description = "Redis cluster endpoint"
  value       = aws_elasticache_replication_group.main.configuration_endpoint_address
  sensitive   = true
}

output "redis_port" {
  description = "Redis port"
  value       = aws_elasticache_replication_group.main.port
}

output "s3_bucket_name" {
  description = "S3 bucket name for assets"
  value       = aws_s3_bucket.assets.id
}

output "bastion_public_ip" {
  description = "Public IP of bastion host"
  value       = aws_instance.bastion.public_ip
}

output "asg_name" {
  description = "Name of the Auto Scaling Group"
  value       = aws_autoscaling_group.main.name
}

output "cloudwatch_log_group" {
  description = "CloudWatch Log Group name"
  value       = aws_cloudwatch_log_group.fitfab.name
}

# Configuration summary for easy setup
output "configuration_summary" {
  description = "Configuration summary for application setup"
  value = {
    alb_url          = "http://${aws_lb.main.dns_name}"
    database_host    = split(":", aws_db_instance.main.endpoint)[0]
    database_port    = "5432"
    database_name    = aws_db_instance.main.db_name
    redis_host       = aws_elasticache_replication_group.main.configuration_endpoint_address
    redis_port       = tostring(aws_elasticache_replication_group.main.port)
    s3_bucket        = aws_s3_bucket.assets.id
    bastion_ip       = aws_instance.bastion.public_ip
  }
}

# Environment variables for Laravel .env file
output "laravel_env_vars" {
  description = "Environment variables for Laravel .env file"
  value = <<-EOT
APP_ENV=production
APP_DEBUG=false
APP_URL=http://${aws_lb.main.dns_name}

DB_CONNECTION=pgsql
DB_HOST=${split(":", aws_db_instance.main.endpoint)[0]}
DB_PORT=5432
DB_DATABASE=${aws_db_instance.main.db_name}
DB_USERNAME=${var.db_username}
DB_PASSWORD=<SENSITIVE>

REDIS_HOST=${aws_elasticache_replication_group.main.configuration_endpoint_address}
REDIS_PORT=${aws_elasticache_replication_group.main.port}

AWS_BUCKET=${aws_s3_bucket.assets.id}
AWS_DEFAULT_REGION=${var.aws_region}

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
EOT
  sensitive = true
}
