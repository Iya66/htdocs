# Cloud Aspire (CherriDM) — Complete Project Architecture & Technical Report

> **Document:** Project Technical Specification & Architecture Report  
> **Target Project:** Cloud Aspire Digital Marketing Portal & SaaS CMS  
> **Primary Domains:** `https://cloudaspire.in` | `https://cloudaspiresolutions.in`  
> **Repository Path:** `c:\xampp\htdocs`  
> **Generated Date:** October 2026  
> **Status:** Production-Ready / Fully Documented  

---

## 1. Executive Summary

**Cloud Aspire** (CherriDM Digital Marketing Portal) is an enterprise-grade digital marketing agency website, customer acquisition platform, and administrative Content Management System (CMS) / Customer Relationship Management (CRM) portal.

Designed for high conversion velocity and modern digital marketing performance, the portal delivers:
- **Public-Facing Digital Experience:** High-converting landing pages, animated service showcases, interactive pricing tiers, dynamic case study portfolios, verified customer reviews, SEO-optimized blog publication, and multi-channel lead capture modals.
- **Enterprise SaaS Admin Dashboard:** Role-based access control (RBAC), multi-stage lead pipeline CRM, rich content management modules (services, blogs, projects, testimonials, FAQs), real-time notification badges, live audit logging, database backup automation, and global system configuration.
- **High Deliverability Communication Engine:** Asynchronous database-backed email queueing, PHPMailer 7.1 with exponential backoff and DNS IPv4 fallback, and instant WhatsApp business notifications.
- **Hardened Security Architecture:** Cross-Site Request Forgery (CSRF) defenses, 30-minute inactivity session invalidation, AES-256-CBC encryption at rest for sensitive credentials, audit trail tracking with device telemetry, and server-level `.htaccess` protection.

---

## 2. Technology Stack & Runtime Specifications

| Layer | Technology | Version / Specification | Role in System |
|---|---|---|---|
| **Core Runtime** | PHP | 8.2+ / 8.4+ (`ea-php84` on cPanel, PHP 8.x in local XAMPP) | Server-side execution, routing, security, business logic |
| **Database** | MySQL / MariaDB | MySQL 5.7+ / MariaDB 10.3+ (InnoDB, `utf8mb4_unicode_ci`) | Relational persistence, transactional integrity |
| **Web Server** | Apache HTTP Server | 2.4+ (`mod_rewrite`, `mod_deflate`, `mod_headers`, `mod_expires`) | URL rewriting, caching, compression, request filtering |
| **Backend Dependencies** | Composer | `phpmailer/phpmailer ^7.1` | Transactional email transmission via SMTP |
| **Frontend Styling** | Vanilla CSS3 | Custom CherriDM Theme, Glassmorphism, CSS Grid & Flexbox | Responsive layout, modern animations, zero-overhead CSS |
| **UI Components & Icons** | FontAwesome 6, Boxicons, LordIcon | CDN / Local Asset Bundles | Vector icons, interactive micro-animations |
| **Typography** | Google Fonts | Inter, Roboto, Outfit | High-readability corporate typography |
| **Client Scripting** | Vanilla JavaScript (ES6+) | Native Fetch API, Event Listeners, Swiper Slider | Asynchronous form submissions, slider components, live search |

---

## 3. System Architecture & Design Pattern

The application implements a **Front-Controller and View-Presenter Hybrid Architecture**, decoupling business logic, database queries, and HTML view rendering without heavyweight framework overhead.

```
                              [ Incoming HTTP Request ]
                                         │
                                   [.htaccess]
                        (Rewriting, Security Headers, Caching)
                                         │
                 ┌───────────────────────┴───────────────────────┐
                 ▼                                               ▼
     [Public Frontend Controller]                    [Admin Route Controller]
         (e.g., home.php,                               (e.g., admin/leads.php,
       service-detail.php)                             admin/dashboard.php)
                 │                                               │
                 ├───────────────────────────────┐               ▼
                 ▼                               ▼        [admin/auth.php]
        [app/config.php]                  [app/database.php]  (Session & RBAC Guard)
    (Session, CSRF, Security)              (PDO Connection)              │
                 │                               │                       │
                 └───────────────┬───────────────┘                       │
                                 ▼                                       │
                      [Database Query Execution]                         │
                                 │                                       │
                                 ▼                                       ▼
                   [app/Views/frontend/*.php]               [app/Views/admin/*.php]
                    (HTML Presentation Layer)               (Wrapped in Header/Footer Pro)
```

