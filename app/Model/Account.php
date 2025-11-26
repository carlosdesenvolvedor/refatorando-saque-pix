<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Stringable\Str;

/**
 * @property string $id 
 * @property string $name 
 * @property string $balance 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Account extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'accounts';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['name', 'balance'];

    public function save(array $options = []): bool
    {
        if (empty($this->{$this->getKeyName()})) {
            $this->{$this->getKeyName()} = (string) Str::uuid();
        }
        return parent::save($options);
    }

    public function withdraws(): HasMany
    {
        return $this->hasMany(AccountWithdraw::class, 'account_id', 'id');
    }
}