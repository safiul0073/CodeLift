# CodeLifter
## Laravel Package for Remote Project Updates

CodeLifter is a **Laravel package** that enables remote project updates by downloading, extracting, and applying ZIP files programmatically. It provides a simple interface to check for updates via an API, download update files, and apply them seamlessly through an admin interface. The package is framework-integrated, leveraging Laravel’s ecosystem for robust update management.

- Simple API-driven update checking
- Programmatic ZIP file download and extraction
- Admin interface integration for update management
- Framework-agnostic core with Laravel-specific bindings
- PSR-12 compliant

## Installation

You can install the package using [Composer](https://getcomposer.org). Run the following command in your Laravel project:

```bash
composer require safiul0073/code-lifter
```

## Getting Started

### Configuration

Update your `.env` file to include the API endpoint for checking updates:

```env
UPDATE_API_URL=https://example.com/api/updates
APP_NAME=YourApplicationName
```
### Publishing (Optional)

To publish the package's configuration, run the following command:

```bash
php artisan vendor:publish --tag=lifter-config
```

### Code Examples

Below is an example of how to use CodeLifter to check and apply updates:

```php
use Safiul0073\CodeLifter\Version;

$version = app(Version::class);

// Initialize with app name and API URL
$version->setup(env('APP_NAME'), env('UPDATE_API_URL'));

// Check for available updates
$response = $version->check('check-url');
// Expected API response format:
// [
//     "is_update_available" => true,
//     "file_path" => "https://example.com/update.zip",
//     "update_logs" => ["Version 1.1.0: Added new features"]
// ]

// Process the update by downloading and extracting the ZIP file
if ($response['is_update_available']) {
    $version->process($response['file_path']);
}
```

### Admin Interface

CodeLifter provides a simple admin interface for managing updates. After installation, you can access the update dashboard at `/admin/code-lifter` (ensure you configure your routes and middleware). The interface allows you to:

- Check for updates manually
- View update logs
- Trigger the update process

## Requirements

Ensure your server meets the following requirements before installing:

- PHP >= 8.2
- Laravel >= 10.0
- Mbstring PHP Extension
- ZipArchive PHP Extension
- cURL PHP Extension

## Security

If you discover any security-related issues, please email mdsafiul0073@gmail.com directly.

## Authors

This package is developed and maintained by [Safiul](https://github.com/safiul0073).

## License

CodeLifter is licensed under the [MIT License](LICENSE).
