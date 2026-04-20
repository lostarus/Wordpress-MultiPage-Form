# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.1] - 2026-04-20

### Added
- **Salesforce Test Connection**: New "Test Connection" button to verify Salesforce integration before form submissions
- **Activity Log**: Real-time activity log showing last 20 Salesforce events (successes and failures)
- **Error Visibility**: Detailed error messages displayed in admin panel instead of just debug logs
- **Clear Logs**: Ability to clear Salesforce activity logs from admin panel

### Improved
- **Error Logging**: All Salesforce errors now logged to database for admin visibility
- **Success Tracking**: Successful record creations are also logged with Salesforce record IDs
- **Troubleshooting**: Expandable data view for failed submissions to see what was sent

## [1.5.0] - 2026-04-20

### Added
- **Salesforce External Client App Support**: Now supports the new Salesforce External Client App structure with Client Credentials OAuth flow (recommended)
- **OAuth Flow Selection**: Choose between Client Credentials (recommended) or Password Grant (legacy) authentication methods
- **Backward Compatibility**: Existing Password Grant setups continue to work without changes
- **Migration Guide**: Documentation for migrating from Password Grant to Client Credentials flow

### Improved
- **Admin UI**: Reorganized Salesforce settings with clear authentication method selection
- **Status Indicator**: Dynamic status indicator that adapts to the selected authentication flow
- **Error Messages**: More specific error messages for each authentication method
- **Documentation**: Comprehensive guide for both External Client App and legacy Connected App setup

### Changed
- Client Credentials flow is now the default and recommended authentication method
- Salesforce settings section now shows/hides fields based on selected OAuth flow

## [1.3.0] - 2026-03-12

### Added
- **Typography Settings**: Customize font family (8 Google Fonts + system + custom) and font sizes for headings, body text, and labels
- **Form Header Customization**: Customize the form title and subtitle from Settings > Form Labels
- **Validation Messages Customization**: All validation error messages are now customizable (required, email, phone, etc.)
- **Radio & Checkbox Styling**: Proper inline styling for radio buttons and checkbox question types

### Removed
- **Yes/No Answer Type**: Removed redundant yes_no question type - use Dropdown with Yes/No options instead
- **Button Animations**: Removed all hover animations (transform, shadow) from buttons for cleaner UX

## [1.2.0] - 2026-03-12

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

