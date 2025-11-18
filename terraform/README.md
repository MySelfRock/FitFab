# FitFab - Infraestrutura AWS com Terraform

Este repositório contém a infraestrutura como código (IaC) para o sistema FitFab usando Terraform na AWS.

## 📋 Pré-requisitos

- [Terraform](https://www.terraform.io/downloads.html) >= 1.0
- AWS CLI configurada com credenciais válidas
- Chave SSH para acesso às instâncias EC2

## 🏗️ Arquiteturas Disponíveis

### 1. Ambiente de Produção (production/)

Arquitetura completa e escalável com as melhores práticas da AWS:

**Componentes:**
- VPC com subnets públicas e privadas em 3 AZs
- Application Load Balancer (ALB) com HTTPS
- Auto Scaling Group (2-10 instâncias)
- RDS PostgreSQL Multi-AZ
- ElastiCache Redis cluster
- S3 + CloudFront para assets estáticos
- Route53 para DNS
- Secrets Manager para credenciais
- CloudWatch para monitoring
- Backup automático

**Custo estimado:** ~$300-500/mês

### 2. Ambiente de Demonstração (demo/)

Arquitetura simples em uma única instância EC2:

**Componentes:**
- 1 instância EC2 t3.medium
- MySQL local
- Redis local
- Elastic IP
- Security Group básico

**Custo estimado:** ~$30-50/mês

## 🚀 Como Usar

### Ambiente de Produção

```bash
cd terraform/production

# 1. Copiar e configurar variáveis
cp terraform.tfvars.example terraform.tfvars
# Edite terraform.tfvars com suas configurações

# 2. Inicializar Terraform
terraform init

# 3. Validar configuração
terraform validate

# 4. Planejar mudanças
terraform plan

# 5. Aplicar infraestrutura
terraform apply

# 6. Obter outputs (URLs, IPs, etc)
terraform output
```

### Ambiente de Demonstração

```bash
cd terraform/demo

# 1. Copiar e configurar variáveis
cp terraform.tfvars.example terraform.tfvars
# Edite terraform.tfvars com suas configurações

# 2. Inicializar Terraform
terraform init

# 3. Aplicar infraestrutura
terraform apply

# 4. Conectar via SSH
ssh -i sua-chave.pem ubuntu@$(terraform output -raw instance_ip)
```

## 📝 Configuração Pós-Deploy

### Produção

Após o deploy, execute:

```bash
# SSH para uma instância do ASG via bastion
ssh -i sua-chave.pem ubuntu@bastion-ip

# Configurar aplicação (já feito automaticamente no user_data)
# Verificar logs
sudo journalctl -u fitfab -f
```

### Demo

```bash
# SSH para a instância
ssh -i sua-chave.pem ubuntu@instance-ip

# A aplicação estará disponível em:
http://instance-ip

# Logs
sudo journalctl -u fitfab -f
```

## 🔧 Variáveis Importantes

### Produção

- `environment` - Nome do ambiente (production)
- `aws_region` - Região AWS (us-east-1)
- `domain_name` - Domínio principal (ex: fitfab.com)
- `db_username` - Usuário do banco de dados
- `db_password` - Senha do banco de dados
- `app_key` - Laravel APP_KEY

### Demo

- `environment` - Nome do ambiente (demo)
- `aws_region` - Região AWS
- `instance_type` - Tipo da instância EC2 (t3.medium)
- `key_name` - Nome da chave SSH na AWS
- `allowed_ips` - IPs permitidos para acesso SSH

## 🗑️ Destruir Infraestrutura

```bash
# CUIDADO: Isso vai destruir TUDO!
terraform destroy
```

## 📊 Monitoramento

### Produção
- CloudWatch Dashboard: Acessível via AWS Console
- Logs: CloudWatch Logs
- Alertas: SNS + CloudWatch Alarms

### Demo
- Logs locais: `sudo journalctl -u fitfab`
- Monitoramento básico: `htop`, `df -h`

## 🔒 Segurança

- Todas as senhas devem estar no Secrets Manager (produção)
- Usar variáveis de ambiente (demo)
- Nunca commitar `terraform.tfvars` com dados sensíveis
- Usar `.gitignore` para excluir arquivos sensíveis

## 💰 Custos

### Produção (mensal)
- ALB: ~$25
- EC2 (2x t3.medium): ~$60
- RDS (db.t3.medium): ~$70
- ElastiCache (cache.t3.micro): ~$15
- S3 + CloudFront: ~$20
- Outros: ~$10
- **Total: ~$200-300/mês**

### Demo (mensal)
- EC2 (1x t3.medium): ~$30
- EBS: ~$10
- **Total: ~$40/mês**

## 📞 Suporte

Para questões sobre a infraestrutura, consulte a documentação da AWS ou abra uma issue.
