# Cloud Aspire — Email Deliverability & System Architecture (`mail.md`)

This document provides the definitive technical specification and operational guide for the email delivery architecture of **Cloud Aspire** (`cloudaspiresolutions.in`).

---

## 1. Executive Summary & Policy

To guarantee **100% Inbox Deliverability** (avoiding Gmail, Yahoo, and Outlook Spam placement) and preserve domain sender reputation, the email subsystem has been strictly reduced to **essential lead generation and authentication workflows only**:

1. **Active Outbound Emails (Strictly Enforced)**:
   - **Admin Lead & Audit Notifications**: `sendAuditRequestNotification`, `sendLeadNotification`, `sendContactNotification`.
   - **Visitor Confirmations**: `sendAuditThankYouEmail`, `sendLeadThankYouEmail`.
   - **Admin Password Reset OTP**: Authenticated password reset flow in `admin/login.php`.
   - **Manual SMTP Diagnostic Test**: Interactive test email from `admin/settings/mailsettings.php`.
2. **Disabled Outbound Emails (Safe No-Ops)**:
   - Welcome emails, admin login/logout alerts, review notices (submission, approval, admin alerts), project activity alerts, site configuration change alerts, automated report digests, and SecurityNotifier emails have been converted into **safe no-ops** that return `['success' => true]` without dispatching emails.
   - All database audit logging, brute force security checks, and activity records in `admin_activities` and `security_alerts` remain 100% active.

---

## 2. Authenticated SMTP Configuration

| Parameter | Configuration Value | Notes |
|---|---|---|
| **Outgoing Mail Server (Host)** | `mail.cloudaspiresolutions.in` | cPanel Exim Mail Server |
| **Port** | `465` | SMTPS |
| **Protocol** | `SSL` (`ENCRYPTION_SMTPS`) | Explicit SSL socket |
| **Authenticated Username** | `noreply@cloudaspiresolutions.in` | Must match `mail_smtp_email` |
| **From Address** | `noreply@cloudaspiresolutions.in` | Aligned with SPF, DKIM, and DMARC |
| **From Name** | `Cloud Aspire` / `Cloud Aspire Solutions` | Clean display name |
| **Envelope Sender (`Return-Path`)** | `noreply@cloudaspiresolutions.in` | Enforced in `PHPMailer->Sender` |
| **HELO / EHLO Domain** | `cloudaspiresolutions.in` | Enforced in `PHPMailer->Hostname` |
| **Message-ID Domain** | `<...@cloudaspiresolutions.in>` | RFC 5322 compliance |
| **X-Mailer Header** | Suppressed (`NULL`) | Prevents spam fingerprinting |
| **Admin Notification Recipient** | `premkumar40187@gmail.com` | Configured in `settings.mail_admin_email` |

---

## 3. Header & Reply-To Alignment Architecture

To prevent email clients from classifying emails as spoofed or fraudulent:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        OUTBOUND EMAIL FLOW                             │
├────────────────────────────────────────────────────────────────────────┤
│ 1. From: "Cloud Aspire" <noreply@cloudaspiresolutions.in>              │
│ 2. Return-Path: <noreply@cloudaspiresolutions.in>                      │
│ 3. EHLO: cloudaspiresolutions.in                                       │
│ 4. Message-ID: <unique-hash@cloudaspiresolutions.in>                   │
│                                                                        │
│ For Admin Lead Notifications:                                          │
│   Reply-To: visitor_email@example.com (Visitor Name)                   │
│   (Allows admin to hit Reply directly without spoofing From)           │
│                                                                        │
│ For Visitor Thank-You Confirmations:                                   │
│   Reply-To: noreply@cloudaspiresolutions.in                            │
│   (Prevents exposing personal Gmail addresses)                         │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Active Transactional Email Catalog

### 01. SEO Audit Request (Admin Notification)
- **Function**: `sendAuditRequestNotification(array $audit_data, bool $async = true)`
- **File**: `app/email_helper.php`
- **Recipient**: `mail_admin_email` (`premkumar40187@gmail.com`)
- **Subject**: `New SEO Audit Request - {Visitor Name}`
- **Reply-To**: `{Visitor Email}`
- **Anti-Spam Features**: Removed "Free" from subject line, clean plain-text URL rendering, dual-MIME `AltBody`.

### 02. SEO Audit Confirmation (Visitor Thank You)
- **Function**: `sendAuditThankYouEmail(string $email, string $name, ?string $website = null, ?int $lead_id = null, bool $async = true)`
- **File**: `app/email_helper.php`
- **Recipient**: `{Visitor Email}`
- **Subject**: `We received your SEO audit request - {Site Name}`
- **Reply-To**: `noreply@cloudaspiresolutions.in`
- **Anti-Spam Features**: Removed "Free" and emojis from subject and badges, realistic next steps, CAN-SPAM compliant footer with contact details.

### 03. General Lead Inquiry (Admin Notification)
- **Function**: `sendLeadNotification(array $lead_data, bool $async = true)`
- **File**: `app/email_helper.php`
- **Recipient**: `mail_admin_email` (`premkumar40187@gmail.com`)
- **Subject**: `New Lead Received - {Visitor Name}`
- **Reply-To**: `{Visitor Email}`

