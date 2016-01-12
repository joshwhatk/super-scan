<?php

namespace Joshwhatk\SuperScan;

class Report
{
    /**
     * The Scan on which to run the Report
     * @var \Joshwhatk\SuperScan\Support\Scan
     */
    protected $scan;

    public function __construct()
    {
        //
    }

    public function addScan(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function report()
    {
        $baseline_count = $this->scan->baseline->count();
        $this->add($this->scan->baseline->count()." baseline files extracted from database.  ");
    }
}
