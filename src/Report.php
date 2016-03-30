<?php

namespace JoshWhatK\SuperScan;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.0
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Illuminate\Support\Facades\Mail;
use JoshWhatK\SuperScan\Contracts\ReportingInterface;

class Report implements ReportingInterface
{
    /**
     * The Scan on which to run the Report
     * @var \JoshWhatK\SuperScan\Support\Scan
     */
    protected $scan;

    protected $messages;

    public function __construct()
    {
        $this->messages = collect([]);
    }

    public function addScan(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function report()
    {
        $baseline_count = $this->scan->baseline->count();
        $this->scan->log($baseline_count." baseline files extracted from database.");

        $account = $this->scan->account;
        $scan = $this->scan;
        $messages = $this->messages;
        $added = $this->scan->added;
        $altered = $this->scan->altered;
        $deleted = $this->scan->deleted;

        $altered_files_text = $this->getFilesText($altered);
        $added_files_text = $this->getFilesText($added);
        $deleted_files_text = $this->getFilesText($deleted);
        $timezone = config('app.timezone');

        if($added->isEmpty() && $altered->isEmpty() && $deleted->isEmpty())
        {
            $this->scan->log('No file changes detected.');
            return;
        }

        Mail::send('super-scan::emails.report',
        [
            'account' => $account,
            'scan' => $scan,
            'messages' => $messages,
            'added' => $added,
            'altered' => $altered,
            'deleted' => $deleted,
            'altered_files_text' => $altered_files_text,
            'added_files_text' => $added_files_text,
            'deleted_files_text' => $deleted_files_text,
            'timezone' => $timezone,
        ], function ($m) {
            $config = config('joshwhatk.super_scan.reporting');

            $m->from($config['from']['email'], $config['from']['name']);

            $m->to($config['recipients'])->subject('SuperScan Report');
        });
    }

    public function alert($message, $type = null)
    {
        if(is_null($type))
        {
            $type = 'alert';
        }

        $this->messages = $this->messages->push([
            'content' => $message,
            'type' => $type,
        ]);
    }

    protected function getFilesText($files)
    {
        if($files->isEmpty())
        {
            return 'No Files';
        }

        if($files->count() == 1)
        {
            return '1 File';
        }

        return $files->count().' Files';
    }
}
