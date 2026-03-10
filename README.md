# Pentest Quote Form

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.1.0-orange.svg)](https://github.com/lostarus/Wordpress-MultiPage-Form/releases)

A professional WordPress plugin for cybersecurity penetration test quote requests.

## 🚀 Features

- ✅ Multi-step form structure with progress indicator
- ✅ Popup and inline form support
- ✅ Dynamic category and question management
- ✅ Google reCAPTCHA v3 integration
- ✅ Webhook/API integrations (Power Automate, Zapier, Make, etc.)
- ✅ Corporate email filter
- ✅ GDPR-compliant consent mechanism
- ✅ Customizable colors and texts
- ✅ CSV export feature
- ✅ Mobile-responsive design
- ✅ Email notifications
- ✅ Admin dashboard for submissions

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## 📦 Installation

### WordPress Admin Panel

1. Download the latest release as ZIP from [Releases](https://github.com/lostarus/Wordpress-MultiPage-Form/releases)
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and activate

### FTP/File Manager

1. Upload the `pentest-quote-form` folder to `/wp-content/plugins/` directory
2. WordPress Admin → Plugins → Pentest Quote Form → Activate

### From Source

```bash
cd /wp-content/plugins/
git clone https://github.com/lostarus/Wordpress-MultiPage-Form.git pentest-quote-form
```

## 🔧 Usage

### Popup Button

```
[ptf_popup_trigger]
[ptf_popup_trigger text="Get Quote"]
[ptf_popup_trigger text="Request Quote" class="custom-class"]
```

### Inline Form

```
[ptf_multistep_form]
[ptf_multistep_form title="Quote Form"]
```

## ⚙️ Configuration

1. Go to **Quote Requests → Settings** in WordPress admin
2. Configure notification email, colors, and reCAPTCHA settings
3. Go to **Quote Requests → Questions** to manage form questions

### Webhook Integration

The plugin supports webhook integrations for automation:

1. Go to **Quote Requests → Settings**
2. Enable Webhook and enter your endpoint URL
3. Form submissions will be sent as JSON to your webhook

Compatible with:
- Microsoft Power Automate
- Zapier
- Make (Integromat)
- n8n
- Custom endpoints

## 📸 Screenshots

### Multi-Step Form
The form guides users through multiple steps with a progress indicator.

### Admin Dashboard
Manage all form submissions from the WordPress admin panel.

## 🛡️ Security

- Input sanitization and validation
- Nonce verification for form submissions
- Capability checks for admin functions
- Prepared SQL statements
- reCAPTCHA protection

## 📝 License

GPL v2 or later - See [LICENSE](LICENSE) file

## 🆘 Support

For questions and issues, please use [GitHub Issues](https://github.com/lostarus/Wordpress-MultiPage-Form/issues).

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## 👨‍💻 Author

**mustafaer** - [GitHub](https://github.com/mustafaer)

---

⭐ If you find this plugin useful, please give it a star on GitHub!
