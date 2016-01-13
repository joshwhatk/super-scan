<?php

namespace Joshwhatk\SuperScan\Support;

use Carbon\Carbon;
use Joshwhatk\SuperScan\Database\BaselineFile;

class File
{
    public $path;
    public $hash;
    public $last_modified;

    public function __construct($file)
    {
        if(is_a($file, BaselineFile::class))
        {
            $this->path = $file->path;
            $this->hash = $file->hash;
            $this->last_modified = $file->last_modified;

            return $this;
        }

        $this->path = $file;
        $this->hash = hash_file("sha1", $file);
        $this->last_modified = new Carbon(filemtime($file));

        return $this;
    }

    public function toArray($account = null)
    {
        $array = [
            'path' => $this->path,
            'hash' => $this->hash,
            'last_modified' => $this->last_modified,
        ];

        if(! is_null($account))
        {
            $array['account_id'] = $account->id;
        }

        return $array;
    }
}
