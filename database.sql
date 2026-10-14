-- ===============================================================
-- Cloud Aspire - Production Database Migration Script
-- CherriDM Themed Digital Marketing Platform
-- Target: MySQL 5.7+ / MariaDB 10.3+ | utf8mb4_unicode_ci
-- Safe & Idempotent: Re-runnable without data loss
-- ===============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ---------------------------------------------------------------
-- 1. Table structure for `accounts`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `wa_number` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `role` enum('admin','manager','user','viewer') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_accounts_username` (`username`),
  UNIQUE KEY `idx_accounts_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 2. Table structure for `admin`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `wa_number` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admin_email` (`email`),
  KEY `idx_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin` (`id`, `name`, `username`, `email`, `password`, `wa_number`, `avatar`, `role`, `is_active`, `last_login`, `google_id`, `created_at`, `updated_at`) VALUES
(1, 'cloudaspire', 'cloudaspire', 'cloudaspire2026@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', '', 'admin', 1, NULL, NULL, '2026-07-05 06:53:50', '2026-07-29 15:38:33'),
(2, 'kutty', 'kutty', 'rkeditor03@gmail.com', '$2y$12$ZI8OPDgnMrwGZk5sK751yOfzk0VdzoLKuIw1SdQaEKtN4//WuiFTa', NULL, NULL, 'admin', 1, NULL, NULL, '2026-07-06 07:07:17', '2026-07-06 07:07:17')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `role`=VALUES(`role`);

-- ---------------------------------------------------------------
-- 3. Table structure for `registered_emails`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registered_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` enum('admin','manager','user','viewer') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_registered_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 4. Table structure for `modules`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_key` varchar(50) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_type` varchar(50) DEFAULT 'standard',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_module_key` (`module_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `modules` (`id`, `module_key`, `module_name`, `module_type`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'google_login', 'Google Login', 'oauth', 'Enable Google OAuth login for admin portal', 0, '2026-07-05 02:05:17', '2026-07-05 02:05:17'),
(2, 'email_notifications', 'Email Notifications', 'standard', 'Send email notifications for important events', 1, '2026-07-05 02:05:17', '2026-07-05 02:05:17'),
(3, 'whatsapp_integration', 'WhatsApp Integration', 'api', 'WhatsApp messaging integration', 1, '2026-07-05 02:05:17', '2026-07-05 02:05:17')
ON DUPLICATE KEY UPDATE `module_name`=VALUES(`module_name`), `is_active`=VALUES(`is_active`);