### 3.1 Routing & Clean URL Engine (`.htaccess`)
- Clean, SEO-optimized URLs without `.php` extensions:
  - `/services` $\rightarrow$ `services.php`
  - `/services/{slug}` $\rightarrow$ `service-detail.php?slug={slug}`
  - `/blogs` $\rightarrow$ `blog.php`
  - `/blogs/{slug}` $\rightarrow$ `blog-detail.php?slug={slug}`
  - `/admin/{route}.php` $\rightarrow$ 301 Redirects cleanly to `/admin/{route}`
- Static Asset Caching:
  - Images (`.webp`, `.png`, `.jpg`, `.svg`) & Fonts: `1 Year` public immutable cache.
  - CSS & JS: `1 Month` public immutable cache.
  - HTML, PHP, JSON: `0 seconds` (`must-revalidate, no-cache`).
- Gzip / Deflate compression enabled across text, HTML, CSS, JavaScript, XML, and font assets.

---

## 4. Complete Directory & File Manifest

```
c:\xampp\htdocs\
├── .htaccess                     # Apache master configuration: rewrites, security, compression, caching
├── .gitignore                    # Version control excludes (vendor, .env, backups, runtime logs)
├── .user.ini                     # cPanel CGI / user php configuration overrides
├── php.ini                       # Local PHP runtime overrides
├── README.md                     # Repository overview
├── composer.json                 # Composer dependencies manifest
├── composer.lock                 # Locked vendor dependency tree
├── codebase.ctx                  # Codebase context manifest
├── database.sql                  # Database schema and initial seed data
├── database_migration.sql        # Production-grade idempotent migration script
├── migrate.php                   # Database migration execution utility
├── robots.txt                    # Search engine crawler instructions
├── sitemap.xml                   # XML sitemap for search engines
├── llms.txt                      # AI & LLM content discovery file
├── ai-catalog.json               # Structured AI catalog manifest
│
├── 404.html                      # Static fallback 404 error page
├── 404.php                       # Branded dynamic 404 error page
│
├── [PUBLIC FRONTEND CONTROLLERS]
├── index.php                     # Root entry point (delegates to home.php)
├── home.php                      # Homepage controller (fetches services, reviews, projects, FAQs, stats)
├── services.php                  # Services index controller
├── service-detail.php            # Dynamic single service detail controller (resolved by slug)
├── projects.php                  # Portfolio case studies controller
├── reviews.php                   # Customer reviews & feedback submission controller
├── testimonials.php              # Client testimonials controller
├── why-choose-us.php             # Core value propositions controller
├── contact.php                   # Contact page controller with inquiry form
├── contact-insert.php            # Legacy contact form submission handler
├── footer-form.php               # Footer audit / fast inquiry submission handler
├── blog.php                      # Blog listing controller
├── blog-detail.php               # Single blog post reader controller (resolved by slug)
├── leads.php                     # Public lead capture handler
├── login.php                     # Client/User authentication interface
├── privacy-policy.php            # Privacy Policy view controller
├── refund-policy.php             # Refund Policy view controller
├── terms.php                     # Terms & Conditions view controller
│
├── app/                          # Core Backend Application Engine
│   ├── .env                      # Environment configuration file (DB credentials, SMTP keys)
│   ├── .env.php                  # Environment loader helper
│   ├── .htaccess                 # Direct HTTP denial for sensitive configuration files
│   ├── config.php                # Master bootstrap: sessions, CSRF, security headers, encryption, audit
│   ├── database.php              # Zero-overhead PDO singleton connection layer
│   ├── email_helper.php          # High-level transactional email notification templates
│   ├── smtp_factory.php          # Centralized PHPMailer factory (IPv4 resolution, retry backoff)
│   ├── mail_queue.php            # Mail queue database enqueue/dequeue library
│   ├── queue_worker.php          # Asynchronous mail queue CLI/cron worker daemon
│   ├── lead_statuses.php         # Lead pipeline definitions and state transition rules
│   ├── security_notifier.php     # Admin security alert notification dispatcher
│   ├── whatsapp_helper.php       # WhatsApp message trigger wrapper
│   │
│   ├── helpers/
│   │   └── SiteHelper.php        # Formatting utilities, slug generators, status badges, pagination
│   │
│   ├── services/
│   │   └── WhatsAppService.php   # WhatsApp Business API / Gateway dispatch integration
│   │
│   └── Views/                    # Separated Presentation Layer
│       ├── frontend/             # Frontend HTML/PHP View Templates
│       │   ├── home.php          # Homepage template (Hero, Services, Why Us, Projects, Reviews, FAQs)
│       │   ├── services.php      # Services catalog grid
│       │   ├── service-detail.php# Service single view (features, process, FAQ, booking)
│       │   ├── projects.php      # Portfolio grid with filter tabs
│       │   ├── reviews.php       # Review submission form & testimonials wall
│       │   ├── testimonials.php  # Client testimonial slider & cards
│       │   ├── why-choose-us.php # Why Choose Us presentation
│       │   ├── contact.php       # Contact form, Google map, office info
│       │   ├── blog.php          # Blog article cards grid
│       │   ├── blog-detail.php   # Blog reader view with sidebar & related posts
│       │   ├── privacy-policy.php# Legal text: Privacy Policy
│       │   ├── refund-policy.php # Legal text: Refund Policy
│       │   └── terms.php         # Legal text: Terms of Service
│       │
│       └── admin/                # Admin Panel View Templates
│           ├── accounts.php      # User accounts management view
│           ├── activity.php      # Admin activity logs & audit trail
│           ├── backup.php        # System & database backup control interface
│           ├── blogs-pro.php     # Blog manager (rich editor, category, tags, cover)
│           ├── contact-us.php    # Contact inquiries & messages inbox
│           ├── dashboard-pro.php # Modern SaaS dashboard layout with metrics & charts
│           ├── faqs.php          # FAQ item manager (question, answer, order)
│           ├── files.php         # File manager & media library
│           ├── leads-pro.php     # Leads CRM view (status tracking, filters, quick actions)
│           ├── mail_logs.php     # Outgoing email dispatch status & delivery logs
│           ├── map.php           # Google Map embed & coordinates manager
│           ├── pages.php         # Custom pages manager
│           ├── privacy-policy.php# Privacy policy editor
│           ├── projects.php      # Project & portfolio manager
│           ├── refund-policy.php # Refund policy editor
│           ├── reports.php       # Analytics, lead conversion & inquiry reports
│           ├── reviews.php       # Customer reviews moderation & approval queue
│           ├── services-pro.php  # Service CRUD editor with features & metadata
│           ├── staff.php         # Admin staff & permission management
│           ├── terms-and-conditions.php # Terms editor
│           ├── testimonials.php  # Testimonials CRUD manager
│           ├── why-choose-us.php # Why Choose Us bullet items editor
│           └── settings/
│               ├── index.php     # Settings tab coordinator
│               ├── general.php   # Site name, logo, favicon, contact details
│               ├── appearance.php# Theme selection, colors, typography
│               ├── integration.php # Google Maps API, Analytics, WhatsApp API keys
│               ├── mailsettings.php# SMTP host, port, credentials, test mail tool
│               ├── module.php    # Toggle enable/disable for system modules
│               └── homepage.php  # Homepage hero & stats customizer
│
├── admin/                        # Admin Portal Entry Points & Controllers
│   ├── index.php                 # Admin root (redirects to dashboard or login)
│   ├── login.php                 # Admin authentication login screen
│   ├── logout.php                # Session destruction and logout handler
│   ├── auth.php                  # Authentication guard middleware (30-min idle timeout)
│   ├── auth-google.php           # Google OAuth SSO sign-in handler
│   ├── dashboard.php             # Dashboard controller (aggregates statistics & queries)
│   ├── leads.php                 # Leads CRM controller
│   ├── services.php              # Services manager controller
│   ├── blogs.php                 # Blogs manager controller
│   ├── projects.php              # Projects manager controller
│   ├── reviews.php               # Reviews manager controller
│   ├── testimonials.php          # Testimonials manager controller
│   ├── why-choose-us.php         # Why Choose Us controller
│   ├── faqs.php                  # FAQs manager controller
│   ├── pages.php                 # Pages manager controller
│   ├── contact-us.php            # Contact messages controller
│   ├── accounts.php              # Client accounts controller
│   ├── staff.php                 # Staff & team controller
│   ├── activity.php              # Activity log viewer controller
│   ├── mail_logs.php             # Email delivery logs controller
│   ├── backup.php                # Database backup runner controller
│   ├── map.php                   # Map settings controller
│   ├── reports.php               # Reports controller
│   ├── privacy-policy.php        # Policy editor controller
│   ├── refund-policy.php         # Policy editor controller
│   ├── terms-and-conditions.php  # Terms editor controller
│   ├── files.php                 # Media library controller
│   │
│   ├── api/                      # Admin AJAX & Internal JSON Endpoints
│   │   ├── controller.php        # Central AJAX dispatcher for admin CRUD operations
│   │   ├── email_preview.php     # Live preview renderer for outgoing email templates
│   │   ├── files.php             # Media upload, delete, and list API
│   │   ├── map.php               # Map coordinate update API
│   │   ├── notifications.php     # Live admin badge notification poller
│   │   ├── search.php            # Global admin search API (leads, services, blogs)
│   │   └── whatsapp.php          # WhatsApp test message & notification trigger
│   │
│   ├── controller/               # Object-Oriented Admin Controllers
│   │   ├── ApiController.php     # Central API processing controller
│   │   ├── MapController.php     # Map coordinate logic controller
│   │   └── backup/
│   │       └── BackupController.php # Backup generation and restoration coordinator
│   ├── model/
│   │   └── backup/
│   │       └── BackupModel.php   # SQL dump generation, gzip compression, and prune engine
│   ├── css/                      # Admin panel stylesheets
│   │   ├── theme.css             # Base admin styles
│   │   ├── theme-professional.css# Enterprise professional theme
│   │   ├── admin-white-theme.css # Clean light theme
│   │   └── components.css        # Badges, cards, datatables, modal components
│   ├── includes/                 # Admin layout headers, footers & partials
│   │   ├── header.php            # Default admin header
│   │   ├── header-pro.php        # Pro theme header with responsive sidebar & user dropdown
│   │   ├── footer.php            # Default admin footer
│   │   ├── footer-pro.php        # Pro theme footer with script assets
│   │   ├── media-picker-modal.php# Reusable modal for asset selection
│   │   └── settings-nav.php      # Settings sidebar navigation
│   │
│   └── settings/                 # Direct settings controller pages
│       ├── general.php
│       ├── appearance.php
│       ├── integration.php
│       ├── mailsettings.php
│       ├── module.php
│       ├── homepage.php
│       └── save-notifications.php
│
├── api/                          # Public Frontend AJAX & REST Endpoints
│   ├── lead-capture.php          # Unified lead capture endpoint (modal + footer form)
│   ├── services.php              # Public JSON catalog of active services
│   └── testimonials.php          # Public JSON stream of approved testimonials
│
├── includes/                     # Frontend Shared Layout Partials
│   ├── header.php                # Site navigation, branding, meta tags, CSS links
│   ├── footer.php                # Site footer, inquiry popup modal, copyright, JS links
│   └── components/
│       └── faq_section.php       # Dynamic reusable accordion FAQ component
│
├── assets/                       # Static Frontend Assets
│   ├── css/                      # Public stylesheets (style.css, responsive.css, animations)
│   ├── js/                       # Public JavaScript (main.js, slider, AJAX form handlers)
│   ├── images/                   # Brand graphics, icons, illustration SVG/PNGs
│   └── vendor/                   # Front-end vendor assets (Swiper, Bootstrap, FontAwesome)
│
├── backups/                      # SQL database backup archives
├── uploads/                      # CMS uploads (logos, service covers, team avatars, media)
└── vendor/                       # Composer dependencies (PHPMailer 7.1)
```

