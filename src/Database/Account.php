<?php

namespace JoshWhatK\SuperScan\Database;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.4
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Illuminate\Database\Eloquent\Model;
use JoshWhatK\SuperScan\Contracts\AccountInterface;

class Account extends Model implements AccountInterface
{
    protected $fillable = ['name', 'server_name', 'ip_address', 'scan_directory', 'public_url', 'excluded_directories',];

    protected $casts = [
        'excluded_directories' => 'array',
    ];

    /**
     * Get the name of the Account.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the name of the Server for the Account.
     * @return string
     */
    public function getServerName()
    {
        return $this->server_name;
    }

    /**
     * Get the IP Address of the Server for the Account.
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Get the Webroot of the Website for the Account.
     * No need for a trailing slash.
     * @return string
     */
    public function getScanDirectory()
    {
        return $this->scan_directory;
    }

    /**
     * Get the URL of the Website for the Account.
     * @return string
     */
    public function getUrl()
    {
        return $this->public_url;
    }

    /**
     * Get a Collection of excluded file paths.
     * @return \Illuminate\Support\Collection
     */
    public function getExcludedDirectories()
    {
        return $this->excluded_directories;
    }

    /**
     * Helper function for adding excluded directories
     * @param string $directory_name
     *
     * @return JoshWhatK\SuperScan\Database\Account
     */
    public function addExcludedDirectory($directory_name)
    {
        $this->excluded_directories = collect($this->excluded_directories)
            ->push($directory_name)
            ->toArray();
        $this->save();

        return $this;
    }
}
