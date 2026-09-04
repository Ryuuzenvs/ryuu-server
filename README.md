
# 🚀 Ryuu Server Explorer v2

**Ryuu Server Explorer v2** adalah platform manajemen *local server* personal yang didesain modular, *lightweight*, dan *scalable*. Dikembangkan menggunakan arsitektur **Hybrid Laravel + Go Microservice**, projek ini memisahkan antara layer *Web Interface/Authentication* dengan *System Monitoring & Real-time Terminal*.

---

## 📌 Technical Stack & Specs

* **Core Web & Dashboard Framework:** Laravel 11.x (PHP 8.2+)
* **Microservices (Monitor & PTY Terminal):** Go (Golang)
* **Real-time Communication:** WebSockets (`gorilla/websocket`) + Xterm.js
* **Database:** MariaDB / MySQL
* **Frontend Layer:** Blade Templating + Alpine.js + Bootstrap 5.3 / Tailwind (Support UI Toggle Mode)
* **WebServer & Proxy:** Apache2 / Nginx + Cloudflare Tunnels (Remote/Portofolio Access)
* **Host OS Context:** Linux Mint XFCE Edition (Low-spec optimized)

---

## 🏗 System Architecture


```

```
                   ┌─────────────────────────┐
                   │    Cloudflare Tunnel    │
                   └────────────┬────────────┘
                                │
                                ▼
                   ┌─────────────────────────┐
                   │     Apache / Nginx      │
                   └──────┬─────────────┬────┘
                          │             │
    (HTTP / Web Traffic)  │             │  (WebSocket / WS)
                          ▼             ▼
         ┌──────────────────┐         ┌──────────────────────────────┐
         │  Laravel Core    │         │     Go Engine Microservice   │
         │  (Port 80/443)   │         │     (Port 8080 / 8081)       │
         └────────┬─────────┘         └──────────────┬───────────────┘
                  │                                  │
  ┌───────────────┴───────────────┐     ┌────────────┴─────────────┐
  │ • Auth & Role (Owner/Guest)   │     │ • Real-time System Stats │
  │ • File Manager CRUD           │     │ • PTY Terminal Server    │
  │ • UI Rendering (Simple/Mewah) │     │   (Interactive 2-Way)    │
  └───────────────────────────────┘     └──────────────────────────┘

```

```

---

## 📁 Directory Structure Overview

```text
/var/www/html/
├── v2-server/                 # Folder Utama Projek Baru
│   ├── app/                   # Laravel Controllers, Models, Middlewares
│   ├── resources/views/       # Blade Components & Layouts
│   ├── go-engine/             # Go Microservices
│   │   ├── monitor/           # System Metrics Collector
│   │   │   └── main.go
│   │   └── terminal/          # PTY Websocket Terminal
│   │       └── main.go
│   └── .env                   # Configuration File
├── index_legacy               # Backup index.php lama
└── run.sh                     # System Runner Script

```

---

## ⚙️ Core Features & Requirements

### 1. Adaptive UI Mode (Simple vs Mewah)

* Switchable via `.env` parameter: `APP_UI_MODE=simple` or `APP_UI_MODE=mewah`.
* **Simple Mode:** *Zero heavy scripts*, CSS minimalis, super kencang untuk ngoding harian.
* **Mewah Mode:** Load 3D Vanta.js / Canvas FX untuk kebutuhan *showcase* & portofolio.

### 2. Multi-Device & Role-Based Access Control (RBAC)

* **Role `Owner`:** Full privileges (File Manager CRUD, Execute Scripts, Terminal Input/Output, DB Admin).
* **Role `Guest`:** Read-only mode untuk publik/portofolio (View System Stats & File Structure dengan masking data sensitif).

### 3. File Manager Layer

* Integrasi *Laravel Flysystem* dengan akses root terbatas di lingkup permission server.
* Proteksi *Directory Traversal* bawaan framework.

### 4. Interactive 2-Way Terminal (Xterm.js + Go PTY)

* Terminal interaktif penuh di browser (dukungan perintah `cd`, `pm2`, `git`, `htop`, dll).
* Komunikasi duplex via WebSockets langsung ke pty Linux local server.

---

## 🌐 API & Websocket Endpoints

### Go Engine Services (Port 8080 & 8081)

| Endpoint | Protocol | Auth Required | Description |
| --- | --- | --- | --- |
| `ws://localhost:8080/ws/system` | WebSocket | Token/Session | Stream CPU, RAM, Temp, Battery metrics |
| `ws://localhost:8081/ws/terminal` | WebSocket | Owner Token | Interactive PTY Session Stream |

### Laravel Internal API / Routes

| Route | Method | Middleware | Description |
| --- | --- | --- | --- |
| `/login` | GET/POST | Guest | Form & Authenticate user |
| `/dashboard` | GET | Auth | Rendering Utama Dashboard |
| `/api/files` | GET | Auth | Get File List |
| `/api/files/action` | POST | Auth + Owner Role | File Manager Operations (CRUD) |

---

## 🚀 How to Run & Migration Steps

### Step 1: Backup Legacy Project

```bash
cd /var/www/html
mv index.php index_legacy

```

### Step 2: Setup Laravel Environment

```bash
cd /var/www/html
composer create-project laravel/laravel v2-server
cd v2-server
php artisan key:generate

```

### Step 3: Setup Go Engine Microservice

```bash
cd /var/www/html/v2-server/go-engine
# Init modules
go mod init ryuu-server-engine
go get [github.com/gorilla/websocket](https://github.com/gorilla/websocket)
go get [github.com/creack/pty](https://github.com/creack/pty)

```

### Step 4: Environment Configuration (`.env`)

```env
APP_NAME="Ryuu Local Server"
APP_ENV=local
APP_UI_MODE=simple # 'simple' atau 'mewah'

# Database Setup
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ryuu_server
DB_USERNAME=root
DB_PASSWORD=

```

---

## 📈 Roadmap Development Phase

* [ ] **Phase 1:** Setup Folder `/v2-server/`, Konfigurasi Routing Apache & Routing Laravel Dasar.
* [ ] **Phase 2:** Integrasi Database & User Role Authentication (Owner vs Guest).
* [ ] **Phase 3:** Develop Go Microservice untuk System Monitor & WebSockets.
* [ ] **Phase 4:** Develop Interactive PTY Terminal (Go + Xterm.js Frontend).
* [ ] **Phase 5:** Refactor File Manager dengan Laravel Storage Layer.
* [ ] **Phase 6:** Testing UI Mode Toggle & Cloudflare Tunnel Deployment.