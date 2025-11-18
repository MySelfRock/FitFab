# DB Subnet Group
resource "aws_db_subnet_group" "main" {
  name       = "${var.environment}-fitfab-db-subnet-group"
  subnet_ids = aws_subnet.private[*].id

  tags = {
    Name = "${var.environment}-fitfab-db-subnet-group"
  }
}

# RDS Parameter Group
resource "aws_db_parameter_group" "main" {
  name   = "${var.environment}-fitfab-pg15"
  family = "postgres15"

  parameter {
    name  = "log_connections"
    value = "1"
  }

  parameter {
    name  = "log_disconnections"
    value = "1"
  }

  parameter {
    name  = "log_statement"
    value = "ddl"
  }

  tags = {
    Name = "${var.environment}-fitfab-pg15"
  }
}

# RDS Instance
resource "aws_db_instance" "main" {
  identifier = "${var.environment}-fitfab-db"

  # Engine
  engine               = "postgres"
  engine_version       = "15.4"
  instance_class       = var.db_instance_class
  parameter_group_name = aws_db_parameter_group.main.name

  # Storage
  allocated_storage     = var.db_allocated_storage
  max_allocated_storage = var.db_max_allocated_storage
  storage_type          = "gp3"
  storage_encrypted     = true

  # Database
  db_name  = var.db_name
  username = var.db_username
  password = var.db_password
  port     = 5432

  # Network
  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = [aws_security_group.rds.id]
  publicly_accessible    = false

  # High Availability
  multi_az = true

  # Backup
  backup_retention_period   = var.db_backup_retention_period
  backup_window             = "03:00-04:00"
  maintenance_window        = "mon:04:00-mon:05:00"
  skip_final_snapshot       = false
  final_snapshot_identifier = "${var.environment}-fitfab-db-final-snapshot-${formatdate("YYYY-MM-DD-hhmm", timestamp())}"
  copy_tags_to_snapshot     = true

  # Monitoring
  enabled_cloudwatch_logs_exports = ["postgresql", "upgrade"]
  monitoring_interval             = 60
  monitoring_role_arn             = aws_iam_role.rds_monitoring.arn
  performance_insights_enabled    = true
  performance_insights_retention_period = 7

  # Maintenance
  auto_minor_version_upgrade = true
  deletion_protection        = true

  tags = {
    Name = "${var.environment}-fitfab-db"
  }

  lifecycle {
    ignore_changes = [final_snapshot_identifier]
  }
}

# IAM Role for RDS Enhanced Monitoring
resource "aws_iam_role" "rds_monitoring" {
  name = "${var.environment}-fitfab-rds-monitoring-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "monitoring.rds.amazonaws.com"
        }
      }
    ]
  })
}

resource "aws_iam_role_policy_attachment" "rds_monitoring" {
  role       = aws_iam_role.rds_monitoring.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
}

# RDS Read Replica (optional, commented out)
# resource "aws_db_instance" "read_replica" {
#   identifier             = "${var.environment}-fitfab-db-replica"
#   replicate_source_db    = aws_db_instance.main.identifier
#   instance_class         = var.db_instance_class
#   publicly_accessible    = false
#   skip_final_snapshot    = true
#   vpc_security_group_ids = [aws_security_group.rds.id]
#
#   tags = {
#     Name = "${var.environment}-fitfab-db-replica"
#   }
# }
