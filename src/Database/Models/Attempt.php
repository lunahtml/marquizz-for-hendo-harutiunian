<?php
//src/Database/Models/Attempt.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Attempt
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $publicId = '',
        public int $surveyId = 0,
        public ?int $respondentId = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            publicId: $data['public_id'] ?? '',
            surveyId: isset($data['survey_id']) ? (int)$data['survey_id'] : 0,
            respondentId: isset($data['respondent_id']) ? (int)$data['respondent_id'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}