<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.2
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

namespace Joshwhatk\SuperScan\Database;

use Illuminate\Database\Eloquent\Model;
use Joshwhatk\SuperScan\Database\Account;

class ScannedFile extends Model
{
    public function scopeAccount($query, Account $account)
    {
        return $query->where('account_id', $account->id);
    }
}