<?php
//src/Database/Models/Survey.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Survey
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public string $name = '',
        public ?string $description = null,
        public string $chartType = 'polar',
        public bool $isActive = true,
        public ?array $settings = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            publicId: $data['public_id'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            chartType: $data['chart_type'] ?? 'polar',
            isActive: (bool)($data['is_active'] ?? true),
            settings: isset($data['settings']) ? json_decode($data['settings'], true) : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'name' => $this->name,
            'description' => $this->description,
            'chart_type' => $this->chartType,
            'is_active' => $this->isActive,
            'settings' => $this->settings ? json_encode($this->settings) : null,
        ];
    }
}