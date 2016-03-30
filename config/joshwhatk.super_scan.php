<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.0
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

return [

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

        'defaults' => [
            [
                'name' => 'Base Account',
                'server_name' => 'hostname',
                'ip_address' => '127.0.0.1',
                'scan_directory' => '/var/www/html',
                'public_url' => 'https://www.example.com',
                'excluded_directories' => [
                    'logs', 'cache'
                ],
            ],
        ],
    ],

    'reporting' => [
        'recipients' => [
            'email@example.com',
            'email@example.com',
        ],
        'from' => [
            'name' => 'SuperScan',
            'email' => 'report@example.com',
        ],
    ],

    'defaults' => [

        'extensions' => [

            /*
            |--------------------------------------------------------------------------
            | Extensions to Check
            |--------------------------------------------------------------------------
            |
            | Examples: ['php', 'html', 'htm', 'js']
            |
            | Recommended: An empty array will return ALL extensions which is best
            | for real security.
            |
            */

            'whitelist' => [],

            /*
            |--------------------------------------------------------------------------
            | Extensions to Exclude from the Check
            |--------------------------------------------------------------------------
            |
            | Examples: ['ftpquota', 'txt', 'swf', 'fla']
            |
            | This will only be used if the 'whitelist' config is empty. If both are
            | empty arrays all file extensions will be checked.
            |
            */

            'blacklist' => ['ftpquota', 'txt', 'swf', 'fla', 'log', 'lock'],

            /*
            |--------------------------------------------------------------------------
            | Scan Extension-less files?
            |--------------------------------------------------------------------------
            */

            'scan_extensionless' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Directories to ignore
        |--------------------------------------------------------------------------
        */

        'directories' => [

            'blacklist' => ['protected', 'private', '.git', 'vendor', 'framework'],
        ],
    ],

];
