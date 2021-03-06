<?php

namespace JoshWhatK\SuperScan\Database;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.2
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use JoshWhatK\SuperScan\Support\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use JoshWhatK\SuperScan\Contracts\AccountInterface;

class BaselineFile extends Model
{
    protected $fillable = ['path', 'hash', 'last_modified', 'account_id'];

    public function scopeAccount($query, AccountInterface $account)
    {
        return $query->where('account_id', $account->id);
    }

    public static function toFiles(Collection $baseline_files)
    {
        $files_collection = collect([]);

        foreach ($baseline_files as $baseline_file) {
            $files_collection->put($baseline_file->path, new File($baseline_file));
        }

        return $files_collection;
    }

    public static function createFromFile(File $file, AccountInterface $account)
    {
        $baseline = new static;
        $baseline->fill($file->toArray());
        $baseline->account_id = $account->id;
        $baseline->save();

        return $baseline;
    }

    public static function updateFromFile(File $file, AccountInterface $account)
    {
        $baseline = static::where('path', $file->path)->account($account)->first();
        $baseline->fill($file->toArray());
        $baseline->save();

        return $baseline;
    }
}