---

## 5. Complete Database Data Dictionary (23 Tables)

The database schema is defined in `database.sql` and `database_migration.sql` with `InnoDB` storage engine, foreign key compliance, and `utf8mb4_unicode_ci` character set.

| # | Table Name | Purpose & Function | Key Columns | Indexes & Constraints |
|---|---|---|---|---|
| 1 | `accounts` | Client & staff portal user credentials and roles | `id`, `name`, `username`, `email`, `password`, `wa_number`, `avatar`, `role`, `is_active`, `google_id` | PRIMARY (`id`), UNIQUE (`username`), UNIQUE (`email`) |
| 2 | `admin` | Super administrative users for `/admin/` access | `id`, `name`, `username`, `email`, `password`, `wa_number`, `avatar`, `role`, `is_active`, `last_login` | PRIMARY (`id`), UNIQUE (`email`), INDEX (`username`) |
| 3 | `registered_emails`| Newsletter subscribers and verified alert emails | `id`, `email`, `name`, `role`, `is_active` | PRIMARY (`id`), UNIQUE (`email`) |
| 4 | `modules` | System feature flags (Google Login, Email Alerts, WhatsApp) | `id`, `module_key`, `module_name`, `module_type`, `description`, `is_active` | PRIMARY (`id`), UNIQUE (`module_key`) |
| 5 | `settings` | Dynamic key-value configuration store | `id`, `setting_key`, `setting_value`, `updated_at` | PRIMARY (`id`), UNIQUE (`setting_key`) |
| 6 | `uploads` | File & media library registry | `id`, `file_name`, `file_path`, `file_type`, `file_size`, `folder`, `file_title` | PRIMARY (`id`), INDEX (`folder`) |
| 7 | `services` | Digital marketing service offerings & catalog | `id`, `service_name`, `slug`, `short_description`, `full_description`, `icon`, `image`, `price`, `status`, `features`, `benefits`, `process_steps`, `faqs` | PRIMARY (`id`), UNIQUE (`slug`), INDEX (`status`) |
| 8 | `plans` | Service pricing packages & deliverables | `id`, `plan_name`, `category`, `price`, `duration`, `features`, `status`, `is_popular`, `sort_order` | PRIMARY (`id`), INDEX (`status`) |
| 9 | `projects` | Portfolio case studies & client projects | `id`, `project_name`, `client_name`, `budget`, `category`, `industry`, `description`, `results`, `thumbnail`, `status` | PRIMARY (`id`), INDEX (`status`) |
| 10| `testimonials` | Client feedback quotes & ratings | `id`, `name`, `designation`, `company`, `feedback`, `rating`, `avatar`, `status` | PRIMARY (`id`), INDEX (`status`) |
| 11| `reviews` | Public client reviews with star ratings (1–5) and moderation | `id`, `name`, `email`, `rating`, `service_id`, `review_text`, `status` (`pending`, `approved`, `rejected`) | PRIMARY (`id`), INDEX (`status`), INDEX (`rating`) |
| 12| `why_choose_us` | Agency strengths & value proposition bullets | `id`, `title`, `description`, `icon`, `sort_order`, `is_active` | PRIMARY (`id`), INDEX (`is_active`) |
| 13| `faqs` | Frequently Asked Questions | `id`, `category`, `question`, `answer`, `sort_order`, `is_active` | PRIMARY (`id`), INDEX (`category`), INDEX (`is_active`) |
| 14| `pages` | Custom CMS dynamic content pages | `id`, `slug`, `title`, `body_content`, `meta_title`, `meta_keywords`, `meta_description`, `is_active` | PRIMARY (`id`), UNIQUE (`slug`) |
| 15| `leads` | Inbound client inquiries and quote requests | `id`, `name`, `email`, `phone`, `service`, `website`, `message`, `status` (`new`, `contacted`, `confirmed`, `lose`), `source`, `ip_address` | PRIMARY (`id`), INDEX (`status`), INDEX (`created_at`) |
| 16| `admin_activities`| Granular audit trail for all admin actions | `id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `device_info`, `timestamp` | PRIMARY (`id`), INDEX (`user_id`), INDEX (`timestamp`) |
| 17| `admin_sessions` | Active administrator sessions for security tracking | `id`, `admin_id`, `session_id`, `ip_address`, `user_agent`, `last_activity` | PRIMARY (`id`), UNIQUE (`session_id`), INDEX (`admin_id`) |
| 18| `security_alerts` | Automated threat warnings (brute-force, invalid access) | `id`, `type`, `severity` (`info`, `warning`, `critical`), `message`, `ip_address`, `is_resolved` | PRIMARY (`id`), INDEX (`severity`), INDEX (`is_resolved`) |
| 19| `rate_limits` | Persistent rate limiting counters for anti-abuse | `id`, `rate_key`, `hits`, `expires_at` | PRIMARY (`id`), UNIQUE (`rate_key`), INDEX (`expires_at`) |
| 20| `ip_blocks` | Banned IP addresses blocked by security engine | `id`, `ip_address`, `reason`, `blocked_by`, `expires_at` | PRIMARY (`id`), UNIQUE (`ip_address`) |
| 21| `mail_logs` | Outgoing transactional email audit trail | `id`, `recipient`, `subject`, `status` (`sent`, `failed`), `error_message`, `sent_at` | PRIMARY (`id`), INDEX (`status`), INDEX (`sent_at`) |
| 22| `mail_queue` | Asynchronous email queue for background dispatch | `id`, `recipient`, `subject`, `body_html`, `alt_body`, `attempts`, `max_attempts`, `status` (`queued`, `processing`, `sent`, `failed`) | PRIMARY (`id`), INDEX (`status`), INDEX (`created_at`) |
| 23| `blogs` | Editorial blog articles | `id`, `title`, `slug`, `excerpt`, `content`, `cover_image`, `category`, `tags`, `views`, `status` (`draft`, `published`) | PRIMARY (`id`), UNIQUE (`slug`), INDEX (`status`) |

---

## 6. Security Engine & Hardening Architecture

### 6.1 Authentication & Session Life Cycle (`admin/auth.php`)
- **Single-Sign-On & Cookie Security:** Session cookies enforce `HttpOnly`, `SameSite=Strict`, and dynamic `Secure` flags when HTTPS is active.
- **Inactivity Auto-Logout:** Automatic termination after **1,800 seconds (30 minutes)** of inactivity:
  ```php
  if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
      session_unset();
      session_destroy();
      header("Location: /admin/login?timeout=1");
      exit;
  }
  $_SESSION['last_activity'] = time();
  ```
- **Session Fixation Prevention:** Session IDs regenerate periodically upon successful authentication.

### 6.2 CSRF Protection (`app/config.php`)
- **Generation:** Generates cryptographically secure 32-byte hex tokens stored with 1-hour expiration.
- **Verification:** Constant-time verification using `hash_equals()` protects against timing attacks.
- **Helper:** `csrfField()` automatically renders hidden input tags into all admin and contact forms.

### 6.3 AES-256-CBC Encryption at Rest
- Sensitive external credentials (SMTP passwords, third-party API secret tokens) are stored in the database encrypted via OpenSSL:
  - Cipher: `AES-256-CBC`
  - Key Derivation: SHA-256 derivation of application `ENCRYPTION_KEY`
  - IV: Cryptographically secure 16-byte pseudo-random initialization vector prepended to ciphertext.

### 6.4 Server-Level Protection (`.htaccess`)
- Denial of direct HTTP access to files with sensitive extensions:
  ```apache
  <FilesMatch "\.(env|log|ini|conf|git|sql|bak|md|sh|zip|phar|lock|json)$">
      Require all denied
  </FilesMatch>
  ```
- Directory browsing explicitly disabled: `Options -Indexes`.
- Critical HTTP response security headers emitted on all responses:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: geolocation=(self), microphone=(), camera=()`

