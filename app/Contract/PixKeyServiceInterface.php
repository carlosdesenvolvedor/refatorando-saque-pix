<?php

declare(strict_types=1);

namespace App\Contract;

use App\Model\PixKey;

interface PixKeyServiceInterface
{
    public function create(array $data): PixKey;
}