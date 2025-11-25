<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property string $kind
 * @property string $key
 * @property int $account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PixKey extends Model
{
    /**
     * The table associated with the model.
     */
  
    protected ?string $table = 'pix_keys';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['kind', 'key', 'account_id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
