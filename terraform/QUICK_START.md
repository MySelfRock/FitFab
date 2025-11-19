# FitFab - Guia Rápido de Deploy

## 🚀 Deploy Rápido - Ambiente Demo (5 minutos)

### Pré-requisitos
- AWS CLI configurada (`aws configure`)
- Terraform instalado
- Par de chaves SSH criado na AWS

### Passo a Passo

```bash
# 1. Entre no diretório demo
cd terraform/demo

# 2. Copie o arquivo de exemplo
cp terraform.tfvars.example terraform.tfvars

# 3. Edite as configurações
nano terraform.tfvars
# Mude: key_name = "sua-chave-ssh"
# Mude: db_password = "SenhaForte123!"

# 4. Inicialize o Terraform
terraform init

# 5. Valide a configuração
terraform validate

# 6. Visualize o que será criado
terraform plan

# 7. Crie a infraestrutura
terraform apply -auto-approve

# 8. Aguarde 5-10 minutos e acesse
# O IP será mostrado nos outputs
terraform output application_url
```

### Primeiro Acesso

```bash
# Pegue o IP da instância
IP=$(terraform output -raw instance_public_ip)

# Acesse no navegador
echo "http://$IP"

# Ou conecte via SSH
ssh -i sua-chave.pem ubuntu@$IP
```

### Deploy da Aplicação

```bash
# Conectado via SSH, execute:

# 1. Clone o repositório
cd /var/www
sudo rm -rf fitfab
sudo git clone https://github.com/seu-usuario/fitfab.git fitfab
cd fitfab

# 2. Instale dependências
sudo -u www-data composer install --no-dev
sudo -u www-data npm install && npm run build

# 3. Configure .env
sudo cp .env.example .env
sudo nano .env
# Configure DB_PASSWORD com a senha que você definiu

# 4. Gere a chave da aplicação
sudo -u www-data php artisan key:generate

# 5. Rode as migrations
sudo -u www-data php artisan migrate --seed

# 6. Configure permissões
sudo chown -R www-data:www-data /var/www/fitfab
sudo chmod -R 755 /var/www/fitfab
sudo chmod -R 775 /var/www/fitfab/storage
sudo chmod -R 775 /var/www/fitfab/bootstrap/cache

# 7. Inicie os workers
sudo supervisorctl start laravel-worker:*
sudo supervisorctl start laravel-horizon

# 8. Reinicie os serviços
sudo systemctl restart php8.2-fpm nginx
```

## 🏢 Deploy Completo - Ambiente de Produção

### Pré-requisitos Adicionais
- Domínio registrado
- Certificado SSL no ACM (AWS Certificate Manager)
- Planejamento de capacidade

### Passo a Passo

```bash
# 1. Entre no diretório production
cd terraform/production

# 2. Copie o arquivo de exemplo
cp terraform.tfvars.example terraform.tfvars

# 3. Edite TODAS as configurações
nano terraform.tfvars
# Configure:
# - domain_name
# - certificate_arn (do ACM)
# - db_username e db_password
# - app_key (gere com: php artisan key:generate --show)
# - alarm_email
# - allowed_ssh_ips

# 4. Inicialize o Terraform
terraform init

# 5. Crie um plano
terraform plan -out=tfplan

# 6. Revise o plano cuidadosamente
terraform show tfplan

# 7. Aplique (isso levará ~15-20 minutos)
terraform apply tfplan

# 8. Configure o DNS
# Outputs mostrarão o ALB DNS name
# Crie um CNAME no seu DNS apontando para o ALB
```

### Configuração Pós-Deploy

```bash
# 1. Conecte ao bastion
BASTION_IP=$(terraform output -raw bastion_public_ip)
ssh -i sua-chave.pem ubuntu@$BASTION_IP

# 2. Do bastion, conecte a uma instância privada
# (use AWS Systems Manager Session Manager como alternativa)

# 3. Configure a aplicação usando os outputs
terraform output laravel_env_vars > .env.production

# 4. Deploy via CI/CD ou manualmente
```

## 📊 Monitoramento

### Demo
```bash
# Conecte via SSH e rode:
cd /var/www/fitfab
bash <(curl -s https://raw.githubusercontent.com/seu-repo/scripts/check_health.sh)
```

### Produção
- CloudWatch Dashboard: AWS Console
- Logs: CloudWatch Logs `/aws/ec2/production-fitfab`
- Alertas: Configurados via SNS

## 🔄 Atualização da Aplicação

### Demo
```bash
# Via SSH
cd /var/www/fitfab
sudo -u www-data git pull
sudo bash /path/to/deploy_application.sh demo
```

### Produção
Use CI/CD pipeline (GitHub Actions, GitLab CI, etc.) ou:

```bash
# Via bastion, execute em cada instância do ASG
# Recomendado: usar AWS Systems Manager Run Command
aws ssm send-command \
  --document-name "AWS-RunShellScript" \
  --targets "Key=tag:Environment,Values=production" \
  --parameters 'commands=["/path/to/deploy_application.sh production"]'
```

## 🗑️ Destruir Infraestrutura

```bash
# CUIDADO! Isso remove TUDO

# Demo
cd terraform/demo
terraform destroy

# Produção (verifique backups primeiro!)
cd terraform/production
terraform destroy
```

## ⚠️ Troubleshooting

### A aplicação não está acessível
```bash
# Verifique os serviços
sudo systemctl status nginx php8.2-fpm

# Verifique logs
sudo tail -f /var/log/nginx/error.log
sudo journalctl -u php8.2-fpm -f
```

### Banco de dados não conecta
```bash
# Teste a conexão
mysql -u fitfab -p fitfab
# ou
psql -h <host> -U <user> -d fitfab

# Verifique o .env
cat /var/www/fitfab/.env | grep DB_
```

### Workers não estão processando
```bash
# Verifique supervisor
sudo supervisorctl status

# Reinicie workers
sudo supervisorctl restart laravel-worker:*
sudo supervisorctl restart laravel-horizon

# Verifique logs
sudo tail -f /var/log/laravel-worker.log
```

## 📞 Suporte

- Documentação completa: `terraform/README.md`
- AWS: https://docs.aws.amazon.com
- Terraform: https://registry.terraform.io/providers/hashicorp/aws/latest/docs