---

## 7. Email, WhatsApp & Asynchronous Pipeline

### 7.1 SMTP Factory (`app/smtp_factory.php`)
Shared hosting environments (e.g., NinzaHost, cPanel) often suffer from IPv6 routing timeouts when connecting to mail servers. The custom factory solves this with:
1. **Dynamic IPv4 Resolution:** Resolves SMTP server hostnames to IPv4 via `dns_get_record()` or `gethostbyname()`.
2. **Anti-Spam Deliverability Headers:**
   - Clears `X-Mailer` header to prevent PHPMailer signature detection by spam filters.
   - Sets valid `Message-ID` host and `Return-Path` for SPF/DKIM/DMARC alignment.
   - Automatically renders clean multipart plain-text alternatives via `htmlToCleanPlainText()`.
3. **Exponential Backoff & Resilience:** Retries failed connections up to 2 times with a 1-second pause while cleanly skipping redundant retries on permanent 5xx authentication rejections.

### 7.2 Non-Blocking Asynchronous Queue (`app/mail_queue.php` & `app/queue_worker.php`)
- **Frontend Submission:** Contact forms and lead captures call `enqueueMail()`, immediately returning success JSON to the user without waiting for SMTP network handshakes.
- **Queue Worker Daemon:** Scheduled via cron or CLI (`php app/queue_worker.php`), processes batches with atomic database row-locking, updates delivery status in `mail_queue`, and writes audit records to `mail_logs`.

