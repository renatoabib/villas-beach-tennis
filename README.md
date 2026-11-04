# 🎾 Villas Beach Tennis — Plataforma de Gestão do Clube

Plataforma web para gestão do **Villas Beach Tennis**, construída em **Laravel**.
Reúne cadastro de sócios, mensalidades, reuniões, documentos, eventos, despesas
e estatísticas em um único painel, com controle de acesso por perfil.

---

## 🚀 Funcionalidades

### 🔐 Controle de acesso por perfil
Cada perfil tem permissões próprias:
- **Super Admin** – acesso total ao sistema
- **Admin** – administra o clube (CRUD)
- **Diretoria (Board)** – supervisiona sem permissão de exclusão
- **Supervisor** – cadastra sócios, reuniões, eventos e documentos
- **Sócio (Member)** – acessa o próprio perfil, reuniões e pagamentos

### 🧩 Módulos

| Módulo           | Descrição                                              |
|------------------|--------------------------------------------------------|
| 👥 Sócios         | Cadastro/edição, foto, atribuição de perfis             |
| 💰 Mensalidades   | Registro de mensalidades por sócio e competência        |
| 📅 Reuniões       | Agendamento com atas e arquivos                         |
| 📁 Documentos      | Anexos via Spatie MediaLibrary                          |
| 🎉 Eventos         | Criação e gestão de eventos do clube                    |
| 📊 Estatísticas    | Indicadores com ApexCharts                              |
| 💸 Despesas        | Controle de despesas e orçamento                        |
| 🔐 Permissões      | Controle fino com Spatie Permission                     |

---

## ⚙️ Stack

- **Framework**: Laravel 10+
- **Frontend**: Blade, Bootstrap 5, Animate.css
- **Gráficos**: ApexCharts.js
- **Uploads**: Spatie MediaLibrary
- **Autenticação**: Laravel Auth (cadastro público desativado)
- **Perfis/Permissões**: Spatie Laravel-Permission
- **Banco de dados**: MySQL
- **Deploy**: Docker Compose + Nginx (Coolify)

---

## 💻 Instalação local

```bash
# 1. Clone o projeto
git clone git@github.com:renatoabib/villas-beach-tennis.git
cd villas-beach-tennis

# 2. Instale as dependências
composer install

# 3. Configure o ambiente
cp .env.example .env
php artisan key:generate

# 4. Ajuste o .env (banco, e-mail, APP_URL)

# 5. Migre e popule o banco
php artisan migrate

# 6. Link do storage (fotos e documentos)
php artisan storage:link

# 7. Assets do frontend
npm install && npm run build

# 8. Suba o servidor local
php artisan serve
```

---

## 🐳 Deploy com Docker (Coolify)

O projeto inclui `docker-compose.yml` com três serviços:

- **app** – PHP-FPM + Laravel
- **db** – MySQL 8
- **nginx** – servidor web (porta 80, SSL pelo proxy do Coolify)

Variáveis principais no painel do Coolify:

```
APP_NAME="Villas Beach Tennis"
APP_URL=https://villas.rcmasters.com.br
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

---

## 📈 Dashboards

Cada perfil vê um painel próprio:
- **Admin**: sócios, mensalidades, reuniões, maiores contribuintes
- **Super Admin**: visão geral de todos os clubes
- **Diretoria**: indicadores e resumos para decisão
- **Sócio**: dados próprios, participação em reuniões e histórico de pagamentos

---

## 📄 Licença

Uso interno do Villas Beach Tennis.
