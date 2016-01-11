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

use Joshwhatk\SuperScan\Database\Account;
use Joshwhatk\SuperScan\Database\ScannedFile;
use \Log;
use \Carbon\Carbon;

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

    public function __construct(Account $account)
    {
        $this->account = $account;

        //-- Initialize Arrays
        $this->baseline = collect([]);
        $this->current = collect([]);
        $this->added = collect([]);
        $this->altered = collect([]);
        $this->deleted = collect([]);

        $this->start = new Carbon;
    }

    public static function run(Account $account)
    {
        $scan = new static($account);

        $scan->initialize();
        $scan->determineBaseline();
    }

    protected function getLastScanTime()
    {
        $this->last_scanned_record = ScannedFile::account($account)
            ->orderBy('created_at', 'desc')->limit(1);
    }

    protected function initialize()
    {
        $this->getLastScanTime();

        if(is_null($this->last_scanned_record))
        {
            $this->first_scan = true;
        }
    }

    protected function determineBaseline()
    {

    }

    protected function log($message)
    {
        if($this->environment === 'local')
        {
            Log::info($message);
        }
    }
}