-- ---------------------------------------------------------------
-- 5. Table structure for `settings`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Cloud Aspire'),
('site_favicon', 'uploads/site_favicon_1783239900.png'),
('site_logo', 'uploads/site_logo_1783239874.png'),
('contact_email', 'cloudaspire2025@gmail.com'),
('contact_phone', '8098830937'),
('contact_whatsapp', '8098830937'),
('contact_address', 'No. 53, Auvai Nagar, Puducherry, 605008'),
('contact_hours', 'Mon - Sat: 9:00 AM - 7:00 PM'),
('contact_map_iframe', '<iframe src=\"https://maps.google.com/maps?q=No%2053%20Auvai%20Nagar&t=&z=13&ie=UTF8&iwloc=&output=embed\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>'),
('admin_onboarded', '1'),
('mail_admin_email', 'cloudaspire2026@gmail.com'),
('mail_smtp_email', 'noreply@cloudaspire.in'),
('mail_smtp_server', 'portel.cloudaspire.in'),
('mail_smtp_port', '587'),
('mail_smtp_protocol', 'tls'),
('mail_from_name', 'Cloud Aspire'),
('mail_force_ipv4', '0'),
('mail_smtp_password', 'cloudaspire@2026'),
('social_facebook', 'https://facebook.com'),
('social_instagram', 'https://www.instagram.com/cloudaspire.in/'),
('social_twitter', 'https://twitter.com'),
('social_linkedin', 'https://www.linkedin.com/in/cloud-aspire-3819b43a3'),
('social_facebook_url', 'https://facebook.com'),
('social_facebook_enabled', '1'),
('social_instagram_url', 'https://www.instagram.com/cloudaspire.in/'),
('social_instagram_enabled', '1'),
('social_twitter_url', 'https://twitter.com'),
('social_twitter_enabled', '1'),
('social_linkedin_url', 'https://www.linkedin.com/in/cloud-aspire-3819b43a3'),
('social_linkedin_enabled', '1'),
('theme_primary', '#03228f'),
('theme_accent', '#fb9a27'),
('privacy_policy', '<h2>1. Information We Collect</h2><p>At Cloud Aspire, we collect information you provide directly to us, such as your name, email address, phone number, and business details when you fill out contact forms, request proposals, subscribe to updates, or engage our services.</p><h2>2. How We Use Your Information</h2><p>We use the data we collect for purposeful and transparent business operations:</p><ul><li>To deliver, execute, and monitor our digital marketing and web development services</li><li>To communicate critical project milestones, campaign performance, and strategy updates</li><li>To enhance website responsiveness, security, and customer experience</li><li>To adhere strictly to applicable statutory and legal frameworks</li></ul><h2>3. Information Sharing & Third Parties</h2><p>We strictly do not sell, trade, or monetize your personal or commercial data. We only share essential metadata with vetted infrastructure providers who are bound by non-disclosure agreements.</p><h2>4. Data Security</h2><p>We employ enterprise-grade security protocols, encryption in transit (SSL/TLS), access controls, and regular vulnerability assessments to safeguard your data against unauthorized access or breaches.</p><h2>5. Cookies & Tracking Technologies</h2><p>Our website utilizes necessary cookies and privacy-respecting analytics to optimize user navigation and performance.</p><h2>6. Your Data Rights</h2><p>You have the right to access, verify, export, or request deletion of your personal records. For inquiries, please reach out to our privacy compliance team at <a href=\"mailto:cloudaspire2025@gmail.com\">cloudaspire2025@gmail.com</a>.</p><h2>7. Policy Updates</h2><p>We may update this Privacy Policy periodically to reflect evolving legal standards. Changes will be posted on this page with an updated timestamp.</p>'),
('terms_conditions', '<h2>1. Acceptance of Terms</h2><p>By browsing, accessing, or engaging services through Cloud Aspire, you agree to adhere to these Terms & Conditions, all applicable laws, and relevant industry regulations.</p><h2>2. Service Engagement & Deliverables</h2><p>All digital marketing, SEO, paid advertising, and web development deliverables are executed according to predefined project proposals and milestone agreements agreed upon between the client and Cloud Aspire.</p><h2>3. Intellectual Property Rights</h2><p>Upon full settlement of agreed project dues, all bespoke creative assets, website source codes, and campaign configurations developed specifically for the client become the proprietary property of the client.</p><h2>4. Client Responsibilities</h2><p>Clients are expected to provide timely feedback, necessary brand assets, and required account access to maintain agreed project schedules and campaign launch dates.</p><h2>5. Limitation of Liability</h2><p>While Cloud Aspire employs best-in-class industry methodologies, we do not guarantee third-party platform algorithm behaviors beyond direct control.</p><h2>6. Inquiries & Legal Notices</h2><p>For questions regarding our terms of service, contact our legal desk at <a href=\"mailto:cloudaspire2025@gmail.com\">cloudaspire2025@gmail.com</a>.</p>'),
('refund_policy', '<h2>1. Satisfaction Commitment</h2><p>At Cloud Aspire, we are committed to providing top-tier digital marketing, SEO, and development solutions. We work closely with every client at every milestone to ensure our deliverables match expectations.</p><h2>2. Refund Eligibility & Window</h2><p>If you are dissatisfied with a standard subscription plan or service kickoff, you may submit a formal review request within 14 calendar days of onboarding.</p><h2>3. Excluded & Non-Refundable Services</h2><p>The following costs represent direct third-party or completed disbursements and are non-refundable:</p><ul><li>Third-party platform advertising spend paid directly to Google, Meta, or LinkedIn</li><li>Domain registration, dedicated server provisioning, or third-party API license fees</li><li>Custom design or development phases that have already been reviewed, approved, and delivered</li></ul><h2>4. Refund Request Process</h2><p>To request a billing review or dispute resolution, please email our finance desk at <a href=\"mailto:cloudaspire2025@gmail.com\">cloudaspire2025@gmail.com</a> with your invoice number and project details.</p>'),
('schema_version', '2026.10.13')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);

