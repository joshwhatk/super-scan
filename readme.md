# Super-scan
Detect changed files using PHP/MySQL/CRON

## Introduction

This set of scripts is an evolution from what David K. Lynn offered as a SitePoint article and then through suggestions from Han Wechgelaer.

For the original code and readme, you can check the repository for which this is a fork: [SuperScan](https://github.com/dklynn/SuperScan). Although based on the same original code, this repository is now **very** different from David K. Lynn's version, so I have removed his readme as well.

**Super-scan** is a package built for Laravel that allows simple File change detection and reporting.

## Installation

Register the SuperScanServiceProvider in your `config/app.php`:

```
JoshWhatK\SuperScan\SuperScanServiceProvider::class,
```

Publish the config file and migrations:

```
php artisan vendor:publish --provider="JoshWhatK\SuperScan\SuperScanServiceProvider"
```

Change the default Account as you wish:

> Example

```
'account' => [

    /*
    |--------------------------------------------------------------------------
    | Default Account
    |--------------------------------------------------------------------------
    |
    | This account information is saved to the database during the super-scan
    | migrations. If you are not using the default
    | JoshWhatK\SuperScan\Database\Account model, then this can be removed.
    |
    */

    'defaut' => [
        'name' => 'Base Account',
        'server_name' => 'hostname',
        'ip_address' => '127.0.0.1',
        'scan_directory' => '/var/www/html' # no trailing slash needed
        'public_url' => 'https://www.example.com',
        'excluded_directories' => [
            'logs',
        ],
    ],
],
```

If you would like to use your own Account model, just make sure that it implements `JoshWhatK\SuperScan\Contracts\AccountInterface`.
