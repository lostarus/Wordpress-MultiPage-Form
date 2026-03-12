# Pentest Quote Form - Complete User Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Quick Start](#quick-start)
4. [Shortcode Usage](#shortcode-usage)
5. [Settings Panel](#settings-panel)
6. [Question & Category Management](#question--category-management)
7. [Customizing Form Labels](#customizing-form-labels)
8. [Webhook Integrations](#webhook-integrations)
9. [Managing Form Submissions](#managing-form-submissions)
10. [Security Settings](#security-settings)
11. [Style & Color Customization](#style--color-customization)
12. [Frequently Asked Questions](#frequently-asked-questions)
13. [Troubleshooting](#troubleshooting)

---

## Introduction

**Pentest Quote Form** is a professional WordPress plugin designed for cybersecurity penetration test quote requests. It features a multi-step form structure, dynamic question management, and powerful integration capabilities.

### Key Features

| Feature | Description |
|---------|-------------|
| 🔄 Multi-Step Form | 3-step form structure with progress indicator |
| 🎯 Popup & Inline | Both popup and embedded form support |
| 📝 Dynamic Questions | Category-based question management |
| 🔗 Webhook/API | Power Automate, Zapier, Make integrations |
| 🛡️ reCAPTCHA v3 | Google reCAPTCHA for bot protection |
| 📧 Email Notifications | Automatic notifications and auto-reply |
| 📊 CSV Export | Export form data to CSV |
| 🎨 Customizable | Colors, texts, labels |
| 📱 Mobile Responsive | Fully responsive design |

---

## Installation

### Method 1: WordPress Admin Panel

1. Download the latest release as ZIP from [Releases](https://github.com/lostarus/Wordpress-MultiPage-Form/releases)
2. **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Activate**

### Method 2: FTP/File Manager

1. Upload the `pentest-quote-form` folder to `/wp-content/plugins/` directory
2. Activate the plugin from **WordPress Admin → Plugins**

### Method 3: Git Installation

```bash
cd /wp-content/plugins/
git clone https://github.com/lostarus/Wordpress-MultiPage-Form.git pentest-quote-form
```

### System Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

---

## Quick Start

After activating the plugin:

1. Go to **WordPress Admin → Quote Requests → Settings**
2. Enter your notification email address
3. Optionally customize colors
4. Add a shortcode to any page:
   - For popup button: `[ptf_popup_trigger]`
   - For inline form: `[ptf_multistep_form]`

---

## Shortcode Usage

### Popup Trigger Button

Adds a button that opens a popup form when clicked.

#### Basic Usage
```
[ptf_popup_trigger]
```

#### Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `text` | "Get Quick Quote" | Button text |
| `class` | - | Additional CSS class |
| `style` | - | Inline CSS styles |
| `primary` | #2F7CFF | Primary/background color |
| `secondary` | #B7FF10 | Secondary color |
| `size` | medium | Button size: `small`, `medium`, `large`, `xlarge`, `custom` |
| `padding_y` | - | Custom vertical padding in pixels (overrides size) |
| `padding_x` | - | Custom horizontal padding in pixels (overrides size) |
| `font_size` | - | Custom font size in pixels (overrides size) |

#### Examples

```
[ptf_popup_trigger text="Get Quote"]
```

```
[ptf_popup_trigger text="Request Quote Now" class="my-custom-btn"]
```

```
[ptf_popup_trigger text="Free Quote" primary="#FF5733" secondary="#33FF57"]
```

#### Button Size Examples

```
[ptf_popup_trigger size="small"]
[ptf_popup_trigger size="large"]
[ptf_popup_trigger size="xlarge" text="Get Your Quote Now"]
```

#### Custom Size (px) Examples

```
[ptf_popup_trigger padding_y="20" padding_x="50" font_size="18"]
[ptf_popup_trigger padding_y="30" padding_x="60" font_size="22" text="Request Quote"]
```

#### Button Size Reference

| Size | Padding | Font Size |
|------|---------|-----------|
| small | 12px 24px | 14px |
| medium | 16px 32px | 16px |
| large | 20px 40px | 18px |
| xlarge | 24px 48px | 20px |
| custom | Your values | Your value |

---

### Inline Form

Embeds the form directly into page content.

#### Basic Usage
```
[ptf_multistep_form]
```

#### Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `primary` | #2F7CFF | Primary color |
| `secondary` | #B7FF10 | Secondary color |

#### Examples

```
[ptf_multistep_form]
```

```
[ptf_multistep_form primary="#E74C3C" secondary="#2ECC71"]
```

---

## Settings Panel

Access all plugin settings from **WordPress Admin → Quote Requests → Settings**.

### Email Settings

| Setting | Description |
|---------|-------------|
| **Notification Email Address** | Email to receive form submissions. For multiple addresses, separate with comma: `sales@company.com, support@company.com` |
| **Auto Reply** | Sends automatic confirmation email to form submitter |

### Data Storage Settings

| Setting | Description |
|---------|-------------|
| **Save to Database** | Stores form data in WordPress database |
| **Send Email Notification** | Sends email notification for each form submission |

> ⚠️ **Warning:** At least one option must be active!

### Color Settings

| Setting | Default | Usage |
|---------|---------|-------|
| **Primary Color** | #2F7CFF | Buttons, active elements, highlights |
| **Secondary Color** | #B7FF10 | Success icons, accent elements |
| **Button Text Color** | #FFFFFF | Text color for popup trigger button |
| **Button Size** | Medium | Size preset for popup trigger button |

#### Button Size Options

| Size | Description |
|------|-------------|
| **Small** | Compact size (12px 24px padding, 14px font) |
| **Medium** | Default size (16px 32px padding, 16px font) |
| **Large** | Larger size (20px 40px padding, 18px font) |
| **Extra Large** | Biggest preset (24px 48px padding, 20px font) |
| **Custom (px)** | Define your own padding and font size values |

#### Custom Button Size

When you select "Custom (px)" for Button Size, additional fields appear:

| Field | Range | Default | Description |
|-------|-------|---------|-------------|
| **Vertical Padding** | 4-60 px | 16 | Top and bottom padding |
| **Horizontal Padding** | 8-100 px | 32 | Left and right padding |
| **Font Size** | 10-32 px | 16 | Button text size |

### Text Settings

| Setting | Description |
|---------|-------------|
| **Default Button Text** | Default text for popup trigger button |
| **Success Message** | Message displayed after successful form submission |

### Privacy Links

| Setting | Example |
|---------|---------|
| **Privacy Notice URL** | `/privacy-notice` or `https://site.com/privacy-notice` |
| **Privacy Policy URL** | `/privacy-policy` or `https://site.com/privacy-policy` |

---

## Question & Category Management

Manage form questions from **WordPress Admin → Quote Requests → Questions**.

### Adding a Category (Test Type)

1. Click "Add New Category" button
2. Fill in category details:
   - **Category Name:** e.g., "Web Application Penetration Test"
   - **Category ID:** e.g., "web-application" (unique, lowercase, with hyphens)
   - **Icon:** Emoji or icon character (📱, 🌐, 🔒, etc.)
3. You can activate/deactivate categories

### Adding Questions

Add custom questions to each category:

1. Click "Add Question" button on the category card
2. Select question type:

| Question Type | Description | Use Case |
|---------------|-------------|----------|
| **text** | Single line text | Short answers |
| **textarea** | Multi-line text | Long descriptions |
| **number** | Numeric value | Quantities, counts |
| **select** | Dropdown list | Single selection from options |
| **radio** | Radio buttons | Single selection (visible options) |
| **checkbox** | Checkboxes | Multiple selection |
| **date** | Date picker | Date selection |
| **email** | Email | Email format validation |
| **tel** | Phone | Phone number input |

3. Enter question details:
   - **Question Text:** Text displayed to user
   - **Question ID:** Unique identifier
   - **Placeholder:** Example value
   - **Required:** Whether answer is mandatory

### Questions with Options

For `select`, `radio`, `checkbox` types, add options:

```
Option 1|option1
Option 2|option2
Option 3|option3
```

Format: `Display Text|value`

---

## Customizing Form Labels

Customize all form texts from **Settings → Form Labels & Texts**.

### Form Header

| Field | Default | Description |
|-------|---------|-------------|
| **Form Title** | Get Quick Quote | Main title shown at the top of the form |
| **Form Subtitle** | Get a quote for your cybersecurity needs | Subtitle/description below the title |

### Progress Bar Step Names

| Field | Default | Description |
|-------|---------|-------------|
| **Step 1** | Test Selection | First step name |
| **Step 2** | Test Details | Second step name |
| **Step 3** | Contact Information | Third step name |

### Step 1: Test Selection Page

| Field | Default |
|-------|---------|
| **Page Title** | Test Type Selection |
| **Page Description** | Which security test(s) would you like a quote for? |
| **Multi-select Hint** | (You can select multiple) |

### Step 2: Test Details Page

| Field | Default |
|-------|---------|
| **Page Title** | Test Details |
| **Page Description** | Please provide details about the selected tests. |

### Step 3: Contact Information Page

| Field | Default |
|-------|---------|
| **Page Title** | Contact Information |
| **Page Description** | Please enter your information so we can contact you. |

### Form Fields

| Field | Label | Placeholder |
|-------|-------|-------------|
| **Company** | Company Name | Company name |
| **Contact Person** | Contact Person | Your Full Name |
| **Email** | Email | corporate@yourcompany.com |
| **Phone** | Phone | +1 555 XXX XXXX |

### Email Field Special Settings

| Field | Default |
|-------|---------|
| **Hint Text** | Only corporate email addresses are accepted. |

### Privacy Consent Texts

| Field | Default |
|-------|---------|
| **Consent Text Start** | I have read, understood and accept the |
| **Privacy Notice Link** | Privacy Notice |
| **"And" Connector** | and |
| **Privacy Policy Link** | Privacy Policy |

### Button Texts

| Field | Default |
|-------|---------|
| **Next/Continue Button** | Continue |
| **Back Button** | Back |
| **Submit Button** | Submit |

### Messages

| Field | Default |
|-------|---------|
| **Success Title** | Thank You! |
| **Loading Text** | Sending... |

---

## Webhook Integrations

Automatically send form data to external systems.

### Enabling Webhooks

1. Go to **Settings → Webhook / API Integrations**
2. Check "Enable Webhook/API integrations"
3. Use ready templates or add custom webhook

### Supported Platforms

| Platform | Description |
|----------|-------------|
| **Power Automate** | Microsoft Flow integration |
| **Zapier** | 5000+ app integrations |
| **Make (Integromat)** | Visual automation platform |
| **Custom API** | Custom endpoint |

### Quick Template Setup

1. Click the relevant platform button (Power Automate, Zapier, etc.)
2. Enter your Webhook URL
3. Save

### JSON Configuration

For advanced users, JSON editor is available:

```json
[
  {
    "name": "Power Automate - Notification",
    "type": "power_automate",
    "url": "https://prod-xx.westeurope.logic.azure.com/workflows/...",
    "method": "POST",
    "active": true
  },
  {
    "name": "CRM Integration",
    "type": "custom",
    "url": "https://api.crm.com/v1/leads",
    "method": "POST",
    "auth_type": "bearer",
    "auth_value": "your-api-token",
    "active": true
  }
]
```

### Webhook Properties

| Property | Values | Description |
|----------|--------|-------------|
| `name` | String | Webhook name |
| `type` | custom, power_automate, zapier, make | Platform type |
| `url` | URL | Endpoint address |
| `method` | POST, PUT, PATCH | HTTP method |
| `auth_type` | none, bearer, basic, api_key | Authentication type |
| `auth_value` | String | Token or credentials |
| `active` | true, false | Active/Inactive status |

### Webhook Payload Structure

JSON structure sent to webhooks:

```json
{
  "meta": {
    "source": "pentest-quote-form",
    "version": "1.3.0",
    "site_name": "Site Name",
    "site_url": "https://yoursite.com",
    "submitted_at": "2024-01-15 14:30:00",
    "page_url": "https://yoursite.com/quote",
    "user_ip": "192.168.1.1"
  },
  "contact": {
    "name": "John Smith",
    "email": "john@company.com",
    "phone": "+1 555 123 4567",
    "company": "Company Inc."
  },
  "selected_categories": [
    {
      "id": "web-application",
      "name": "Web Application Penetration Test"
    }
  ],
  "answers": [
    {
      "category_id": "web-application",
      "category_name": "Web Application Penetration Test",
      "questions": [
        {
          "id": "url_count",
          "question": "How many URLs will be tested?",
          "type": "number",
          "answer": 5
        }
      ]
    }
  ],
  "flat": {
    "first_name": "John Smith",
    "email": "john@company.com",
    "phone": "+1 555 123 4567",
    "company": "Company Inc.",
    "test_types": ["web-application"],
    "url_count": 5
  }
}
```

### Testing Webhooks

1. After configuring your webhooks
2. Click "Test Webhooks" button
3. Review test results

---

## Managing Form Submissions

View all form submissions from **WordPress Admin → Quote Requests**.

### List View

| Column | Description |
|--------|-------------|
| **ID** | Unique record number |
| **Date** | Submission date |
| **Company** | Company name |
| **Contact** | Contact person name |
| **Email** | Email address |
| **Phone** | Phone number |
| **Test Types** | Selected tests |
| **Status** | new, contacted, quoted, completed |

### Status Management

| Status | Description |
|--------|-------------|
| 🆕 **new** | New submission |
| 📞 **contacted** | Contact made |
| 💰 **quoted** | Quote sent |
| ✅ **completed** | Completed |

### Viewing Details

Click any record to view details:
- All form answers
- Test details
- Submission info (IP, page URL, date)

### CSV Export

1. Click "Export as CSV" button on the list page
2. All records will be downloaded in CSV format

### Deleting Records

1. Hover over the record you want to delete
2. Click "Delete" link
3. Confirm

---

## Security Settings

### reCAPTCHA v3

Use Google reCAPTCHA v3 for bot protection:

1. Go to [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin/create)
2. Select **reCAPTCHA v3**
3. Get Site and Secret Keys
4. Enter them in **Settings → reCAPTCHA Settings**

| Field | Description |
|-------|-------------|
| **Site Key** | Public key (frontend) |
| **Secret Key** | Private key (backend) |

### Corporate Email Filter

The form automatically rejects personal email addresses:

**Blocked domains:**
- gmail.com, gmail.com.tr
- outlook.com, hotmail.com
- yahoo.com, yandex.com
- icloud.com, mail.com
- And other personal email providers

### Rate Limiting

- Maximum 5 form submissions per IP per hour
- Automatic spam protection

### SSRF Protection

Security checks for webhook URLs:
- Localhost and internal IPs are blocked
- Only HTTPS/HTTP protocols accepted
- Metadata endpoints are blocked

---

## Style & Color Customization

### Color Settings

Change colors from **Settings → Color Settings**:

| Color | CSS Variable | Usage |
|-------|--------------|-------|
| Primary Color | `--ptf-primary` | Buttons, active elements |
| Secondary Color | `--ptf-secondary` | Success icon |
| Button Text Color | `--ptf-button-text` | Popup trigger button text |

### Button Size Settings

Customize button size from **Settings → Color Settings → Button Size**:

| Preset | Padding | Font Size |
|--------|---------|-----------|
| Small | 12px 24px | 14px |
| Medium | 16px 32px | 16px |
| Large | 20px 40px | 18px |
| Extra Large | 24px 48px | 20px |
| Custom | Your values | Your value |

### Changing Colors via Shortcode

```
[ptf_popup_trigger primary="#E74C3C" secondary="#2ECC71"]
[ptf_multistep_form primary="#9B59B6" secondary="#F1C40F"]
```

### Changing Button Size via Shortcode

```
[ptf_popup_trigger size="large"]
[ptf_popup_trigger size="xlarge" text="Get Quote Now"]
[ptf_popup_trigger padding_y="25" padding_x="50" font_size="20"]
```

### CSS Customization

Add to your theme's CSS file:

```css
/* Button style */
.ptf-popup-trigger {
    border-radius: 8px !important;
    font-size: 18px !important;
}

/* Form container */
.ptf-form-wrapper {
    box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
}

/* Checkbox hover color */
.ptf-checkbox-item:hover {
    border-color: #your-color !important;
}
```

---

## Frequently Asked Questions

### Why isn't the form showing?

1. Make sure you typed the shortcode correctly
2. Check for JavaScript errors (F12 → Console)
3. There might be conflicts with theme or other plugins

### Email notifications are not coming

1. Check that **Settings → Send Email Notification** is active
2. Verify WordPress email settings
3. Try using an SMTP plugin (WP Mail SMTP, etc.)

### Can I remove the corporate email requirement?

By default, personal emails are blocked. Changing this requires code modification.

### Can I use multiple forms?

Yes, you can use multiple shortcodes on the same page. Each form works independently.

### Where is form data stored?

In the WordPress database in the `wp_ptf_submissions` table.

### Is it GDPR compliant?

Yes, the form includes privacy consent. You can also export data to CSV and delete records.

---

## Troubleshooting

### JavaScript Errors

1. Open browser console (F12)
2. Check error messages
3. There might be theme or plugin conflicts

### CSS Conflicts

If form styles are broken:

```css
/* Increase specificity */
body .ptf-form-wrapper {
    /* your styles */
}
```

### Webhook Errors

1. Make sure URL is correct
2. Use HTTPS
3. Test with the test button
4. Check WordPress error logs

### Database Issues

If table wasn't created:

1. Deactivate the plugin
2. Reactivate it
3. Check for `wp_ptf_submissions` table

---

## Technical Information

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
├── uninstall.php
└── DOCUMENTATION.md
```

### Hooks & Filters

```php
// After form submission
do_action('ptf_after_submission', $form_data, $submission_id);

// Before sending email
$email_content = apply_filters('ptf_email_content', $content, $form_data);

// Webhook payload
$payload = apply_filters('ptf_webhook_payload', $payload, $form_data);
```

### Database Table

```sql
CREATE TABLE wp_ptf_submissions (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    first_name varchar(255) NOT NULL,
    last_name varchar(255) NOT NULL,
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

---

## Support

- **GitHub Issues:** [Bug reports and feature requests](https://github.com/lostarus/Wordpress-MultiPage-Form/issues)
- **Documentation:** This file
- **Changelog:** [CHANGELOG.md](CHANGELOG.md)

---

## License

This plugin is licensed under GPL v2 or later.

---

**Pentest Quote Form** - Professional WordPress plugin for cybersecurity penetration test quote forms.

