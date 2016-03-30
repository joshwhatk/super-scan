<?php

namespace JoshWhatK\SuperScan\Support;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.0
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Symfony\Component\Finder\Finder;

class FileHelper
{
    private $directory;

    public function __construct()
    {
        return $this;
    }

    public static function make()
    {
        return new static;
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string  $directory
     * @return array
     */
    public function allFiles($directory, $exclude = null, $toArray = false)
    {
        $exclude = (array) $exclude;

        $finder = Finder::create()->files()->in($directory);

        if(!is_null($exclude))
        {
            $finder->exclude($exclude);
        }

        if(!$toArray)
        {
            return $finder;
        }

        return iterator_to_array($finder, false);
    }

}