### 04. General Lead Confirmation (Visitor Thank You)
- **Function**: `sendLeadThankYouEmail(string $email, string $name, string $service = '', bool $async = true)`
- **File**: `app/email_helper.php`
- **Recipient**: `{Visitor Email}`
- **Subject**: `Thank you for contacting {Site Name}`
- **Reply-To**: `noreply@cloudaspiresolutions.in`

### 05. Contact Form Submission (Admin Notification)
- **Function**: `sendContactNotification(array $contact_data, bool $async = true)`
- **File**: `app/email_helper.php`
- **Recipient**: `mail_admin_email` (`premkumar40187@gmail.com`)
- **Subject**: `New Contact Form Submission - {Visitor Name}`
- **Reply-To**: `{Visitor Email}`

### 06. Admin Password Reset OTP
- **Location**: `admin/login.php`
- **Recipient**: Requesting Admin Email
- **Subject**: `Password Reset Verification Code`
- **Expires**: 10 minutes

### 07. Interactive SMTP Diagnostic
- **Location**: `admin/settings/mailsettings.php`
- **Recipient**: Tested Email Address
- **Subject**: `Test Email from Cloud Aspire Admin`

---

## 5. Anti-Abuse & Deliverability Defenses (`api/lead-capture.php`)

1. **Honeypot Bot Traps**:
   - Evaluates hidden fields: `website_hp`, `hp_field`, `comment_body`, `url_confirm`.
   - If filled by spam bots, immediately returns fake success (`{"status":"success"}`) without saving to database or enqueuing emails.
2. **IP Rate Limiting**:
   - Tracked in `rate_limits` table (`type = 'lead_capture_ip'`).
   - Limits clients to a maximum of **3 submissions per hour**.
3. **Recipient Autoresponder Throttling**:
   - Tracked in `rate_limits` table (`type = 'lead_thankyou_email'`).
   - Prevents attackers from using the contact form to flood a victim's inbox (maximum **1 autoresponder per email per hour**).
4. **Header Injection Prevention**:
   - Strips all carriage returns (`\r`), newlines (`\n`), and URL-encoded linefeeds (`%0a`, `%0d`) from single-line fields before processing.
5. **DNS MX / A Record Verification**:
   - Validates recipient email syntax via `filter_var` and verifies domain MX/A records using `checkdnsrr` to reject dead or dummy domains.

---

## 6. Required DNS Records Checklist for `cloudaspiresolutions.in`

To ensure Gmail, Yahoo, and Outlook deliver 100% of emails directly to the **Inbox**, verify or configure these 3 DNS TXT records in your cPanel Zone Editor or DNS Manager:

### 1. SPF Record (Sender Policy Framework)
- **Type**: `TXT`
- **Host / Name**: `@` (or `cloudaspiresolutions.in`)
- **TTL**: `3600` (or `14400`)
- **Record Value**:
  ```text
  v=spf1 +a +mx +ip4:103.120.178.147 +include:server147.nethost.in ~all
  ```
  *(Authorizes your web server and cPanel mail server `server147.nethost.in` to send on behalf of the domain).*

### 2. DKIM Record (DomainKeys Identified Mail)
- In cPanel, navigate to **Email Deliverability** &rarr; locate `cloudaspiresolutions.in` &rarr; click **Manage**.
- Under **DKIM**, ensure DKIM is enabled and copy the suggested `default._domainkey.cloudaspiresolutions.in` TXT record into your DNS provider if external DNS is used.
- **Type**: `TXT`
- **Host / Name**: `default._domainkey.cloudaspiresolutions.in`
- **Value**: `v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAASC...`

### 3. DMARC Record (Domain-based Message Authentication)
- **Type**: `TXT`
- **Host / Name**: `_dmarc` (or `_dmarc.cloudaspiresolutions.in`)
- **TTL**: `3600`
- **Recommended Value**:
  ```text
  v=DMARC1; p=none; sp=none; rua=mailto:noreply@cloudaspiresolutions.in;
  ```
  *(Starts with `p=none` for monitoring alignment. Once verified, can be upgraded to `p=quarantine` or `p=reject`).*

---

## 7. How to Verify Deliverability (Testing Steps)

1. **mail-tester.com Test**:
   - Go to [mail-tester.com](https://www.mail-tester.com/) and copy the unique test address (e.g. `test-xyz@srv1.mail-tester.com`).
   - Go to Admin Panel &rarr; **Settings** &rarr; **Mail & SMTP Settings**.
   - Under **Send Test Email**, paste the test address and click **Send Test Email**.
   - Return to mail-tester.com and click **Check your score**. Aim for 9/10 or 10/10.

2. **Gmail "Show original" Verification**:
   - Send an inquiry through your website form or send a test email to your Gmail address.
   - In Gmail, open the received email.
   - Click the three dots (More) next to Reply &rarr; click **Show original**.
   - Confirm all three pass:
     - **SPF**: `PASS with IP ...`
     - **DKIM**: `PASS with domain cloudaspiresolutions.in`
     - **DMARC**: `PASS`
