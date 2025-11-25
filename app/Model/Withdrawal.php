<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $account_id
 * @property int $pix_key_id
 * @property float $amount
 * @property string $status
 * @property Carbon|null $scheduled_for
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Account $account
 */
class Withdrawal extends Model
{
    protected ?string $table = 'withdrawals';

    protected array $fillable = [
        'account_id',
        'pix_key_id',
        'amount',
        'status',
        'scheduled_for',
    ];

    protected array $casts = ['id' => 'integer', 'account_id' => 'integer', 'pix_key_id' => 'integer', 'amount' => 'float', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'scheduled_for' => 'datetime'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}