-- ---------------------------------------------------------------
-- 6. Table structure for `uploads`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `folder` varchar(50) DEFAULT 'general',
  `file_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_uploads_folder` (`folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 7. Table structure for `services`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `short_description` text DEFAULT NULL,
  `full_description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `seo_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `process_steps` text DEFAULT NULL,
  `faqs` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_services_slug` (`slug`),
  KEY `idx_services_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `service_name`, `slug`, `short_description`, `full_description`, `icon`, `image`, `price`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SEO Optimization', 'seo-optimization', 'Dominate search rankings, drive targeted organic traffic, and outrank competitors with proven data-driven SEO strategies.', 'Comprehensive SEO including technical site audits, on-page optimization, high-authority backlink outreach, keyword research, and rank tracking.', 'fa-magnifying-glass-chart', 'uploads/services/service_1789994678_6ab126b6c2b6e.png', '₹4,999/mo', 'active', '2026-08-29 12:42:35', '2026-10-13 10:00:00'),
(2, 'Google & Meta Ads', 'google-meta-ads', 'Maximize ROI with high-converting paid ad campaigns across Google Search, Display, YouTube, Instagram, and Facebook.', 'Targeted PPC advertising, dynamic retargeting, conversion rate optimization, lower cost-per-acquisition, and transparent analytics.', 'fa-bullhorn', 'uploads/services/service_1789721271_7abfa3.png', '₹7,999/mo', 'active', '2026-08-29 12:42:35', '2026-10-13 10:00:00'),
(3, 'Social Media Marketing', 'social-media-marketing', 'Build brand authority, engage your audience, and scale follower growth across Instagram, LinkedIn, and Facebook.', 'Creative content creation, Reels/Shorts production, active community management, influencer partnerships, and growth campaigns.', 'fa-share-nodes', 'uploads/services/service_1789994482_6ab125f2962b7.png', '₹5,999/mo', 'active', '2026-08-29 12:42:35', '2026-10-13 10:00:00'),
(4, 'Website & App Development', 'website-app-development', 'Ultra-fast, mobile-responsive custom websites and web applications built to turn visitors into paying customers.', 'Custom PHP/JavaScript/HTML5 web development, e-commerce portals, web applications, landing pages, and cloud hosting setup.', 'fa-laptop-code', 'uploads/services/service_1789994893_6ab1278dc3ecd.png', '₹9,999', 'active', '2026-08-29 12:42:35', '2026-10-13 10:00:00'),
(5, 'Brand Identity & Design', 'brand-identity-design', 'Establish a memorable brand identity with professional logos, graphic design, and cohesive visual branding.', 'Comprehensive branding kits: logo packages, typography, color palettes, vector assets, stationery, and digital UI/UX assets.', 'fa-palette', 'uploads/services/service_1789995162_6ab1289adf467.png', '₹3,999', 'active', '2026-08-29 12:42:35', '2026-10-13 10:00:00'),
(6, 'Content Marketing & Copywriting', 'content-marketing', 'Compelling storytelling and conversion copywriting that captivates your audience and drives organic engagement.', 'SEO-optimized blog writing, landing page copywriting, email newsletters, whitepapers, and high-converting sales funnels.', 'fa-pen-nib', 'uploads/services/service_1789995289_6ab12919c3938.png', '₹3,499/mo', 'active', '2026-08-29 12:42:35', '2026-10-13 10:00:00')
ON DUPLICATE KEY UPDATE `service_name`=VALUES(`service_name`), `icon`=VALUES(`icon`), `image`=VALUES(`image`), `price`=VALUES(`price`), `status`=VALUES(`status`);

-- ---------------------------------------------------------------
-- 8. Table structure for `plans`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` varchar(50) DEFAULT 'monthly',
  `features` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_popular` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `delivery_time` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plans` (`id`, `plan_name`, `category`, `price`, `duration`, `features`, `status`, `created_at`, `updated_at`, `is_popular`, `sort_order`, `delivery_time`) VALUES
(1, 'Starter Growth', NULL, 9999.00, 'monthly', 'Local SEO & Google Maps Optimization\nSocial Media Management (8 posts/mo)\nGoogle Search Ads Setup\nMonthly Performance Report\nEmail & WhatsApp Support', 'active', '2026-08-29 12:57:23', '2026-08-29 12:57:23', 0, 0, NULL),
(2, 'Scale & Dominate', NULL, 19999.00, 'monthly', 'Full Technical & On-Page SEO\nGoogle Ads & Meta Ads Management\nConversion Rate Optimization (CRO)\nSocial Media Growth (16 posts + Reels)\nDedicated Account Strategist\nBi-Weekly Strategy Calls', 'active', '2026-08-29 12:57:23', '2026-08-29 12:57:23', 1, 0, NULL),
(3, 'Enterprise Custom', NULL, 39999.00, 'monthly', 'Omnichannel Performance Marketing\nCustom High-Speed Web Development\nCRM & Lead Automation Pipeline\nAdvanced Data & ROI Attribution\n24/7 Priority Support & SLA\nWeekly Strategy Alignment', 'active', '2026-08-29 12:57:23', '2026-08-29 12:57:23', 0, 0, NULL)
ON DUPLICATE KEY UPDATE `plan_name`=VALUES(`plan_name`), `price`=VALUES(`price`);

-- ---------------------------------------------------------------
-- 9. Table structure for `projects`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `budget` varchar(50) DEFAULT NULL,
  `project_datetime` datetime DEFAULT NULL,
  `status` enum('pending','running','completed','cancelled') DEFAULT 'pending',
  `project_link` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(100) DEFAULT 'web',
  `industry` varchar(100) DEFAULT 'Digital',
  `description` text DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `results` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_projects_status_date` (`status`,`project_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`id`, `project_name`, `budget`, `project_datetime`, `status`, `project_link`, `thumbnail`, `created_at`, `updated_at`, `category`, `industry`, `description`, `client_name`, `results`) VALUES
