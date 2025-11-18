# FitFab - Gerador de Projetos de Móveis SaaS

Sistema SaaS para geração automatizada de projetos de móveis personalizados com cutlist otimizada, marketplace de profissionais e gestão de pedidos.

## Visão Geral

FitFab permite que usuários:
- Criem projetos de móveis através de templates e formulários
- Gerem automaticamente cutlist otimizada e layouts de corte
- Exportem arquivos técnicos (SVG/DXF/PDF/CSV)
- Solicitem orçamentos para profissionais locais
- Gerenciem pedidos e pagamentos

## Stack Tecnológica

- **Backend**: Laravel 12+ (PHP 8.2+)
- **Banco de Dados**: PostgreSQL 15
- **Cache/Queue**: Redis 7
- **Storage**: MinIO (dev) / AWS S3 (prod)
- **Queue Monitoring**: Laravel Horizon
- **Autenticação**: Laravel Sanctum
- **Containerização**: Docker + Docker Compose

## Estrutura do Banco de Dados

### Tabelas Principais

- `users` - Usuários do sistema (clientes, profissionais, admin)
- `templates` - Templates de móveis (armários, prateleiras, etc)
- `materials` - Materiais disponíveis (MDF, MDP, etc)
- `projects` - Projetos criados pelos usuários
- `pieces` - Peças individuais de cada projeto
- `sheets` - Chapas com layout de corte otimizado
- `professionals` - Profissionais cadastrados (marceneiros, serrarias)
- `offers` - Orçamentos oferecidos por profissionais
- `orders` - Pedidos e pagamentos
- `files` - Arquivos gerados (PDF, SVG, DXF, CSV)

## Instalação e Configuração

### Pré-requisitos

- Docker 20+
- Docker Compose 2+
- Git

### Setup do Ambiente de Desenvolvimento

1. Clone o repositório:
```bash
git clone <repository-url>
cd FitFab
```

2. Copie o arquivo de ambiente:
```bash
cp .env.example .env
```

3. Inicie os containers Docker:
```bash
docker-compose up -d
```

4. Instale as dependências:
```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

5. Execute as migrations:
```bash
docker-compose exec app php artisan migrate
```

6. Gere a chave da aplicação:
```bash
docker-compose exec app php artisan key:generate
```

7. Execute os seeders (opcional):
```bash
docker-compose exec app php artisan db:seed
```

### Acessando a Aplicação

- **API**: http://localhost:8000
- **Horizon Dashboard**: http://localhost:8000/horizon
- **MinIO Console**: http://localhost:9001

### Credenciais Padrão (Dev)

**Banco de Dados PostgreSQL:**
- Host: localhost:5432
- Database: fitfab
- Username: fitfab
- Password: fitfab

**MinIO:**
- Console: http://localhost:9001
- Access Key: minioadmin
- Secret Key: minioadmin

**Redis:**
- Host: localhost:6379

## Comandos Úteis

### Artisan

```bash
# Limpar cache
php artisan cache:clear

# Executar queue worker
php artisan queue:work

# Executar Horizon
php artisan horizon

# Criar migration
php artisan make:migration create_table_name

# Criar model
php artisan make:model ModelName

# Criar controller
php artisan make:controller ControllerName

# Executar testes
php artisan test
```

### Docker

```bash
# Iniciar containers
docker-compose up -d

# Parar containers
docker-compose down

# Ver logs
docker-compose logs -f app

# Acessar container
docker-compose exec app bash

# Rebuild containers
docker-compose up -d --build
```

## Arquitetura

### Serviços de Domínio

- `ProjectBuilderService` - Criação e processamento de projetos
- `CutlistService` - Otimização de corte (algoritmo guillotine)
- `SheetLayoutService` - Geração de layouts SVG
- `FileExportService` - Exportação de arquivos (PDF/DXF/CSV)
- `PricingService` - Cálculo de preços e estimativas
- `MarketplaceService` - Matching de profissionais por geolocalização
- `NotificationService` - Notificações (email, SMS)

### Jobs Assíncronos

- `GenerateProjectJob` - Gera peças, cutlist, layouts e arquivos
- `RenderPdfJob` - Renderiza PDF a partir de HTML
- `NotifyProsJob` - Notifica profissionais sobre novas solicitações
- `ProcessPaymentJob` - Processa pagamentos pós-checkout

### API Endpoints (Principais)

#### Autenticação
- `POST /api/v1/auth/register` - Registro de usuário
- `POST /api/v1/auth/login` - Login
- `POST /api/v1/auth/logout` - Logout
- `POST /api/v1/auth/refresh` - Refresh token

#### Projetos
- `POST /api/v1/projects` - Criar projeto
- `GET /api/v1/projects/{id}` - Detalhes do projeto
- `GET /api/v1/projects/{id}/download?type=pdf` - Download de arquivo

#### Marketplace
- `GET /api/v1/professionals?lat=&lng=&radius=` - Buscar profissionais
- `POST /api/v1/offers` - Criar orçamento
- `POST /api/v1/offers/{id}/accept` - Aceitar orçamento

#### Pedidos
- `POST /api/v1/orders/{id}/pay` - Iniciar pagamento
- `POST /api/v1/webhooks/payment` - Webhook de pagamento

## Desenvolvimento

### Estrutura de Pastas

```
app/
├── Domain/              # Lógica de domínio
│   ├── Projects/
│   ├── Templates/
│   ├── Professionals/
│   └── Orders/
├── Services/            # Serviços de aplicação
├── Jobs/                # Jobs assíncronos
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/              # Eloquent Models
└── Policies/            # Authorization Policies

database/
├── migrations/          # Database migrations
├── seeders/            # Database seeders
└── factories/          # Model factories

tests/
├── Unit/               # Testes unitários
├── Feature/            # Testes de integração
└── E2E/                # Testes end-to-end
```

### Testes

```bash
# Executar todos os testes
php artisan test

# Executar testes específicos
php artisan test --filter ProjectTest

# Executar com coverage
php artisan test --coverage
```

### Code Quality

```bash
# Laravel Pint (formatter)
./vendor/bin/pint

# PHPStan (análise estática)
./vendor/bin/phpstan analyse
```

## Deployment

### Produção (AWS)

A aplicação está configurada para deploy em:
- **Compute**: AWS ECS / Fargate
- **Database**: AWS RDS (PostgreSQL)
- **Cache**: AWS ElastiCache (Redis)
- **Storage**: AWS S3
- **CDN**: CloudFront

### CI/CD

GitHub Actions configurado para:
- Executar testes em cada PR
- Build da imagem Docker
- Deploy automático em staging/production

## Segurança

- Autenticação via Laravel Sanctum com tokens
- Políticas de autorização (Policies)
- Validação de dados em todos os endpoints
- Rate limiting configurado
- Criptografia de dados sensíveis
- CORS configurado
- Compliance LGPD

## Licença

Proprietário - Todos os direitos reservados

## Contato

Para questões técnicas, abra uma issue no repositório.
