<?php
//src/Database/Models/Option.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Option
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public int $questionId = 0,
        public string $text = '',
        public float $score = 0.0,
        public int $orderIndex = 0,
        public ?string $recommendationText = null,
        public ?string $recommendationLevel = null,
        public bool $isActive = true,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            publicId: $data['public_id'] ?? '',
            questionId: isset($data['question_id']) ? (int) $data['question_id'] : 0,
            text: $data['text'] ?? '',
            score: isset($data['score']) ? (float) $data['score'] : 0.0,
            orderIndex: isset($data['order_index']) ? (int) $data['order_index'] : 0,
            recommendationText: $data['recommendation_text'] ?? null,
            recommendationLevel: $data['recommendation_level'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}