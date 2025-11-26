<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\BelongsTo;

/**
 * @property string $account_withdraw_id
 * @property string $type
 * @property string $key
 */
class AccountWithdrawPix extends Model
{
    protected ?string $table = 'account_withdraw_pix';

    public bool $incrementing = false;

    protected string $primaryKey = 'account_withdraw_id';

    protected string $keyType = 'string';

    public bool $timestamps = false;

    protected array $fillable = [
        'account_withdraw_id',
        'type',
        'key',
    ];

    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(AccountWithdraw::class, 'account_withdraw_id', 'id');
    }
}