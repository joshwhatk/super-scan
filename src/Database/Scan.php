<?php

namespace JoshWhatK\SuperScan\Database;

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    1.0.0
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

use Illuminate\Database\Eloquent\Model;
use JoshWhatK\SuperScan\Contracts\AccountInterface;

class Scan extends Model
{
    protected $fillable = ['changes', 'account_id'];

    public function scopeAccount($query, AccountInterface $account)
    {
        return $query->where('account_id', $account->id);
    }
}
