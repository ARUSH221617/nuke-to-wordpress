# Nuke to WordPress Migration Plugin

A professional WordPress plugin for migrating content from Nuke PHP CMS to WordPress seamlessly and efficiently.

## Features

- Migrate categories, articles, and images from Nuke to WordPress
- Batch processing to handle large datasets
- Progress tracking and resumable migrations
- Rollback capability for failed migrations
- Clean and modern admin interface using TailwindCSS
- Detailed logging and error reporting

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL/MariaDB database access to both Nuke and WordPress installations
- WordPress admin privileges

## Installation

1. Download the latest release from the releases page
2. Upload the plugin through WordPress admin or extract to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to 'Nuke to WP' in the admin menu to begin configuration

## Configuration

1. Go to 'Nuke to WP' → 'Settings'
2. Enter your Nuke database credentials:
   - Database host
   - Database name
   - Username
   - Password
3. Configure migration options:
   - Batch size
   - Content types to migrate
   - Media handling preferences

## Usage

1. Backup your WordPress site before starting migration
2. Go to 'Nuke to WP' → 'Migration'
3. Select content types to migrate
4. Click 'Start Migration'
5. Monitor progress through the dashboard

## Development

### Prerequisites

- Node.js (14.x or higher)
- Composer
- PHP 7.4+

### Setup

```bash
# Install dependencies
composer install
npm install

# Development
npm run dev

# Production build
npm run production
```

### File Structure

```
nuke-to-wordpress/
├── admin/
│   ├── css/
│   └── js/
├── includes/
│   └── class-migration-rollback.php
├── vendor/
├── nuke-to-wordpress.php
├── composer.json
└── package.json
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

For support, please open an issue in the GitHub repository or contact support@example.com

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Credits

- Built with [TailwindCSS](https://tailwindcss.com/)
- Uses [WP Background Processing](https://github.com/deliciousbrains/wp-background-processing)

## Changelog

### 1.0.0
- Initial release
- Basic migration functionality
- Category, article, and image migration support
- Migration rollback capability