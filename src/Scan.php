<?php

namespace JoshWhatK\SuperScan;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.4
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use \Log;
use \Carbon\Carbon;
use RecursiveIteratorIterator;
use JoshWhatK\SuperScan\Report;
use RecursiveDirectoryIterator;
use JoshWhatK\SuperScan\Support\File;
use JoshWhatK\SuperScan\Support\FileHelper;
use JoshWhatK\SuperScan\Database\BaselineFile;
use JoshWhatK\SuperScan\Database\HistoryRecord;
use JoshWhatK\SuperScan\Contracts\AccountInterface;
use JoshWhatK\SuperScan\Database\Scan as FileScan;
use JoshWhatK\SuperScan\Contracts\ReportingInterface;

class Scan
{
    /**
     * The Account for which the SuperScan is being run.
     *
     * @var \JoshWhatK\Database\Account
     */
    public $account;

    /**
     * Initialize the array for the `baseline` table.
     *
     * @var \Illuminate\Support\Collection
     */
    public $baseline;

    /**
     * Initialize the array for the current file scan.
     *
     * @var \Illuminate\Support\Collection
     */
    public $current;

    /**
     * Intitialize the differences arrays.
     *
     * @var \Illuminate\Support\Collection
     */
    public $added;
    public $altered;
    public $deleted;

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
    public $first_scan = false;

    /**
     * The time that the scan was started.
     *
     * @var array
     */
    protected $timestamps = [];

    /**
     * The Report to run for the current Scan
     *
     * @var \JoshWhatK\SuperScan\Report
     */
    protected $report;

    /**
     * A Collection of file paths and extensions to be excluded.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $exclusions = [
        'directories' => [],
        'extensions' => [],
    ];

    /**
     * A list of whitelisted extensions if the extensions are whitelisted.
     *
     * @var null or \Illuminate\Support\Collection
     */
    protected $only_extensions = null;

    /**
     * The current file.
     *
     * @var \SplFileInfo
     */
    protected $current_file;

