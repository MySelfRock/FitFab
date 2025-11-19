# ElastiCache Subnet Group
resource "aws_elasticache_subnet_group" "main" {
  name       = "${var.environment}-fitfab-cache-subnet"
  subnet_ids = aws_subnet.private[*].id

  tags = {
    Name = "${var.environment}-fitfab-cache-subnet"
  }
}

# ElastiCache Parameter Group
resource "aws_elasticache_parameter_group" "main" {
  name   = "${var.environment}-fitfab-redis7"
  family = "redis7"

  parameter {
    name  = "maxmemory-policy"
    value = "allkeys-lru"
  }

  tags = {
    Name = "${var.environment}-fitfab-redis7"
  }
}

# ElastiCache Replication Group (Redis Cluster)
resource "aws_elasticache_replication_group" "main" {
  replication_group_id = "${var.environment}-fitfab-redis"
  description          = "Redis cluster for FitFab"

  # Engine
  engine               = "redis"
  engine_version       = "7.0"
  node_type            = var.redis_node_type
  parameter_group_name = aws_elasticache_parameter_group.main.name

  # Cluster configuration
  num_cache_clusters = var.redis_num_cache_nodes
  port               = 6379

  # Network
  subnet_group_name  = aws_elasticache_subnet_group.main.name
  security_group_ids = [aws_security_group.elasticache.id]

  # High Availability
  automatic_failover_enabled  = var.redis_num_cache_nodes > 1 ? true : false
  multi_az_enabled            = var.redis_num_cache_nodes > 1 ? true : false

  # Backup
  snapshot_retention_limit = 5
  snapshot_window          = "03:00-05:00"
  maintenance_window       = "sun:05:00-sun:07:00"

  # Security
  at_rest_encryption_enabled = true
  transit_encryption_enabled = true
  auth_token_enabled         = false # Set to true and provide auth_token for additional security

  # Notifications
  notification_topic_arn = length(var.alarm_email) > 0 ? aws_sns_topic.alarms[0].arn : null

  # Maintenance
  auto_minor_version_upgrade = true

  tags = {
    Name = "${var.environment}-fitfab-redis"
  }
}

# SNS Topic for ElastiCache Notifications
resource "aws_sns_topic" "alarms" {
  count = length(var.alarm_email) > 0 ? 1 : 0
  name  = "${var.environment}-fitfab-alarms"

  tags = {
    Name = "${var.environment}-fitfab-alarms"
  }
}

resource "aws_sns_topic_subscription" "alarms" {
  count     = length(var.alarm_email) > 0 ? 1 : 0
  topic_arn = aws_sns_topic.alarms[0].arn
  protocol  = "email"
  endpoint  = var.alarm_email
}
