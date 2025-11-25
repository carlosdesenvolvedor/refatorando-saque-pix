<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use App\Model\Withdrawal; // <-- ADICIONE ESTA LINHA

/**
 * @property int $id 
 * @property string $name 
 * @property string $cpf 
 * @property string $balance 
 * @property string|null $email 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Account extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'name',
        'cpf',
        'balance',
        'email',
    ];

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'account_id');
    }
}