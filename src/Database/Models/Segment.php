<?php
//src/Database/Models/Segment.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Segment
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public int $surveyId = 0,
        public string $name = '',
        public ?string $description = null,
        public ?string $icon = null,
        public string $color = '#36A2EB',
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
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            color: $data['color'] ?? '#36A2EB',
            orderIndex: isset($data['order_index']) ? (int)$data['order_index'] : 0,
            isActive: (bool)($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'survey_id' => $this->surveyId,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'order_index' => $this->orderIndex,
            'is_active' => $this->isActive,
        ];
    }
}