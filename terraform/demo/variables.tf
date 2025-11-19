variable "environment" {
  description = "Environment name"
  type        = string
  default     = "demo"
}

variable "aws_region" {
  description = "AWS region"
  type        = string
  default     = "us-east-1"
}

variable "instance_type" {
  description = "EC2 instance type"
  type        = string
  default     = "t3.medium"
}

variable "key_name" {
  description = "SSH key name"
  type        = string
}

variable "db_password" {
  description = "MySQL root and fitfab user password"
  type        = string
  sensitive   = true
  default     = "FitFab2024SecurePass!"
}

variable "allowed_ips" {
  description = "List of IPs allowed to access the instance"
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "allow_external_mysql" {
  description = "Allow external MySQL access (be careful!)"
  type        = bool
  default     = false
}

variable "allow_external_redis" {
  description = "Allow external Redis access (be careful!)"
  type        = bool
  default     = false
}