(24, 'Auroville Organic Retail Scale', NULL, NULL, 'completed', NULL, NULL, '2026-08-29 12:57:01', '2026-08-29 12:57:01', 'seo', 'E-Commerce', 'Engineered a scalable organic search engine optimization strategy that grew non-brand organic search traffic by 340% in 6 months.', 'Auroville Organics', '+340% Organic Traffic'),
(25, 'Heritage Boutique Villa Lead Engine', NULL, NULL, 'completed', NULL, NULL, '2026-08-29 12:57:01', '2026-08-29 12:57:01', 'ads', 'Hospitality', 'Implemented high-converting Meta Ads and Google Search campaigns targeting high-net-worth travelers, decreasing cost per lead by 52%.', 'White Town Heritage', '5.4x ROAS on Paid Ads'),
(26, 'Coastal Realty Premium Portal', NULL, NULL, 'completed', NULL, NULL, '2026-08-29 12:57:01', '2026-08-29 12:57:01', 'web', 'Real Estate', 'Designed and developed a lightning-fast real estate listing portal with integrated WhatsApp lead capture and CRM pipeline automation.', 'Pondy Coastal Properties', '850+ Verified Buyer Inquiries'),
(27, 'D2C Sustainable Fashion Launch', NULL, NULL, 'completed', NULL, NULL, '2026-08-29 12:57:01', '2026-08-29 12:57:01', 'social', 'Fashion & D2C', 'Executed viral influencer collaborations and micro-targeted Instagram reels campaigns, generating 12,000+ purchases in Q4.', 'Pondy Handloom Co.', '₹45L+ Revenue Generated')
ON DUPLICATE KEY UPDATE `project_name`=VALUES(`project_name`), `status`=VALUES(`status`);

