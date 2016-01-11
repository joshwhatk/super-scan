<?php

namespace Joshwhatk\SuperScan;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.1
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use \Log;
use \Carbon\Carbon;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Joshwhatk\SuperScan\Database\Account;
use Joshwhatk\SuperScan\Database\ScannedFile;

class Scan
{
    /**
     * The Account for which the SuperScan is being run.
     *
     * @var \Joshwhatk\Database\Account
     */
    protected $account;

    /**
     * Initialize the array for the `baseline` table.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $baseline;

    /**
     * Initialize the array for the current file scan.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $current;

    /**
     * Intitialize the differences arrays.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $added;
    protected $altered;
    protected $deleted;

    /**
     * The Application's environment
     * @var string
     */
    protected $environment;

    /**
     * Whether or not this is the first time for this account.
     *
     * @var boolean
     */
    protected $first_scan = false;

    /**
     * The time that the scan was started.
     *
     * @var \Carbon\Carbon
     */
    protected $start;

    /**
     * The last record from the previous scan.
     *
     * @var \Joshwhatk\Database\ScannedFile
     */
    protected $last_scanned_record;

    /**
     * The Collection which will hold all triggered reports.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $reports;

    /**
     * The directory to be scanned.
     * @var [type]
     */
    protected $directory;

    /**
     * A Collection of file paths and extensions to be excluded.
     * @var \Illuminate\Support\Collection
     */
    protected $exclusions = [
        'directories' => [],
        'extensions' => [],
    ];

    /**
     * A list of whitelisted extensions if the extensions are whitelisted.
     * @var null or \Illuminate\Support\Collection
     */
    protected $only_extensions = null;

    /**
     * The file iterator.
     * @var \RecursiveIteratorIterator
     */
    protected $iterator;

    /**
     * The default configuration for this package.
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    public function __construct(Account $account)
    {
        $this->environment = config('app.env');
        $this->createConfig(config('joshwhatk.super_scan'));

        $this->account = $account;
        $this->directory = $account->getWebroot();
        $this->getExcludedExtensions();
        $this->getExcludedDirectories();

        //-- Initialize Arrays
        $this->baseline = collect([]);
        $this->current = collect([]);
        $this->added = collect([]);
        $this->altered = collect([]);
        $this->deleted = collect([]);
        $this->reports = collect([
            'alerts' => collect([]),
            'messages' => collect([]),
        ]);

    }

    protected function createConfig($config)
    {
        $this->config = collect([
            'scan_extensionless' => $config['defaults.extensions.scan_extensionless'],
            'extensions' => $config['defaults.extensions'],
            'directories' => $config['defaults.directories.blacklist'],
        ]);
    }

    public static function run(Account $account)
    {
        $scan = new static($account);

        $scan->initialize();
        $scan->determineBaseline();
        $scan->scanDirectory();
    }

    private function initialize()
    {
        $this->getLastScanTime();

        if (is_null($this->last_scanned_record)) {
            $this->first_scan = true;
        }

        //-- Set the start after the first database query has returned
        $this->start = new Carbon;
    }

    private function determineBaseline()
    {
        $this->baseline = BaselineFile::account($this->account)
            ->orderBy('file_path', 'asc')->get();

        if ($this->baseline->isEmpty() && !$this->first_scan) {
            $this->alert(
                "**Probable hack**  Empty baseline table!  (ALL baseline files are missing or deleted)!"
            );
        }
        $baseline_count = $this->baseline->count();
        $this->report($this->baseline->count()." baseline files extracted from database.  ");
    }

    private function scanDirectory()
    {
        $recursive_directory_iterator = new RecursiveDirectoryIterator($this->directory);
        $this->iterator = new RecursiveIteratorIterator($recursive_directory_iterator);

        $this->checkDirectoriesAndFiles();
    }

    protected function checkDirectoriesAndFiles()
    {
        while($this->iterator->valid())
        {
            //  Not in Dot AND not in $skip (prohibited) directories
            if(! $this->directoryIsSkippable())
            {
                //  Get or set file extension ('' vs null)
                $extension = $this->setFileExtension();

                if($this->extensionIsAllowed($extension))
                {
                    $file_path = $this->cleanPath($iterator->key());

                    //
                }
            }
        }
    }

    protected function cleanPath($path)
    {
        return str_replace(chr(92),chr(47),$path);
    }

    protected function extensionIsAllowed($extension)
    {
        // extension is empty and extensionless are not scanned
        if($extension === '' && !$this->config['scan_extensionless'])
        {
            return false;
        }

        //-- extensions is not whitelisted and the extension is in that array
        if($this->extensionIsBlacklisted($extension))
        {
            return false;
        }

        if(! $this->extensionIsWhitelisted())
        {
            return false;
        }

        return true;
    }

    protected function extensionIsBlacklisted($extension)
    {
        //-- the extensions is not whitelisted and the blacklist contains it
        if(!$this->whitelistIsSet()
           &&
           $this->exclusions['extensions']->contains($extension))
        {
            return true;
        }

        //-- otherwise it isn't blacklisted
        return false;
    }

    protected function extensionIsWhitelisted($extension)
    {
        //-- if whitelist is set and it is in only extensions
        if($this->whitelistIsSet() && $this->only_extensions->contains($extension))
        {
            return true;
        }

        //-- otherwise, it is not whitelisted
        return false;
    }

    protected function getExcludedExtensions()
    {
        if(! $this->whitelistIsSet())
        {
            $this->exclusions['extensions'] = collect($this->config['extensions']['blacklist']);
        }
    }

    /**
     * Returns true if the extensions whitelist contains extensions
     * @return boolean
     */
    protected function whitelistIsSet()
    {
        $whitelist = $this->config['extensions']['whitelist'];

        //-- return false if the whitelist is empty
        if($whitelist === [])
        {
            return false;
        }

        //-- set up the only_extensions property
        if(is_null($this->only_extensions))
        {
            $this->only_extensions = collect($whitelist);
        }

        return true;
    }

    protected function getExcludedDirectories()
    {
        $this->exclusions['directories'] = collect($this->config['directories']);

        //-- add any excluded directories specific to this account
        $account_exclusions = collect($account->getExcludedDirectories());
        if(! $account_exclusions->isEmpty())
        {
            $this->exclusions['directories']->merge($account_exclusions->all());
        }
    }

    protected function getLastScanTime()
    {
        $this->last_scanned_record = ScannedFile::account($this->account)
            ->orderBy('created_at', 'desc')->limit(1)->get();
    }

    protected function directoryIsSkippable()
    {
        return $this->iterator->isDot() || $this->exclusions['directories']->contains($this->iterator->getSubPath());
    }

    protected function setFileExtension()
    {
        if (is_null(pathinfo($this->iterator->key(), PATHINFO_EXTENSION)))
        {
            return '';
        }

        return strtolower(pathinfo($this->iterator->key(), PATHINFO_EXTENSION));
    }

    protected function log($message)
    {
        if ($this->environment === 'local') {
            Log::info($message);
        }
    }

    protected function alert($message)
    {
        $this->reports['alerts']->push($message);
    }

    protected function report($message)
    {
        $this->reports['messages']->push($message);
        $this->log($message);
    }
}