### 7.3 WhatsApp Notification Engine (`app/services/WhatsAppService.php`)
- Dispatches instant alerts to agency mobile numbers upon new lead registration.
- Configurable via `/admin/settings/integration`.

---

## 8. REST & AJAX API Endpoints

### 8.1 Public API Endpoints

#### 1. Lead Capture (`POST /api/lead-capture.php`)
- **Payload:** `name`, `phone` / `mobile`, `email`, `service`, `website`, `message`, `csrf_token`.
- **Validation:** Normalizes Indian and international phone numbers, checks rate limit thresholds, sanitizes text inputs.
- **Execution:**
  1. Inserts lead record into `leads` table with status `new`.
  2. Enqueues internal alert email to agency administrators.
  3. Enqueues personalized confirmation email to prospective client.
  4. Triggers instant WhatsApp message to agency notifications number.
- **Response:**
  ```json
  {
    "status": "success",
    "message": "Thank you! Our growth team will get in touch shortly."
  }
  ```

#### 2. Services Feed (`GET /api/services.php`)
- Returns JSON array of all active services with title, slug, icon, short description, and pricing for dynamic front-end widgets.

#### 3. Testimonials Feed (`GET /api/testimonials.php`)
- Returns approved client testimonials and 5-star ratings for dynamic carousel rendering.

