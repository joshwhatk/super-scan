<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.4
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

namespace JoshWhatK\SuperScan;

use JoshWhatK\SuperScan\Contracts\ReportingInterface;

class Report implements ReportingInterface
{
    /**
     * The Scan on which to run the Report
     * @var \JoshWhatK\SuperScan\Support\Scan
     */
    protected $scan;

    public function addScan(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function report()
    {
        $baseline_count = $this->scan->baseline->count();
        $this->add($this->scan->baseline->count()." baseline files extracted from database.  ");
    }

    public function add($message, $type = 'info')
    {
        //
    }

    public function alert($message)
    {
        $this->add($message, 'alert');
    }
}
