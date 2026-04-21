<?php
//src/Database/Models/Question.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Question
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public string $text = '',
        public string $type = 'radio',
        public int $orderIndex = 0,
        public bool $isRequired = true,
        public bool $isActive = true,
        public array $options = [],
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            publicId: $data['public_id'] ?? '',
            text: $data['text'] ?? '',
            type: $data['type'] ?? 'radio',
            orderIndex: isset($data['order_index']) ? (int)$data['order_index'] : 0,
            isRequired: (bool)($data['is_required'] ?? true),
            isActive: (bool)($data['is_active'] ?? true),
            options: isset($data['options']) ? json_decode($data['options'], true) : [],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}