### 8.2 Admin AJAX API Endpoints

| Endpoint | Method | Functionality |
|---|---|---|
| `/admin/api/controller.php` | `POST` | Master AJAX dispatcher for CRUD operations on leads, services, testimonials, FAQs, and settings |
| `/admin/api/files.php` | `POST`/`GET` | Uploads media assets, verifies MIME types, stores metadata in `uploads`, returns URL |
| `/admin/api/notifications.php` | `GET` | Polled by admin top bar; returns counts of unread leads and active security alerts |
| `/admin/api/search.php` | `GET` | Global admin search querying across leads, services, and blogs |
| `/admin/api/whatsapp.php` | `POST` | Dispatches test WhatsApp messages to verify gateway credentials |
| `/admin/api/email_preview.php` | `GET` | Renders HTML email templates with mock data for visual inspection before broadcasting |

---

## 9. Admin CMS & CRM Module Specifications

```
                     ┌───────────────────────────────────────┐
                     │          ADMIN DASHBOARD             │
                     │  (KPIs, Lead Velocity, Recent Audits) │
                     └───────────────────┬───────────────────┘
                                         │
     ┌───────────────────┬───────────────┴───────────────┬───────────────────┐
     ▼                   ▼                               ▼                   ▼
 [Leads CRM]      [Content Modules]               [System Reports]    [Settings Hub]
 ├─ New           ├─ Services (CRUD)              ├─ Lead Analytics   ├─ General (Branding)
 ├─ Contacted     ├─ Blogs (Rich Editor)          ├─ Conversion Rate  ├─ Appearance (Themes)
 ├─ Confirmed     ├─ Projects / Case Studies      ├─ Mail Logs        ├─ Integration (API/Map)
 └─ Lose          ├─ Reviews (Moderation Queue)   └─ Activity Audit   ├─ Mail Settings (SMTP)
                  ├─ Testimonials                                     ├─ Module Toggles
                  ├─ Why Choose Us                                    ├─ Homepage Customizer
                  ├─ FAQs                                             └─ Database Backups
                  └─ Custom Pages
```

