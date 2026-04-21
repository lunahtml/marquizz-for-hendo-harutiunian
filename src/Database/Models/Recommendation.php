<?php
//src/Database/Models/Recommendation.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Recommendation
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public int $surveyId = 0,
        public ?int $segmentId = null,
        public int $minScore = 0,
        public int $maxScore = 100,
        public string $title = '',
        public ?string $description = null,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public int $orderIndex = 0,
        public bool $isActive = true,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            publicId: $data['public_id'] ?? '',
            surveyId: isset($data['survey_id']) ? (int)$data['survey_id'] : 0,
            segmentId: isset($data['segment_id']) ? (int)$data['segment_id'] : null,
            minScore: isset($data['min_score']) ? (int)$data['min_score'] : 0,
            maxScore: isset($data['max_score']) ? (int)$data['max_score'] : 100,
            title: $data['title'] ?? '',
            description: $data['description'] ?? null,
            actionText: $data['action_text'] ?? null,
            actionUrl: $data['action_url'] ?? null,
            orderIndex: isset($data['order_index']) ? (int)$data['order_index'] : 0,
            isActive: (bool)($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}