-- ---------------------------------------------------------------
-- 10. Table structure for `testimonials`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`id`, `name`, `designation`, `message`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Dr. Vigneshwaran M.', 'Medical Director, Lotus Multispecialty Clinic', 'Cloud Aspire completely restructured our digital patient acquisition funnel. We saw a 280% surge in booked consultations within 90 days. Their deep understanding of local Pondicherry demographics combined with high-intent Google Search campaigns delivered extraordinary ROI.', 'assets/images/testimonials/vigneshwaran-m.jpg', 'active', '2026-08-29 12:57:42', '2026-08-29 12:57:42'),
(2, 'Ananya Sharma', 'Head of E-Commerce, Auroville Pure Living', 'The SEO strategy engineered by Cloud Aspire outranked legacy players in our organic niche. Our organic traffic compounded by 340%, leading to record-breaking revenue figures. They operate like an internal growth squad rather than a third-party vendor.', 'assets/images/testimonials/ananya-sharma.jpg', 'active', '2026-08-29 12:57:42', '2026-08-29 12:57:42'),
(3, 'Sarah Jenkins', 'Managing Partner, Villa Bay Heritage Resort', 'Our luxury bookings in White Town skyrocketed after Cloud Aspire overhauled our Google and Instagram ad creative. The ROAS jumped to 5.4x while lowering cost per lead significantly. Outstanding speed, communication, and transparency.', 'assets/images/testimonials/sarah-jenkins.jpg', 'active', '2026-08-29 12:57:42', '2026-08-29 12:57:42')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `status`=VALUES(`status`);

-- ---------------------------------------------------------------
-- 11. Table structure for `reviews`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `star_rating` tinyint(4) NOT NULL DEFAULT 5,
  `message` text DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reviews_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reviews` (`id`, `name`, `email`, `star_rating`, `message`, `review_text`, `company`, `designation`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Feroz Khan', 'feroz@vkauto.in', 5, 'Cloud Aspire is easily the most accountable digital agency in Pondicherry. They revamped our automotive dealership pipeline, generating consistent verified walk-ins and phone inquiries.', 'Cloud Aspire is easily the most accountable digital agency in Pondicherry. They revamped our automotive dealership pipeline, generating consistent verified walk-ins and phone inquiries.', 'Volkswagen Pondicherry', 'General Manager', 'approved', '2026-08-29 12:58:05', '2026-08-29 12:58:05'),
(2, 'James Anderson', 'james@capmed.org', 5, 'Best agency specialized in healthcare SEO, Meta campaigns, and high-speed web architecture. Their weekly analytics reports give complete clarity on cost per acquisition.', 'Best agency specialized in healthcare SEO, Meta campaigns, and high-speed web architecture. Their weekly analytics reports give complete clarity on cost per acquisition.', 'Capital Medical Services', 'Managing Director', 'approved', '2026-08-29 12:58:05', '2026-08-29 12:58:05'),
(3, 'Thangamani R.', 'thangamani@jenomaran.com', 5, 'Superb experience with Cloud Aspire. Their tailored real estate campaigns delivered over 850 verified buyer inquiries for our gated community launches across Pondicherry.', 'Superb experience with Cloud Aspire. Their tailored real estate campaigns delivered over 850 verified buyer inquiries for our gated community launches across Pondicherry.', 'JENO Maran Builders', 'VP Marketing', 'approved', '2026-08-29 12:58:05', '2026-08-29 12:58:05')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `status`=VALUES(`status`);

-- ---------------------------------------------------------------
-- 12. Table structure for `why_choose_us`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `why_choose_us` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-check-circle',
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `why_choose_us` (`id`, `question`, `answer`, `icon`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Proven Track Record', 'Over 500+ successful digital initiatives with measurable ROI across e-commerce, healthcare, hospitality, and real estate.', 'fa-trophy', 'active', 1, '2026-08-29 12:58:24', '2026-08-29 12:58:24'),
(2, 'Certified Specialists', 'Dedicated in-house specialists in performance marketing, technical SEO, and conversion-focused web architecture.', 'fa-users-gear', 'active', 2, '2026-08-29 12:58:24', '2026-08-29 12:58:24'),
(3, 'Data-Driven Growth', 'Every campaign decision is backed by live analytics, conversion tracking, and deep audience behavior modeling.', 'fa-chart-line', 'active', 3, '2026-08-29 12:58:24', '2026-08-29 12:58:24'),
(4, 'Dedicated Support', 'Direct access to account leads and transparent bi-weekly strategy alignment calls with zero bureaucratic delays.', 'fa-headset', 'active', 4, '2026-08-29 12:58:24', '2026-08-29 12:58:24'),
(5, 'Rapid Execution', 'Fast turnaround without sacrificing code quality, data security, mobile responsiveness, or creative finesse.', 'fa-bolt', 'active', 5, '2026-08-29 12:58:24', '2026-08-29 12:58:24'),
(6, 'Transparent Analytics', 'Live real-time performance dashboards so you always know your exact cost per acquisition and customer lifetime value.', 'fa-shield-halved', 'active', 6, '2026-08-29 12:58:24', '2026-08-29 12:58:24')
ON DUPLICATE KEY UPDATE `question`=VALUES(`question`), `status`=VALUES(`status`);

-- ---------------------------------------------------------------
-- 13. Table structure for `faqs`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faqs` (`id`, `question`, `answer`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'How quickly can we see results from digital marketing campaigns?', 'Paid ads on Google and Meta deliver high-intent inquiries within 48 to 72 hours of campaign activation. Organic search optimization (SEO) builds compounding traffic over 60 to 90 days.', 'active', 1, '2026-08-29 12:58:43', '2026-08-29 12:58:43'),
(2, 'Do you offer custom tailored packages for specific business verticals?', 'Yes. While we have proven packages, over 70% of our clients receive bespoke growth roadmaps tailored precisely to their market size, profit margins, and quarterly targets.', 'active', 2, '2026-08-29 12:58:43', '2026-08-29 12:58:43'),
(3, 'What makes Cloud Aspire the top digital marketing company in Pondicherry?', 'Unlike traditional firms that focus on vanity metrics, we focus exclusively on revenue attribution, lower cost per acquisition, and scalable organic assets that compound over time.', 'active', 3, '2026-08-29 12:58:43', '2026-08-29 12:58:43'),
(4, 'How transparent is your campaign tracking and reporting?', 'You receive direct access to real-time analytics dashboards, bi-weekly performance reviews, and monthly strategic alignment sessions with your dedicated campaign lead.', 'active', 4, '2026-08-29 12:58:43', '2026-08-29 12:58:43'),
(5, 'Can you help upgrade or rebuild our existing website or web app?', 'Absolutely. Our web engineering team builds custom, ultra-fast, mobile-first web applications optimized for speed, Google Core Web Vitals, and high-converting lead pipelines.', 'active', 5, '2026-08-29 12:58:43', '2026-08-29 12:58:43')
ON DUPLICATE KEY UPDATE `question`=VALUES(`question`), `status`=VALUES(`status`);

