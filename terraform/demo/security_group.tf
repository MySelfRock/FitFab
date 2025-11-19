# Security Group for Demo Instance
resource "aws_security_group" "main" {
  name        = "${var.environment}-fitfab-demo-sg"
  description = "Security group for FitFab demo instance"

  # HTTP
  ingress {
    description = "HTTP from anywhere"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # HTTPS (optional)
  ingress {
    description = "HTTPS from anywhere"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # SSH
  ingress {
    description = "SSH from allowed IPs"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = length(var.allowed_ips) > 0 ? var.allowed_ips : ["0.0.0.0/0"]
  }

  # MySQL (for external access if needed - be careful!)
  ingress {
    description = "MySQL from allowed IPs (optional)"
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = var.allow_external_mysql ? var.allowed_ips : []
  }

  # Redis (for external access if needed - be careful!)
  ingress {
    description = "Redis from allowed IPs (optional)"
    from_port   = 6379
    to_port     = 6379
    protocol    = "tcp"
    cidr_blocks = var.allow_external_redis ? var.allowed_ips : []
  }

  # All outbound
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "${var.environment}-fitfab-demo-sg"
  }
}
