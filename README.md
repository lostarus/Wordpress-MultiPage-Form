# Pentest Quote Form

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.5.5-orange.svg)](https://github.com/lostarus/Wordpress-MultiPage-Form/releases)
[![Tested up to](https://img.shields.io/badge/Tested%20up%20to-WP%206.4-brightgreen.svg)](https://wordpress.org/)

A professional WordPress plugin for cybersecurity penetration test quote requests with multi-step form, Salesforce integration, webhook/API integrations, and full customization options.

## 📖 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [Shortcode Usage](#-shortcode-usage)
- [Settings Panel](#%EF%B8%8F-settings-panel)
- [Question & Category Management](#-question--category-management)
- [Customizing Form Labels](#-customizing-form-labels)
- [Typography Settings](#-typography-settings)
- [Salesforce Integration](#-salesforce-integration)
- [Webhook Integrations](#-webhook-integrations)
- [Managing Submissions](#-managing-submissions)
- [Security](#%EF%B8%8F-security)
- [Style Customization](#-style-customization)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)
- [Technical Reference](#-technical-reference)
- [Changelog](#-changelog)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🚀 Features

### Core Features

| Feature | Description |
|---------|-------------|
| 🔄 **Multi-Step Form** | 3-step form with animated progress indicator |
| 🎯 **Popup & Inline** | Both popup modal and embedded form support |
| 📝 **Dynamic Questions** | Category-based question management with drag & drop |
| 📱 **Mobile Responsive** | Fully responsive design for all devices |
| ✅ **GDPR Compliant** | Built-in privacy consent mechanism |

### Integrations

| Feature | Description |
|---------|-------------|
| ☁️ **Salesforce** | Direct Lead/Contact/Opportunity creation via REST API |
| 🔗 **Webhooks** | Power Automate, Zapier, Make, custom API support |
| 📧 **Email** | Automatic notifications and auto-reply emails |

### Salesforce Features (v1.5.x)

| Feature | Description |
|---------|-------------|
| 🆕 **External Client App** | Modern Client Credentials OAuth flow (recommended) |
| 🔐 **Legacy Support** | Password Grant flow for backward compatibility |
| 🧪 **Connection Testing** | Test button to verify integration before going live |
| 📋 **Activity Log** | Real-time log of last 20 Salesforce events |
| 🗺️ **Field Mapping** | JSON-based field mapping with custom fields support |
| 🌐 **My Domain Support** | Custom Salesforce domain URL support |

### Security & Customization

| Feature | Description |
|---------|-------------|
| 🛡️ **reCAPTCHA v3** | Google reCAPTCHA bot protection |
| 🏢 **Corporate Email Filter** | Blocks 50+ personal email providers |
| 🎨 **Full Customization** | Colors, fonts, labels, messages |
| 🔤 **Typography** | 8 Google Fonts + system + custom fonts |
| 📊 **CSV Export** | Export all submissions to CSV |

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

The plugin supports two OAuth 2.0 authentication methods:

1. **Client Credentials Flow (Recommended)** - Uses the new Salesforce External Client App structure. No username/password required.
2. **Password Grant Flow (Legacy)** - Uses the traditional Connected App with username/password. Deprecated but supported for backward compatibility.

An access token is cached for 50 minutes and automatically refreshed when it expires.

### Prerequisites

#### Option 1: External Client App (Recommended)

This is the modern, secure way to integrate with Salesforce. No username/password required.

**Step-by-Step Setup in Salesforce:**

1. Log in to your Salesforce org
2. Go to **Setup** (gear icon → Setup)
3. In Quick Find, search for **"External Client App Manager"**
4. Click **"New External Client App"**
5. Fill in basic information:
   - **App Name**: `WordPress Form Integration`
   - **API Name**: `WordPress_Form_Integration`
   - **Contact Email**: Your email address
   - **Description**: (optional) WordPress form integration
6. Under **OAuth Settings**:
   - Check **Enable OAuth Settings**
   - **Callback URL**: `https://yoursite.com` (any valid URL, not actually used for Client Credentials)
   - ✅ Check **Enable Client Credentials Flow**
   - **Selected OAuth Scopes**: Add these:
     - `Manage user data via APIs (api)`
     - `Perform requests at any time (refresh_token, offline_access)`
7. Click **Save** and wait 2-10 minutes for activation

8. **Get Consumer Key and Secret**:
   - In **External Client App Manager**, find your app
   - Click on your app name to open it
   - Look for **OAuth Settings** or **App Credentials** section
   - Click **Manage Consumer Details** or similar
   - Verify your identity (Salesforce will send a verification code)
   - Copy the **Consumer Key** and **Consumer Secret**

9. **⚠️ CRITICAL - Set Run As User**:
   - In **External Client App Manager**, find your app
   - Click the dropdown arrow (▼) next to your app → **Manage**
   - Click **Edit Policies** 
   - Scroll to **Client Credentials Flow** section
   - **Select a Run As user** from the dropdown
     - This user's permissions will be used for all API calls
     - Choose a user with API access (System Administrator or Integration User)
   - Optionally, under **IP Relaxation**, select "Relax IP restrictions"
   - Click **Save**

> ⚠️ **Most Common Error**: "request not supported on this domain" means the **Run As user is NOT set**. Step 9 is mandatory!

**Required from Salesforce:**
- Consumer Key (Client ID)
- Consumer Secret
- A Run As user configured in App Policies

**Where to Find Things:**
| Item | Location in Salesforce Setup |
|------|------------------------------|
| Create/View App | Setup → External Client App Manager |
| Consumer Key/Secret | Your App → Manage Consumer Details |
| Run As User | Your App → Manage → Edit Policies → Client Credentials Flow |

#### Option 2: Connected App with Password Grant (Legacy)

⚠️ **Deprecated**: Salesforce is phasing out the Password Grant flow. Use External Client App with Client Credentials if possible.

1. A Salesforce org (Production or Sandbox)
2. A **Connected App** with OAuth enabled ([How to create one](https://help.salesforce.com/s/articleView?id=sf.connected_app_create.htm))
   - Enable OAuth Settings → check **Enable OAuth**
   - Add any Callback URL (e.g. `https://yoursite.com`)
   - Scopes: **api**, **refresh_token**
   - Important: Set IP policy to **Relax IP Restrictions** under Manage → Edit Policies
3. Your Salesforce **username**, **password**, and **security token**
   - Get security token: Setup → My Personal Information → Reset My Security Token

### Setup

1. Go to **WordPress Admin → Quote Requests → Settings**
2. Scroll to **Salesforce Direct Integration**
3. Check **Enable Salesforce Integration**
4. Select your **OAuth Flow**:

#### Client Credentials Flow (External Client App)

| Field | Description |
|-------|-------------|
| **OAuth Flow** | Select "Client Credentials (External Client App - Recommended)" |
| **Login URL / My Domain** | ⚠️ **Use your My Domain URL**: `https://yourcompany.my.salesforce.com` (Find it in Salesforce Setup → My Domain) |
| **Consumer Key** | From your External Client App → OAuth Settings |
| **Consumer Secret** | From your External Client App → OAuth Settings |
| **Salesforce Object** | `Lead` (default), `Contact`, `Account`, `Opportunity`, or `Case` |
| **API Version** | Default `v59.0` — match your org's API version if needed |

> ⚠️ **Important**: For Client Credentials flow, you should use your **My Domain URL** (e.g., `https://yourcompany.my.salesforce.com`) instead of `login.salesforce.com`. Using the generic login URL often results in "request not supported on this domain" error.

#### Password Grant Flow (Legacy Connected App)

| Field | Description |
|-------|-------------|
| **OAuth Flow** | Select "Password Grant (Legacy Connected App)" |
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

### Migrating from Password Grant to Client Credentials

If you're currently using the Password Grant flow and want to migrate to the recommended Client Credentials flow:

1. Create a new External Client App in Salesforce Setup
2. Enable Client Credentials Flow and set a Run As user
3. Copy the new Consumer Key and Consumer Secret
4. In WordPress settings, change OAuth Flow to "Client Credentials"
5. Update the Consumer Key and Consumer Secret
6. Save settings - username/password fields will be ignored

### Testing Your Connection

After configuring your Salesforce credentials:

1. Click the **Test Connection** button in the Salesforce settings section
2. The plugin will attempt to:
   - Authenticate with Salesforce using your credentials
   - Access the Salesforce REST API
   - Describe the target object (Lead, Contact, etc.)
3. On success, you'll see:
   - ✅ Instance URL confirmation
   - Number of available fields
   - List of required fields for the object
4. On failure, you'll see:
   - ❌ Which step failed (Authentication or API Access)
   - Detailed error message for troubleshooting

### Activity Log

The plugin maintains an activity log of the last 20 Salesforce events:

| Log Entry | Description |
|-----------|-------------|
| ✓ Success | Record created with Salesforce ID |
| ✗ Error | Failed submission with error message |

**Features:**
- View timestamp for each event
- See Salesforce record IDs for successful submissions
- Expand "View Data" on errors to see the exact JSON that was sent
- Clear logs with one click

This helps you:
- Verify that submissions are reaching Salesforce
- Debug field mapping issues
- Identify authentication problems
- Track integration health over time

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
| **"request not supported on this domain"** | **Most likely cause**: You need to use your My Domain URL. Go to Settings → Select "My Domain (Custom URL)" → Enter `https://yourcompany.my.salesforce.com`. Also ensure Run As user is set in External Client App Manager. |
| **Authentication failed (Client Credentials)** | 1) Use My Domain URL instead of login.salesforce.com 2) Ensure Run As user is assigned in Edit Policies |
| **Authentication failed (Password Grant)** | Double-check Consumer Key/Secret and that IP policy is set to *Relax IP Restrictions* |
| **Invalid client** | Consumer Key or Consumer Secret is wrong. Go to **External Client App Manager** → Your App → Manage Consumer Details |
| **Invalid password** | Make sure to append the security token directly to the password (Password Grant only) |
| **INVALID_FIELD error** | The mapped Salesforce field name is wrong or doesn't exist on the object |
| **Required field missing** | Lead requires `LastName` and `Company` — ensure they are mapped |
| **Sandbox not working** | Use your Sandbox My Domain URL: `https://yourcompany--sandboxname.sandbox.my.salesforce.com` |
| **Check Activity Log** | View the Activity Log in WordPress admin for detailed error messages and sent data |
| **Check WordPress logs** | Errors are also written to the WordPress debug log (`WP_DEBUG_LOG`) with prefix `PTF Salesforce Error:` |

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
    "version": "1.5.5",
    "site_name": "Your Site",
    "submitted_at": "2026-04-20 14:30:00"
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
├── pentest-quote-form.php    # Main plugin file
├── uninstall.php             # Clean uninstall handler
├── README.md                 # This documentation
├── CHANGELOG.md              # Version history
├── LICENSE                   # GPL v2 License
├── CONTRIBUTING.md           # Contribution guidelines
├── SECURITY.md               # Security policy
├── CODE_OF_CONDUCT.md        # Code of conduct
├── assets/
│   ├── css/
│   │   ├── admin-questions.css   # Question management styles
│   │   ├── admin-settings.css    # Settings page styles
│   │   └── form-styles.css       # Frontend form styles
│   └── js/
│       ├── admin-questions.js    # Question management scripts
│       ├── admin-settings.js     # Settings page scripts
│       ├── admin-utils.js        # Shared admin utilities
│       └── form-scripts.js       # Frontend form scripts
└── includes/
    ├── class-form.php            # Main form class & Salesforce integration
    ├── class-form-admin.php      # Admin submissions page
    ├── class-form-questions.php  # Question/category management
    └── class-form-settings.php   # Settings management
```

### Hooks & Filters

```php
// After successful form submission
do_action('ptf_after_submission', $form_data, $submission_id);

// Modify email content before sending
$content = apply_filters('ptf_email_content', $content, $form_data);

// Modify webhook payload before sending
$payload = apply_filters('ptf_webhook_payload', $payload, $form_data);

// Modify Salesforce record before sending
$record = apply_filters('ptf_salesforce_record', $record, $form_data, $sf_object);

// Fires after successful Salesforce record creation
do_action('ptf_salesforce_record_created', $sf_id, $form_data, $sf_object);

// Modify blocked email domains list
$domains = apply_filters('ptf_blocked_email_domains', $domains);
```

### Database Schema

**Submissions Table: `wp_ptf_submissions`**

```sql
CREATE TABLE wp_ptf_submissions (
    id bigint(20) AUTO_INCREMENT,
    first_name varchar(255) NOT NULL,
    last_name varchar(255) DEFAULT '',
    email varchar(255) NOT NULL,
    phone varchar(50) DEFAULT '',
    company varchar(255) NOT NULL,
    test_types text NOT NULL,
    target_scope text NOT NULL,
    kvkk_consent tinyint(1) DEFAULT 0,
    page_url varchar(500) DEFAULT '',
    user_ip varchar(45) DEFAULT '',
    submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
    status varchar(20) DEFAULT 'new',
    PRIMARY KEY (id)
);
```

**Questions Table: `wp_ptf_questions`**

```sql
CREATE TABLE wp_ptf_questions (
    id bigint(20) AUTO_INCREMENT,
    option_key varchar(100) NOT NULL DEFAULT 'categories',
    option_value longtext NOT NULL,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY option_key (option_key)
);
```

### REST API Endpoints

The plugin uses WordPress AJAX for form submission:

| Action | Endpoint | Description |
|--------|----------|-------------|
| `ptf_submit_form` | `admin-ajax.php` | Form submission handler |
| `ptf_test_webhooks` | `admin-ajax.php` | Webhook testing |
| `ptf_test_salesforce` | `admin-ajax.php` | Salesforce connection test |
| `ptf_save_questions` | `admin-ajax.php` | Save questions/categories |

---

## 📝 Changelog

### v1.5.5 (2026-04-20)
- ✅ Fixed: Content-Type header for Salesforce OAuth requests
- ✅ Fixed: Custom My Domain URLs now persist after save
- ✅ Fixed: Client Credentials flow works with My Domain

### v1.5.0 - v1.5.4
- ✨ Added: Salesforce External Client App support (Client Credentials flow)
- ✨ Added: Test Connection button for Salesforce
- ✨ Added: Activity Log for Salesforce events
- ✨ Added: My Domain URL support
- 🔧 Improved: Error messages with original Salesforce responses

### v1.3.0
- ✨ Added: Typography settings (8 Google Fonts)
- ✨ Added: Form header customization
- ✨ Added: Validation messages customization

### v1.2.0
- ✨ Added: Button text color and size options
- ✨ Added: Live preview in admin

### v1.1.0
- ✨ Added: Customizable form labels and texts
- ✨ Added: Comprehensive documentation

### v1.0.0
- 🎉 Initial public release

See [CHANGELOG.md](CHANGELOG.md) for full version history.

---

## 📄 License

This project is licensed under the GPL v2 or later. See [LICENSE](LICENSE) for details.

```
Pentest Quote Form - WordPress Plugin
Copyright (C) 2026 mustafaer

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

### How to Contribute

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/lostarus/Wordpress-MultiPage-Form.git

# Navigate to your WordPress plugins directory
mv Wordpress-MultiPage-Form /path/to/wordpress/wp-content/plugins/pentest-quote-form

# Activate the plugin in WordPress admin
```

---

## 🆘 Support

- **Issues:** [GitHub Issues](https://github.com/lostarus/Wordpress-MultiPage-Form/issues)
- **Discussions:** [GitHub Discussions](https://github.com/lostarus/Wordpress-MultiPage-Form/discussions)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md)
- **Security:** [SECURITY.md](SECURITY.md)

---

## 👨‍💻 Author

**mustafaer** - [GitHub](https://github.com/mustafaer)

---

## ⭐ Show Your Support

If you find this plugin useful, please consider:
- Giving it a ⭐ star on GitHub
- Sharing it with others
- [Contributing](#-contributing) to the project

---

<p align="center">
  <strong>Pentest Quote Form</strong><br>
  Professional WordPress plugin for cybersecurity penetration test quote forms.
  <br><br>
  <a href="https://github.com/lostarus/Wordpress-MultiPage-Form/releases">Download Latest Release</a>
  ·
  <a href="https://github.com/lostarus/Wordpress-MultiPage-Form/issues">Report Bug</a>
  ·
  <a href="https://github.com/lostarus/Wordpress-MultiPage-Form/issues">Request Feature</a>
</p>
