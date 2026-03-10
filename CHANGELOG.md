# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-03-10

### Added
- **Button Text Color**: New color picker to customize text color for all primary buttons
- **Button Size Presets**: Choose from Small, Medium, Large, or Extra Large button sizes
- **Custom Button Size**: Define exact pixel values for padding (vertical/horizontal) and font size
- **Live Preview**: All color and size settings now update instantly in the admin preview
- **Enhanced Button Styling**: Button text color now applies to Next, Submit, and Popup trigger buttons

### Improved
- **reCAPTCHA Error Handling**: Better error catching with user-friendly messages instead of silent failures
- **Admin Settings Preview**: Color picker changes now reflect immediately without saving
- **Documentation**: Updated with comprehensive button customization guide

### Fixed
- Form no longer gets stuck on "Sending..." when reCAPTCHA fails
- Button text color preview now updates in real-time

## [1.1.0] - 2026-03-10

### Added
- **Customizable Form Labels**: All form texts are now fully customizable from the Settings panel
  - Progress bar step names (Step 1, 2, 3)
  - Page titles and descriptions for each step
  - All form field labels and placeholders
  - Button texts (Continue, Back, Submit)
  - Privacy consent texts
  - Success and loading messages
- **Comprehensive Documentation**: Added detailed DOCUMENTATION.md with complete user guide

### Changed
- Removed hover animations from popup trigger button for cleaner UX
- Improved settings panel organization with categorized sections
- Enhanced deep merge for nested field labels configuration

### Fixed
- Field labels now properly fall back to defaults when not set

## [1.0.0] - 2026-03-05

### Added
- Initial public release
- Multi-step form structure with progress indicator
- Popup form support with customizable trigger button
- Inline form support via shortcode
- Dynamic category and question management from admin panel
- Google reCAPTCHA v3 integration for spam protection
- Webhook/API integrations (Power Automate, Zapier, Make, etc.)
- Corporate email filter to block free email providers
- GDPR-compliant consent mechanism
- Customizable colors and texts
- CSV export feature for form submissions
- Mobile-responsive design
- Email notifications for new submissions
- Admin dashboard for managing submissions

### Security
- Input sanitization and validation
- Nonce verification for form submissions
- Capability checks for admin functions
- Prepared SQL statements for database queries

[1.2.0]: https://github.com/lostarus/Wordpress-MultiPage-Form/releases/tag/v1.2.0
[1.1.0]: https://github.com/lostarus/Wordpress-MultiPage-Form/releases/tag/v1.1.0
[1.0.0]: https://github.com/lostarus/Wordpress-MultiPage-Form/releases/tag/v1.0.0

