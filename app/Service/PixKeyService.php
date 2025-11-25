<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\PixKeyServiceInterface;
use App\Exception\BusinessException;
use App\Model\PixKey;

class PixKeyService implements PixKeyServiceInterface
{
    public function create(array $data): PixKey
    {
        // 1. Verifica se a chave já existe no banco
        $existingKey = PixKey::where('key', $data['key'])->first();

        if ($existingKey) {
            // Lança uma exceção de negócio se a chave for duplicada
            throw new BusinessException('Esta chave Pix já está cadastrada.', 422);
        }

        // 2. Cria a nova chave Pix
        $pixKey = PixKey::create($data);

        return $pixKey;
    }
}