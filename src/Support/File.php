<?php

namespace Joshwhatk\SuperScan\Support;

use Carbon\Carbon;
use Joshwhatk\SuperScan\Database\BaselineFile;
use ArrayAccess;

class File implements ArrayAccess
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
        $this->last_modified = Carbon::createFromFormat('U', filemtime($file));

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

    public function __toString()
    {
        return $this->path;
    }

    /*
    |--------------------------------------------------------------------------
    | ArrayAccess
    |--------------------------------------------------------------------------
    |
    | Pulled this from Illuminate\Database\Eloquent\Model::class
    |
    */

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}
