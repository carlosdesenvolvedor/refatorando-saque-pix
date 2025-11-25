<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $cpf
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Model
{
    protected ?string $table = 'users';
    protected array $fillable = ['name', 'email', 'cpf', 'password'];
    protected array $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}