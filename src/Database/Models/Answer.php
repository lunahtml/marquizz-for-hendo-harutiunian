<?php
//src/Database/Models/Answer.php
declare(strict_types=1);

namespace SurveySphere\Database\Models;

final class Answer
{
    public function __construct(
        public readonly ?int $id = null,
        public int $attemptId = 0,
        public int $questionId = 0,
        public int $optionId = 0,
        public readonly ?string $createdAt = null,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            attemptId: isset($data['attempt_id']) ? (int)$data['attempt_id'] : 0,
            questionId: isset($data['question_id']) ? (int)$data['question_id'] : 0,
            optionId: isset($data['option_id']) ? (int)$data['option_id'] : 0,
            createdAt: $data['created_at'] ?? null,
        );
    }
}