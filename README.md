# Pentest Quote Form

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.4.1-orange.svg)](https://github.com/lostarus/Wordpress-MultiPage-Form/releases)

A professional WordPress plugin for cybersecurity penetration test quote requests with multi-step form, webhook integrations, and full customization options.

## Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [Shortcode Usage](#-shortcode-usage)
- [Settings Panel](#%EF%B8%8F-settings-panel)
- [Question & Category Management](#-question--category-management)
- [Customizing Form Labels](#-customizing-form-labels)
- [Typography Settings](#-typography-settings)
- [Webhook Integrations](#-webhook-integrations)
- [Salesforce Integration](#-salesforce-integration)
- [Managing Submissions](#-managing-submissions)
- [Security](#%EF%B8%8F-security)
- [Style Customization](#-style-customization)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)
- [Technical Reference](#-technical-reference)
- [License](#-license)

---

## 🚀 Features

| Feature | Description |
|---------|-------------|
| 🔄 **Multi-Step Form** | 3-step form with progress indicator |
| 🎯 **Popup & Inline** | Both popup and embedded form support |
| 📝 **Dynamic Questions** | Category-based question management |
| 🔗 **Webhook/API** | Power Automate, Zapier, Make integrations |
| ☁️ **Salesforce** | Direct Salesforce Lead/Contact/Opportunity creation via REST API |
| 🛡️ **reCAPTCHA v3** | Google reCAPTCHA bot protection |
| 📧 **Email Notifications** | Automatic notifications and auto-reply |
| 📊 **CSV Export** | Export submissions to CSV |
| 🎨 **Full Customization** | Colors, fonts, labels, messages |
| 🔤 **Typography** | 8 Google Fonts + system + custom fonts |
| 📱 **Mobile Responsive** | Fully responsive design |
| 🏢 **Corporate Email Filter** | Blocks personal email addresses |
| ✅ **GDPR Compliant** | Privacy consent mechanism |

---

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

---

## 📦 Installation

### Method 1: WordPress Admin Panel

1. Download the latest release from [Releases](https://github.com/lostarus/Wordpress-MultiPage-Form/releases)
2. **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Activate**

### Method 2: FTP/File Manager

1. Upload the `pentest-quote-form` folder to `/wp-content/plugins/`
2. Activate from **WordPress Admin → Plugins**

### Method 3: Git

```bash
cd /wp-content/plugins/
git clone https://github.com/lostarus/Wordpress-MultiPage-Form.git pentest-quote-form
```

---

## 🏁 Quick Start

1. Go to **WordPress Admin → Quote Requests → Settings**
2. Enter your notification email address
3. Add shortcode to any page:
   - Popup button: `[ptf_popup_trigger]`
   - Inline form: `[ptf_multistep_form]`

---

## 📝 Shortcode Usage

### Popup Trigger Button

```
[ptf_popup_trigger]
[ptf_popup_trigger text="Get Quote"]
[ptf_popup_trigger text="Request Quote" class="my-btn" primary="#E74C3C"]
```

#### Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `text` | "Get Quick Quote" | Button text |
| `class` | - | Additional CSS class |
| `primary` | #2F7CFF | Primary/background color |
| `secondary` | #B7FF10 | Secondary color |
| `size` | medium | `small`, `medium`, `large`, `xlarge`, `custom` |
| `padding_y` | - | Custom vertical padding (px) |
| `padding_x` | - | Custom horizontal padding (px) |
| `font_size` | - | Custom font size (px) |

#### Size Examples

```
[ptf_popup_trigger size="small"]
[ptf_popup_trigger size="xlarge" text="Get Your Quote Now"]
[ptf_popup_trigger padding_y="20" padding_x="50" font_size="18"]
```

| Size | Padding | Font |
|------|---------|------|
| small | 12px 24px | 14px |
| medium | 16px 32px | 16px |
| large | 20px 40px | 18px |
| xlarge | 24px 48px | 20px |

### Inline Form

```
[ptf_multistep_form]
[ptf_multistep_form primary="#9B59B6" secondary="#F1C40F"]
```

---

## ⚙️ Settings Panel

Access settings from **WordPress Admin → Quote Requests → Settings**.

### Email Settings

| Setting | Description |
|---------|-------------|
| **Notification Email** | Receives form submissions (comma-separated for multiple) |
| **Auto Reply** | Sends confirmation to submitter |

### Data Storage

| Setting | Description |
|---------|-------------|
| **Save to Database** | Stores submissions in WordPress database |
| **Send Email** | Sends email notification per submission |

> ⚠️ At least one option must be active!

### Color Settings

| Setting | Default | Usage |
|---------|---------|-------|
| **Primary Color** | #2F7CFF | Buttons, active elements |
| **Secondary Color** | #B7FF10 | Success icons |
| **Button Text Color** | #FFFFFF | Popup trigger button text |

### Button Size

| Preset | Description |
|--------|-------------|
| Small | Compact (12px 24px, 14px font) |
| Medium | Default (16px 32px, 16px font) |
| Large | Larger (20px 40px, 18px font) |
| Extra Large | Biggest (24px 48px, 20px font) |
| Custom (px) | Define your own values |

### Privacy Links

| Setting | Example |
|---------|---------|
| **Privacy Notice URL** | `/privacy-notice` |
| **Privacy Policy URL** | `/privacy-policy` |

---

## 📝 Question & Category Management

Manage from **WordPress Admin → Quote Requests → Questions**.

### Adding Categories

1. Click **Add New Category**
2. Enter name, ID (lowercase with hyphens), and icon (emoji)
3. Activate/deactivate as needed

### Question Types

| Type | Description | Use Case |
|------|-------------|----------|
| **text** | Single line | Short answers |
| **textarea** | Multi-line | Long descriptions |
| **number** | Numeric | Quantities |
| **select** | Dropdown | Single selection |
| **radio** | Radio buttons | Single selection (visible) |
| **checkbox** | Checkboxes | Multiple selection |
| **date** | Date picker | Dates |
| **email** | Email field | Email validation |
| **tel** | Phone field | Phone numbers |

### Adding Options

For `select`, `radio`, `checkbox` types:

```
Option Label|option_value
Yes|yes
No|no
```

---

## 🔤 Customizing Form Labels

Customize all texts from **Settings → Form Labels & Texts**.

### Form Header

| Field | Default |
|-------|---------|
| **Form Title** | Get Quick Quote |
| **Form Subtitle** | Get a quote for your cybersecurity needs |

### Progress Bar Steps

| Step | Default |
|------|---------|
| Step 1 | Test Selection |
| Step 2 | Test Details |
| Step 3 | Contact Information |

### Form Fields

| Field | Label | Placeholder |
|-------|-------|-------------|
| Company | Company Name | Company name |
| Contact | Contact Person | Your Full Name |
| Email | Email | corporate@yourcompany.com |
| Phone | Phone | +1 555 XXX XXXX |

### Buttons

| Button | Default |
|--------|---------|
| Next | Continue |
| Back | Back |
| Submit | Submit |

### Validation Messages

| Message | Default |
|---------|---------|
| Required | This field is required. |
| Invalid Email | Please enter a valid email address. |
| Corporate Email | Please enter your corporate email address... |
| Invalid Phone | Please enter a valid phone number. |
| reCAPTCHA Error | reCAPTCHA verification failed... |

---

## 🔤 Typography Settings

Customize fonts from **Settings → Color Settings → Typography**.

### Font Family Options

| Font | Description |
|------|-------------|
| **Inherit** | Uses theme's font |
| **System UI** | San Francisco, Segoe UI |
| **Inter** | Modern sans-serif (Google) |
| **Roboto** | Clean sans-serif (Google) |
| **Open Sans** | Humanist sans-serif (Google) |
| **Lato** | Warm sans-serif (Google) |
| **Poppins** | Geometric sans-serif (Google) |
| **Montserrat** | Urban sans-serif (Google) |
| **Nunito** | Rounded sans-serif (Google) |
| **Custom** | Your own font-family CSS |

### Font Sizes

| Element | Range | Default |
|---------|-------|---------|
| Headings | 14-48 px | 26 px |
| Body Text | 10-24 px | 15 px |
| Labels | 10-20 px | 14 px |

---

## ☁️ Salesforce Integration

Send form submissions directly to Salesforce as a **Lead**, **Contact**, **Account**, **Opportunity**, or **Case** — no third-party middleware required.

### How It Works

The plugin uses the **Salesforce OAuth 2.0 Username-Password flow** to authenticate and then calls the **Salesforce REST API** to create a record on every form submission. An access token is cached for 50 minutes and automatically refreshed when it expires.

### Prerequisites

1. A Salesforce org (Production or Sandbox)
2. A **Connected App** with OAuth enabled ([How to create one](https://help.salesforce.com/s/articleView?id=sf.connected_app_create.htm))
   - Enable OAuth Settings → check **Enable OAuth**
   - Add any Callback URL (e.g. `https://yoursite.com`)
   - Scopes: **api**, **refresh_token**
3. Your Salesforce **username**, **password**, and **security token**

### Setup

1. Go to **WordPress Admin → Quote Requests → Settings**
2. Scroll to **Salesforce Direct Integration**
3. Check **Enable Salesforce Integration**
4. Fill in the fields:

| Field | Description |
|-------|-------------|
| **Login URL** | `https://login.salesforce.com` (Production) or `https://test.salesforce.com` (Sandbox) |
| **Consumer Key** | From your Connected App → OAuth Settings |
| **Consumer Secret** | From your Connected App → OAuth Settings |
| **Username** | Your Salesforce login email |
| **Password + Security Token** | Password concatenated with security token, no spaces (e.g. `MyPassword1ABC123xyz`) |
| **Salesforce Object** | `Lead` (default), `Contact`, `Account`, `Opportunity`, or `Case` |
| **API Version** | Default `v59.0` — match your org's API version if needed |

5. Configure **Field Mapping** (JSON editor):

```json
{
  "Company":     "company",
  "LastName":    "first_name",
  "Email":       "email",
  "Phone":       "phone",
  "Description": "test_types_text"
}
```

The **left key** is the Salesforce API field name; the **right value** is the form field name.

### Available Form Fields for Mapping

| Form Field | Description |
|------------|-------------|
| `first_name` | Contact person name |
| `email` | Email address |
| `phone` | Phone number |
| `company` | Company name |
| `test_types_text` | Readable list of selected test types (comma-separated) |
| `submitted_at` | Submission date/time |
| `page_url` | URL of the page where the form was submitted |
| *(dynamic question IDs)* | Any custom question field key |

> **Note:** When the target object is `Lead`, `LeadSource` is automatically set to `"Web"` if not mapped.

### Developer Hooks

```php
// Modify the Salesforce record before it is sent
add_filter('ptf_salesforce_record', function($record, $form_data, $object) {
    $record['LeadSource'] = 'Website';
    $record['Rating']     = 'Hot';
    return $record;
}, 10, 3);

// Fires after a Salesforce record is successfully created
add_action('ptf_salesforce_record_created', function($sf_id, $form_data, $object) {
    // $sf_id is the new Salesforce record ID
    error_log('Salesforce record created: ' . $sf_id);
}, 10, 3);
```

### Troubleshooting

| Problem | Solution |
|---------|----------|
| **Authentication failed** | Double-check Consumer Key/Secret and that the Connected App's IP policy is set to *Relax IP Restrictions* |
| **Invalid password** | Make sure to append the security token directly to the password |
| **INVALID_FIELD error** | The mapped Salesforce field name is wrong or doesn't exist on the object |
| **Required field missing** | Lead requires `LastName` and `Company` — ensure they are mapped |
| **Sandbox not working** | Set Login URL to `https://test.salesforce.com` |
| Check logs | Errors are written to the WordPress debug log (`WP_DEBUG_LOG`) with prefix `PTF Salesforce Error:` |

---

## 🔗 Webhook Integrations

Send form data to external systems automatically.

### Supported Platforms

- **Power Automate** - Microsoft Flow
- **Zapier** - 5000+ app integrations
- **Make (Integromat)** - Visual automation
- **Custom API** - Any endpoint

### Setup

1. Go to **Settings → Webhook / API Integrations**
2. Enable webhooks
3. Click platform button or add custom webhook
4. Enter Webhook URL
5. Save

### JSON Configuration

```json
[
  {
    "name": "Power Automate",
    "type": "power_automate",
    "url": "https://prod-xx.westeurope.logic.azure.com/...",
    "method": "POST",
    "active": true
  },
  {
    "name": "CRM API",
    "type": "custom",
    "url": "https://api.crm.com/v1/leads",
    "method": "POST",
    "auth_type": "bearer",
    "auth_value": "your-token",
    "active": true
  }
]
```

### Webhook Payload

```json
{
  "meta": {
    "source": "pentest-quote-form",
    "version": "1.4.1",
    "site_name": "Your Site",
    "submitted_at": "2024-01-15 14:30:00"
  },
  "contact": {
    "name": "John Smith",
    "email": "john@company.com",
    "phone": "+1 555 123 4567",
    "company": "Company Inc."
  },
  "selected_categories": [...],
  "answers": [...],
  "flat": {...}
}
```

---

## 📊 Managing Submissions

View submissions from **WordPress Admin → Quote Requests**.

### Status Options

| Status | Description |
|--------|-------------|
| 🆕 new | New submission |
| 📞 contacted | Contact made |
| 💰 quoted | Quote sent |
| ✅ completed | Completed |

### CSV Export

Click **Export as CSV** to download all records.

---

## 🛡️ Security

### reCAPTCHA v3

1. Get keys from [Google reCAPTCHA](https://www.google.com/recaptcha/admin/create)
2. Select **reCAPTCHA v3**
3. Enter Site Key and Secret Key in **Settings → reCAPTCHA**

### Corporate Email Filter

Automatically blocks personal emails:
- gmail.com, outlook.com, hotmail.com
- yahoo.com, yandex.com, icloud.com
- And 50+ other providers

### Rate Limiting

- Maximum 5 submissions per IP per hour
- Automatic spam protection

### SSRF Protection

- Localhost/internal IPs blocked for webhooks
- Only HTTPS/HTTP accepted
- Metadata endpoints blocked

---

## 🎨 Style Customization

### Via Shortcode

```
[ptf_popup_trigger primary="#E74C3C" secondary="#2ECC71"]
[ptf_multistep_form primary="#9B59B6"]
```

### Via CSS

```css
/* Button style */
.ptf-popup-trigger {
    border-radius: 8px !important;
}

/* Form container */
.ptf-form-wrapper {
    box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
}
```

### CSS Variables

| Variable | Usage |
|----------|-------|
| `--ptf-primary` | Primary color |
| `--ptf-secondary` | Secondary color |
| `--ptf-button-text` | Button text color |

---

## 🔧 Troubleshooting

### Form Not Showing

1. Check shortcode syntax
2. Check browser console for JS errors (F12)
3. Test for theme/plugin conflicts

### Emails Not Sending

1. Verify **Send Email Notification** is enabled
2. Check WordPress email settings
3. Use SMTP plugin (WP Mail SMTP)

### Webhook Errors

1. Verify URL is correct
2. Use HTTPS
3. Test with the test button
4. Check WordPress error logs

### Database Issues

1. Deactivate plugin
2. Reactivate
3. Check for `wp_ptf_submissions` table

---

## ❓ FAQ

**Q: Can I use multiple forms on one page?**
A: Yes, each form works independently.

**Q: Where is data stored?**
A: In `wp_ptf_submissions` table in WordPress database.

**Q: Is it GDPR compliant?**
A: Yes, includes privacy consent, CSV export, and record deletion.

**Q: Can I remove corporate email filter?**
A: Requires code modification.

---

## 📚 Technical Reference

### File Structure

```
pentest-quote-form/
├── assets/
│   ├── css/
│   │   ├── admin-questions.css
│   │   ├── admin-settings.css
│   │   └── form-styles.css
│   └── js/
│       ├── admin-questions.js
│       ├── admin-settings.js
│       ├── admin-utils.js
│       └── form-scripts.js
├── includes/
│   ├── class-form.php
│   ├── class-form-admin.php
│   ├── class-form-questions.php
│   └── class-form-settings.php
├── pentest-quote-form.php
└── uninstall.php
```

### Hooks & Filters

```php
// After submission
do_action('ptf_after_submission', $form_data, $submission_id);

// Email content filter
$content = apply_filters('ptf_email_content', $content, $form_data);

// Webhook payload filter
$payload = apply_filters('ptf_webhook_payload', $payload, $form_data);

// Salesforce: modify record before sending
$record = apply_filters('ptf_salesforce_record', $record, $form_data, $sf_object);

// Salesforce: fires after successful record creation
do_action('ptf_salesforce_record_created', $sf_id, $form_data, $sf_object);
```

### Database Schema

```sql
CREATE TABLE wp_ptf_submissions (
    id bigint(20) AUTO_INCREMENT,
    first_name varchar(255),
    email varchar(255),
    phone varchar(50),
    company varchar(255),
    test_types text,
    target_scope text,
    kvkk_consent tinyint(1),
    page_url varchar(500),
    user_ip varchar(45),
    submitted_at datetime,
    status varchar(20) DEFAULT 'new',
    PRIMARY KEY (id)
);
```

---

## 📄 License

GPL v2 or later. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

---

## 🆘 Support

- **Issues:** [GitHub Issues](https://github.com/lostarus/Wordpress-MultiPage-Form/issues)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md)

---

## 👨‍💻 Author

**mustafaer** - [GitHub](https://github.com/mustafaer)

---

⭐ If you find this plugin useful, please give it a star on GitHub!

---

**Pentest Quote Form** - Professional WordPress plugin for cybersecurity penetration test quote forms.
