<?php

namespace JoshWhatK\SuperScan\Contracts;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.2
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use JoshWhatK\SuperScan\Scan;

interface ReportingInterface
{
    /**
     * The Scan is added to the report after
     * it is completed, this is the method
     * to accomplish that.
     *
     * @param Scan $scan
     * @return void
     */
    public function addScan(Scan $scan);

    /**
     * The main reporting method.
     * This should build up the report
     * and send out any emails.
     *
     * @return void
     */
    public function report();

    /**
     * Add an extra message to the report.
     * This is mostly used if the report does
     * not run as expected.
     *
     * @param  string $message
     * @return void
     */
    public function alert($message);
}
