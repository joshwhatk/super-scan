<?php

/**
 * Part of the SuperScan package.
 *
 * @package    SuperScan
 * @version    0.0.3
 * @author     joshwhatk
 * @license    MIT
 * @link       http://jwk.me
 */

namespace Joshwhatk\SuperScan\Database;

use Illuminate\Database\Eloquent\Model;
use Joshwhatk\SuperScan\Contracts\AccountInterface;

class HistoryRecord extends Model
{
    protected $fillable = ['status', 'path', 'baseline_hash', 'latest_hash', 'last_modified', 'account_id'];

    public function scopeAccount($query, AccountInterface $account)
    {
        return $query->where('account_id', $account->id);
    }
}