### 9.1 Inbound Lead CRM Workflow (`/admin/leads.php`)
The CRM categorizes incoming prospective clients into 4 defined stages:
- **`new` (New / Pending):** Newly captured lead awaiting first response.
- **`contacted` (Outreach Initiated):** Contact made via phone call, WhatsApp, or discovery email.
- **`confirmed` (Won / Converted):** Prospect qualified, scope finalized, client onboarded.
- **`lose` (Unqualified / Lost):** Budget mismatch, unresponsive, or unqualified.

### 9.2 Content Management Suite
- **Services Manager (`/admin/services.php`):** Title, SEO slug, icon, short summary, deliverables, process steps, FAQs, and pricing.
- **Blogs Manager (`/admin/blogs.php`):** Rich editorial editor, categories, tags, featured image, published/draft status, and view counter.
- **Portfolio & Case Studies (`/admin/projects.php`):** Client name, campaign budget, results metric (e.g., "+340% Traffic"), imagery, and live link.
- **Reviews Moderation (`/admin/reviews.php`):** Public reviews can be reviewed and toggled between `pending`, `approved`, and `rejected`.
- **Database Backup Engine (`/admin/backup.php`):** Generates full SQL dump files, compresses into `.gz` or `.sql`, and stores in protected storage with automated pruning of older snapshots.

