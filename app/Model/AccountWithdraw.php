<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasOne;

/**
 * @property string $id
 * @property string $account_id
 * @property string $method
 * @property float $amount
 * @property bool $scheduled
 * @property Carbon|null $scheduled_for
 * @property bool $done
 * @property bool $error
 * @property string|null $error_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Account $account
 * @property-read AccountWithdrawPix $pix
 */
class AccountWithdraw extends Model
{
    use HasUuids;

    protected ?string $table = 'account_withdraws';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'account_id',
        'method',
        'amount',
        'scheduled',
        'scheduled_for',
        'done',
        'error',
        'error_reason',
    ];

    protected array $casts = ['amount' => 'float', 'scheduled' => 'boolean', 'done' => 'boolean', 'error' => 'boolean', 'scheduled_for' => 'datetime'];

    public function save(array $options = []): bool
    {
        if (empty($this->{$this->getKeyName()})) {
            $this->{$this->getKeyName()} = (string) \Hyperf\Stringable\Str::uuid();
        }
        return parent::save($options);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function pix(): HasOne
    {
        return $this->hasOne(AccountWithdrawPix::class, 'account_withdraw_id', 'id');
    }
}