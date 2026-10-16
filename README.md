# Quiz Funnel Builder SaaS

A complete, production-ready, multi-tenant SaaS application built with **Core PHP 8.2+**, **MySQL 8+**, **HTML5**, **Tailwind CSS**, and an interactive **Vanilla JavaScript + SVG** visual branching funnel builder.

---

## 🌟 Key Features

1. **Multi-Tenancy & Data Isolation:**
   - Isolated customer workspaces with role-based access control (`owner`, `admin`, `editor`, `analyst`).
   - Tenant-scoped repository access with complete IDOR defense.
   - Platform Super Administrator panel for managing tenants, plans, and system audit logs.

2. **Visual Branching Logic Builder:**
   - Drag-and-drop canvas supporting pan, zoom, fit-to-view, and node repositioning.
   - Distinct node types: **Start Node**, **Question Node** (with option handles), and **Destination Node** (terminal landing page).
   - Dynamic SVG Bezier curve connector wires.
   - Real-time inspector panel for configuring question text, types, options, and option routing targets.
   - Flow validation engine (detects missing routes, unreachable nodes, invalid URLs).
   - In-editor live quiz simulation preview.

3. **Pre-Quiz Form Builder:**
   - Configurable lead capture form shown before branching questions.
   - Supports text, email, phone, numbers, dropdown selects, and consent checkboxes.
   - Snapshotting mechanism preserves historical lead labels and keys even if form definitions change later.

4. **Server-Validated Branching Quiz Engine:**
   - Server-authoritative routing: the server calculates the next question or destination from published graph edges.
   - Safe back navigation: changing an earlier answer automatically invalidates/supersedes downstream answers, recalculating the path cleanly.
   - Idempotent submission protection.

5. **Submission & Journey Inspector:**
   - Detailed admin view of each lead's submission.
   - Chronological event timeline (`quiz_viewed`, `pre_form_submitted`, `quiz_started`, `branch_selected`, `quiz_completed`).
   - Sequential answer trail displaying exact questions visited and choices selected.
   - CSV export functionality.

6. **Real-Time Analytics:**
   - Derived strictly from real database events: Views, Form Starts, Quiz Starts, Completions, Drop-off progression by question, and Final Destination distribution.

---

## 🚀 Local Development on XAMPP (Windows)

### Prerequisites
- **XAMPP** running Apache and MySQL (`127.0.0.1:3306`).
- **PHP 8.2+** with `pdo_mysql`, `mbstring`, `json`, `openssl`, `session`.

### 1. Database Setup & Migration
Run the automated migration runner:
```powershell
cd C:\xampp\htdocs\qfunnel
php database/migrate.php
```

### 2. Seed Development Data
Seed initial plans, administrator, demo workspace, and the Medical/Doctor branching funnel:
```powershell
php database/seed.php
```

### 3. Run Automated Tests
Execute the automated test suite verifying multi-tenancy, graph validation, and branching logic:
```powershell
php tests/run_tests.php
```

---

## 🌐 Application URLs

| Screen | URL |
|---|---|
| **Sign In** | `http://localhost/qfunnel/login` |
| **Workspace Dashboard** | `http://localhost/qfunnel/admin/dashboard` |
| **Funnels List** | `http://localhost/qfunnel/admin/funnels` |
| **Visual Builder** | `http://localhost/qfunnel/admin/funnels/1/builder` |
| **Funnel Settings & Form** | `http://localhost/qfunnel/admin/funnels/1/settings` |
| **Submissions & Leads** | `http://localhost/qfunnel/admin/submissions` |
| **Journey Inspector** | `http://localhost/qfunnel/admin/submissions/1` |
| **Analytics** | `http://localhost/qfunnel/admin/analytics` |
| **Workspace Settings** | `http://localhost/qfunnel/admin/settings/workspace` |
| **Platform Super Admin** | `http://localhost/qfunnel/admin/platform` |
| **Public Quiz Experience** | `http://localhost/qfunnel/quiz/doctor-referral` |

### Default Credentials
- **Tenant Owner:** `owner@qfunnel.local` / `Password123!`
- **Super Administrator:** `superadmin@qfunnel.local` / `Password123!`
- **Team Editor:** `editor@qfunnel.local` / `Password123!`

---

## 📁 Architecture Overview

```text
qfunnel/
├── app/
│   ├── Controllers/     # MVC Controllers (Auth, Dashboard, Funnel, Builder, Quiz, etc.)
│   ├── Core/            # Framework Foundation (Router, Database, Session, Request, View, Logger)
│   ├── Middleware/      # Auth, TenantContext, PlatformAdmin, CSRF
│   ├── Services/        # Business Logic (QuizEngine, FunnelGraph, Publish, FormSchema, Analytics)
│   └── Views/           # HTML5 + Tailwind CSS templates & layouts
├── config/              # App and database configuration
├── database/
│   ├── migrations/      # Versioned MySQL migrations
│   ├── seeders/         # Development seeders
│   ├── migrate.php      # Migration execution script
│   └── seed.php         # Seeding execution script
├── docs/                # Architecture, design specs, security & deployment guides
├── public/              # Web-accessible document root
│   ├── assets/          # CSS, JS, SVG assets
│   ├── index.php        # Application entrypoint
│   └── .htaccess        # Front-controller rewrite rules
├── routes/              # Web and API routing definitions
├── storage/             # Application logs & cache
├── tests/               # Automated test suites
├── .env.example         # Environment template
└── README.md
```
