<?php
//src/Database/Models/Respondent.php

declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Respondent
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public string $name = '',
        public string $email = '',
        public ?string $phone = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            publicId: $data['public_id'] ?? '',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            phone: $data['phone'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}