    /**
     * The default configuration for this package.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    public function __construct(AccountInterface $account, Report $report)
    {
        $this->report = $report;
        $this->environment = config('app.env');
        $this->createConfig(config('joshwhatk.super_scan'));

        $this->account = $account;
        $this->getExcludedExtensions();
        $this->getExcludedDirectories();

        //-- Initialize Arrays
        $this->baseline = collect([]);
        $this->current = collect([]);

        $this->added = collect([]);
        $this->altered = collect([]);
        $this->deleted = collect([]);
    }

    protected function createConfig($config)
    {
        $this->config = collect([
            'scan_extensionless' => $config['defaults']['extensions']['scan_extensionless'],
            'extensions' => $config['defaults']['extensions'],
            'directories' => $config['defaults']['directories']['blacklist'],
        ]);
    }

    public static function run(AccountInterface $account, $report = null)
    {
        //-- use a ReportingInterface
        if (! is_a($report, ReportingInterface)) {
            $report = new Report;
        }

        $scan = new static($account, $report);

        $scan->initialize();
        $scan->determineBaseline();
        $scan->scanDirectory();
        $scan->handleDeletedFiles();
        $scan->complete();

        $scan->report->addScan($scan);
        $scan->report->report();

        return $scan;
    }

    private function initialize()
    {
        $last_scanned_record = $this->getLastScanTime();

        if (is_null($last_scanned_record)) {
            $this->first_scan = true;
        }

        //-- Set the start after the first database query has returned
        $this->timestamps['started'] = new Carbon;
    }

    private function determineBaseline()
    {
        $baselines = BaselineFile::account($this->account)
            ->orderBy('path', 'asc')->get();

        //-- convert to File::class
        $this->baseline = BaselineFile::toFiles($baselines);

        if ($this->baseline->isEmpty() && !$this->first_scan) {
            $this->alert(
                "**Probable hack**  Empty baseline table!  (ALL baseline files are missing or deleted)!"
            );
        }
    }

    private function scanDirectory()
    {
        //-- directory, excluded directories
        $allFiles = FileHelper::make()
            ->allFiles($this->account->getScanDirectory(), $this->exclusions['directories']->toArray(), true);

        foreach ($allFiles as $file) {
            $this->current_file = $file;
            $this->checkDirectoriesAndFiles();
        }
    }

    private function handleDeletedFiles()
    {
        $this->deleted = $this->getDeletedFiles();

        foreach ($this->deleted as $file_path => $file) {
            //-- delete file from baseline table
            $baseline = BaselineFile::where('path', $file_path)->account($this->account)->first();
            $baseline->delete();

            $this->saveDeletedFileToHistory($file_path);
        }
    }

    private function complete()
    {
        $this->timestamps['completed'] = new Carbon;
        $this->save();
        $this->dump();
    }

    protected function save()
    {
        $count_of_changes = $this->added->count() + $this->altered->count() + $this->deleted->count();
        $scan = new FileScan;
        $scan->changes = $count_of_changes;
        $scan->account_id = $this->account->id;
        $scan->save();
    }

    protected function saveDeletedFileToHistory($file_path)
    {
        $historyRecord = new HistoryRecord;

        $historyRecord->fill([
            'status' => 'Deleted',
            'path' => $file_path,
            'baseline_hash' => $this->deleted[$file_path]['hash'],
            'last_modified' => $this->deleted[$file_path]['last_modified'],
            'account_id' => $this->account->id,
        ]);

        $historyRecord->save();
    }

    protected function getDeletedFiles()
    {
        return $this->baseline->diff($this->current);
    }

    protected function checkDirectoriesAndFiles()
    {
        //  Not in Dot AND not in $skip (prohibited) directories
        // if(! $this->directoryIsSkippable())
        // {
            //  Get or set file extension ('' vs null)
            $extension = $this->setFileExtension();

        if ($this->extensionIsAllowed($extension)) {
            $file_path = $this->cleanPath($this->current_file->getRealPath());
            $this->log(['$this->current_file->getRealPath()' => $this->current_file->getRealPath()]);
            $this->log(['$file_path' => $file_path]);

                //-- add current file
                $this->current->put($file_path, new File($file_path));

                //-- if the file was added
                $this->handleNewFile($file_path);

                //-- if the file was altered
                $this->handleAlteredFile($file_path);
        }
        // }
    }

    protected function handleNewFile($file_path)
    {
        //-- it is added if baseline doesn't contain the $file_path
        if (! $this->baseline->contains($file_path)) {
            $this->added->put($file_path, $this->current[$file_path]);

            //-- insert added file into baseline table
            BaselineFile::createFromFile($this->current[$file_path], $this->account);

            if (! $this->first_scan) {
                return $this->saveAddedFileToHistory($file_path);
            }
        }
    }

    protected function handleAlteredFile($file_path)
    {
        if ($this->baseline->contains($file_path)
           &&
           ($this->baseline[$file_path]['hash'] != $this->current[$file_path]['hash']
            ||
            $this->baseline[$file_path]['last_modified'] != $this->current[$file_path]['last_modified'])
        ) {
            $this->altered->put($file_path, $this->current[$file_path]);

            //-- add the baseline_hash
            $this->altered[$file_path]['baseline_hash'] = $this->baseline[$file_path]['hash'];

            //-- update altered file in baseline table
            BaselineFile::updateFromFile($this->current[$file_path], $this->account);

            $this->saveAlteredFileToHistory($file_path);
        }
    }

    protected function saveAlteredFileToHistory($file_path)
    {
        $historyRecord = new HistoryRecord;

        $historyRecord->fill([
            'status' => 'Altered',
            'path' => $file_path,
            'baseline_hash' => $this->altered[$file_path]['baseline_hash'],
            'latest_hash' => $this->altered[$file_path]['hash'],
            'last_modified' => $this->altered[$file_path]['last_modified'],
            'account_id' => $this->account->id,
        ]);

        $historyRecord->save();
    }

    protected function saveAddedFileToHistory($file_path)
    {
        $historyRecord = new HistoryRecord;

        $historyRecord->fill([
            'status' => 'Added',
            'path' => $file_path,
            'latest_hash' => $this->added[$file_path]['hash'],
            'last_modified' => $this->added[$file_path]['last_modified'],
            'account_id' => $this->account->id,
        ]);

        $historyRecord->save();
    }

    protected function cleanPath($path)
    {
        return str_replace(chr(92), chr(47), $path);
    }

    protected function extensionIsAllowed($extension)
    {
        // extension is empty and extensionless are not scanned
        if ($extension === '' && !$this->config['scan_extensionless']) {
            return false;
        }

        //-- whitelist is not set and the extension is in that array
        if ($this->extensionIsBlacklisted($extension)) {
            return false;
        }

        if ($this->whitelistIsSet() && !$this->extensionIsWhitelisted($extension)) {
            return false;
        }

        return true;
    }

    protected function extensionIsBlacklisted($extension)
    {
        //-- the extensions is not whitelisted and the blacklist contains it
        if (!$this->whitelistIsSet()
           &&
           $this->exclusions['extensions']->contains($extension)) {
            return true;
        }

        //-- otherwise it isn't blacklisted
        return false;
    }

    protected function extensionIsWhitelisted($extension)
    {
        //-- if whitelist is set and it is in only extensions
        if ($this->only_extensions->contains($extension)) {
            return true;
        }

        //-- otherwise, it is not whitelisted
        return false;
    }

    protected function getExcludedExtensions()
    {
        if (! $this->whitelistIsSet()) {
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
        if ($whitelist === []) {
            return false;
        }

        //-- set up the only_extensions property
        if (is_null($this->only_extensions)) {
            $this->only_extensions = collect($whitelist);
        }

        return true;
    }

    protected function getExcludedDirectories()
    {
        $this->exclusions['directories'] = collect($this->config['directories']);

        //-- add any excluded directories specific to this account
        $account_exclusions = collect($this->account->getExcludedDirectories());
        if (! $account_exclusions->isEmpty()) {
            $this->exclusions['directories']->merge($account_exclusions->all());
        }
    }

    protected function getLastScanTime()
    {
        return FileScan::account($this->account)
            ->orderBy('created_at', 'desc')->limit(1)->get();
    }

    protected function directoryIsSkippable()
    {
        // $this->log($this->exclusions['directories']);
        // $this->log($this->iterator->getPath());
        // $this->log($this->exclusions['directories']->contains($this->iterator->getPath()));

        // $path = collect([$this->iterator->getPath() => true]);
        // $this->log($path->contains($this->exclusions['directories']));

        // return $this->iterator->isDot() || $path->contains($this->exclusions['directories']);
    }

    protected function setFileExtension()
    {
        if (is_null(pathinfo($this->current_file->getRealPath(), PATHINFO_EXTENSION))) {
            return '';
        }

        return strtolower(pathinfo($this->current_file->getRealPath(), PATHINFO_EXTENSION));
    }

    protected function log($message)
    {
        if ($this->environment === 'local') {
            Log::info($message);
            debug($message);
        }
    }

    protected function dump()
    {
        if ($this->environment === 'local') {
            $this->log(['current_files' => $this->current]);
            $this->log(['baseline_files' => $this->baseline]);
            $this->log(['added_files' => $this->added]);
            $this->log(['altered_files' => $this->altered]);
            $this->log(['deleted_files' => $this->deleted]);
        }
    }

    protected function alert($message)
    {
        $this->report->alert($message);
    }
}