---

## 10. Audit of Bugs, Diagnostics & Recommended Fixes

Based on codebase analysis and server `error_log` records, the following items have been diagnosed:

| Diagnostic Finding | Root Cause | Impact | Recommended Fix / Implemented Solution |
|---|---|---|---|
| **SMTP Host Connection Error (`portel.cloudaspire.in`)** | DNS resolution failure on local XAMPP environment when attempting to connect to external cPanel host | Outgoing emails fail on localhost without internet or valid DNS entry | 1. Ensure `app/.env` uses reachable local SMTP (or Mailtrap/Gmail) for local testing.<br>2. On production cPanel host, `portel.cloudaspire.in` or `localhost` resolves natively.<br>3. `app/smtp_factory.php` handles graceful queue retention without breaking frontend responses. |
| **PHP Warning: `session.gc_divisor` must be greater than 0** | Misconfigured `php.ini` or `.user.ini` value for `session.gc_divisor = 0` | PHP startup warning in log | Ensure `session.gc_divisor = 1000` and `session.gc_probability = 1` in `php.ini` / `.user.ini`. |
| **Dynamic FAQ Accordion in Service Detail** | Static hardcoded FAQs previously limited service page utility | Lower conversion and SEO richness on individual service pages | `includes/components/faq_section.php` accepts dynamic FAQ arrays from `services` table JSON column with fallback defaults. |
| **Lead Status State Consistency** | Admin controller and views required standardized status keys (`new`, `contacted`, `confirmed`, `lose`) | Inconsistent status badges | Standardized via `app/lead_statuses.php` and verified across `admin/leads.php`, `admin/controller/ApiController.php`, and `app/Views/admin/leads-pro.php`. |

---

## 11. Deployment, Migration & Maintenance Runbook

### 11.1 Local Development (XAMPP on Windows)
1. Ensure files reside in `c:\xampp\htdocs`.
2. Start **Apache** and **MySQL** services in the XAMPP Control Panel.
3. Configure `c:\xampp\htdocs\app\.env`:
   ```ini
   DB_HOST=localhost
   DB_NAME=cloudaspire_db
   DB_USER=root
   DB_PASS=
   DB_CHARSET=utf8mb4

   SITE_URL=http://localhost
   SITE_NAME="Cloud Aspire"
   ENCRYPTION_KEY=0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef
   ```
4. Run migrations by browsing to `http://localhost/migrate.php` or importing `database_migration.sql` into phpMyAdmin.
5. Access site at `http://localhost/` and admin portal at `http://localhost/admin/`.

### 11.2 Production Deployment (cPanel / Apache)
1. Verify PHP version is set to **PHP 8.2 or 8.4** (`ea-php84`).
2. Run `composer install --no-dev --optimize-autoloader`.
3. Set file permissions:
   - `uploads/` $\rightarrow$ `755`
   - `backups/` $\rightarrow$ `755`
   - `app/.env` $\rightarrow$ `600`
4. Set up the Asynchronous Email Queue cron job (every 1 minute):
   ```bash
   * * * * * /usr/local/bin/php /home/{cpanel_user}/public_html/app/queue_worker.php > /dev/null 2>&1
   ```
5. Ensure `.htaccess` is present in the web root for rewrite rules, security headers, and compression.

---

## 12. Conclusion & Summary

The **Cloud Aspire Digital Marketing Portal** is a production-hardened web application built with a balanced hybrid architecture. It delivers superior performance, high conversion speed, complete administrative control, dynamic lead management, and dependable email delivery.
