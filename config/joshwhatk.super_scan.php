<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.2
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

return [

    'account_information' => [
        'relations' => [
            'servers',
            'websites',
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

            'blacklist' => ['ftpquota', 'txt', 'swf', 'fla'],

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

            'blacklist' => ['protected', 'private', '.git'],
        ],
    ],

];
