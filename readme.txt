=== Pentest Quote Form ===
Contributors: mustafaer
Tags: form, quote, cybersecurity, multi-step form, popup form, pentest, salesforce
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cybersecurity penetration test quote form - Multi-step form with popup and inline form support. Includes Salesforce and Webhook/API integrations.

== Description ==

Pentest Quote Form is a professional WordPress plugin specially designed for cybersecurity penetration test requests.

= Features =

* Multi-step form structure
* Popup and inline form support
* Dynamic category and question management
* Google reCAPTCHA v3 integration
* **Salesforce Direct Integration** (External Client App & Legacy Connected App support)
* Webhook/API integrations (Power Automate, Zapier, Make, Custom)
* Corporate email filter
* GDPR-compliant consent mechanism
* Fully customizable form labels and texts
* CSV export feature

== Installation ==

1. Upload the plugin to `/wp-content/plugins/pentest-quote-form` directory
2. Activate the plugin from WordPress Admin panel
3. Configure from the settings page

== Changelog ==

= 1.5.0 =
* Added: Salesforce External Client App support with Client Credentials OAuth flow (recommended)
* Added: OAuth flow selection - choose between Client Credentials or Password Grant authentication
* Added: Migration guide for transitioning from Password Grant to Client Credentials
* Improved: Admin UI with reorganized Salesforce settings and dynamic field visibility
* Improved: Status indicator adapts to selected authentication flow
* Improved: More specific error messages for each authentication method
* Changed: Client Credentials flow is now the default for new installations
* Maintained: Full backward compatibility with existing Password Grant setups

= 1.3.0 =
* Added: Typography settings (font family and sizes)
* Added: Form header customization
* Added: Validation messages customization
* Added: Radio & checkbox inline styling
* Removed: Yes/No answer type (use Dropdown instead)
* Removed: Button hover animations

= 1.2.0 =
* Added: Button text color customization option
* Added: Button size settings (Small, Medium, Large, Extra Large, Custom)
* Added: Custom button size with pixel values (padding and font size)
* Added: Live preview for all color and size settings in admin panel
* Added: Button styling applied to Next, Submit, and Popup trigger buttons
* Improved: reCAPTCHA error handling with user-friendly messages
* Improved: Admin settings preview now updates instantly for all options
* Updated: Documentation with new button customization features

= 1.1.0 =
* Added: Fully customizable form labels and texts from Settings panel
* Added: Progress bar step names customization
* Added: Page titles and descriptions customization for each step
* Added: Form field labels and placeholders customization
* Added: Button texts customization (Continue, Back, Submit)
* Added: Privacy consent texts customization
* Added: Success and loading messages customization
* Added: Comprehensive documentation (DOCUMENTATION.md)
* Changed: Removed hover animations from popup trigger button
* Changed: Improved settings panel organization
* Fixed: Field labels now properly fall back to defaults

= 1.0.0 =
* Initial public release
* Multi-step form structure
* Popup and inline form support
* Dynamic category and question management
* Google reCAPTCHA v3 integration
* Webhook/API integrations (Power Automate, Zapier, Make, etc.)
* Corporate email filter
* GDPR-compliant consent mechanism
* CSV export feature
* Mobile-responsive design