-- ---------------------------------------------------------------
-- 14. Table structure for `pages`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_title` varchar(150) NOT NULL,
  `page_slug` varchar(150) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `seo_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `robots_meta` varchar(100) DEFAULT 'index, follow',
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_pages_slug` (`page_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `page_title`, `page_slug`, `is_active`, `seo_title`, `meta_description`, `meta_keywords`, `canonical_url`, `robots_meta`, `created_at`, `updated_at`) VALUES
(1, 'Home', 'home.php', 1, 'Cloud Aspire - Best Digital Marketing Company in Pondicherry', 'Premier digital marketing agency in Pondicherry specializing in SEO, Google Ads, Meta Ads, and High-Performance Web Development.', 'digital marketing pondicherry, best seo company pondicherry, google ads puducherry, web development pondicherry', 'https://cloudaspire.in/', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(2, 'Services', 'services.php', 1, 'Our Services - Cloud Aspire Pondicherry', 'Result-driven SEO, Google Ads, Meta Ads, Social Media, Web Development, and Content Marketing services in Pondicherry.', 'digital marketing services, seo services pondicherry, meta ads pondicherry, web app development', 'https://cloudaspire.in/services', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(3, 'Projects', 'projects.php', 1, 'Our Portfolio - Cloud Aspire Pondicherry', 'Explore client success stories, enterprise campaigns, and verified ROI case studies engineered by Cloud Aspire.', 'digital marketing portfolio, seo case studies pondicherry, paid advertising results', 'https://cloudaspire.in/projects', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(4, 'Plans & Pricing', 'plans.php', 1, 'Pricing Plans - Cloud Aspire Pondicherry', 'Transparent, high-ROI digital marketing packages designed to scale businesses of all sizes.', 'digital marketing pricing, seo monthly packages pondicherry, social media marketing cost', 'https://cloudaspire.in/plans', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(5, 'Why Choose Us', 'why-choose-us.php', 1, 'Why Choose Us - Cloud Aspire Pondicherry', 'Discover our data-driven growth methodology, enterprise accountability, and relentless focus on client ROI.', 'why choose cloud aspire, best marketing agency pondicherry', 'https://cloudaspire.in/why-choose-us', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(6, 'Testimonials', 'testimonials.php', 1, 'Client Testimonials - Cloud Aspire Pondicherry', 'Verified client feedback and direct impact testimonials from enterprise leaders who grow with Cloud Aspire.', 'cloud aspire testimonials, client reviews digital marketing', 'https://cloudaspire.in/testimonials', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(7, 'Reviews', 'reviews.php', 1, 'Client Reviews & Ratings - Cloud Aspire', 'Read authenticated reviews and rating metrics from clients across Pondicherry, Chennai, and Bangalore.', 'cloud aspire reviews, digital marketing ratings pondicherry', 'https://cloudaspire.in/reviews', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(8, 'Contact Us', 'contact.php', 1, 'Contact Cloud Aspire - Digital Marketing Agency Pondicherry', 'Get in touch for a free marketing proposal, technical website audit, or strategy session with our team.', 'contact digital marketing pondicherry, hire seo agency puducherry', 'https://cloudaspire.in/contact', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(9, 'Privacy Policy', 'privacy-policy.php', 1, 'Privacy Policy - Cloud Aspire', 'Privacy policy, data collection compliance, and security standards for Cloud Aspire users.', 'privacy policy cloud aspire', 'https://cloudaspire.in/privacy-policy', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(10, 'Refund Policy', 'refund-policy.php', 1, 'Refund Policy - Cloud Aspire', 'Refund terms, billing dispute windows, and satisfaction guarantees for Cloud Aspire services.', 'refund policy cloud aspire', 'https://cloudaspire.in/refund-policy', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00'),
(11, 'Terms & Conditions', 'terms.php', 1, 'Terms & Conditions - Cloud Aspire', 'Terms of engagement, deliverables, intellectual property rights, and service agreements.', 'terms conditions cloud aspire', 'https://cloudaspire.in/terms', 'index, follow', '2026-08-30 00:00:00', '2026-08-30 00:00:00')
ON DUPLICATE KEY UPDATE `page_title`=VALUES(`page_title`), `seo_title`=VALUES(`seo_title`), `is_active`=VALUES(`is_active`);

-- ---------------------------------------------------------------
-- 15. Table structure for `leads`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `service` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('new','contacted','qualified','proposal_sent','converted','closed') DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_leads_status` (`status`),
  KEY `idx_leads_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 16. Table structure for `admin_activities`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('success','failed','warning') DEFAULT 'success',
  `module` varchar(50) DEFAULT 'general',
  PRIMARY KEY (`id`),
  KEY `idx_admin_act_user` (`user_id`),
  KEY `idx_admin_act_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 17. Table structure for `admin_sessions`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_id` (`session_id`),
  KEY `idx_admin_sessions_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 18. Table structure for `security_alerts`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `security_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL,
  `status` enum('unread','read','resolved','dismissed') DEFAULT 'unread',
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sec_alerts_severity` (`severity`),
  KEY `idx_sec_alerts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 19. Table structure for `rate_limits`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `count` int(11) DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rate_limit_id_type` (`identifier`,`type`),
  KEY `idx_rate_limits_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 20. Table structure for `ip_blocks`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ip_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `blocked_until` datetime NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ip_blocks_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 21. Table structure for `mail_logs`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','failed','queued') DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mail_logs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 22. Table structure for `mail_queue`
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `alt_body` text DEFAULT NULL,
  `attempts` tinyint(4) DEFAULT 0,
  `status` enum('pending','processing','sent','failed') DEFAULT 'pending',
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `send_after` datetime DEFAULT NULL,
  `priority` tinyint(4) DEFAULT 5,
  PRIMARY KEY (`id`),
  KEY `idx_mail_queue_status` (`status`,`priority`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
-- ===============================================================
-- End of Migration
-- ===============================================================
