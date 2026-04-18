<?php
//src/Services/SurveyService.php
declare(strict_types=1);

namespace SurveySphere\Services;

use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Database\Models\Survey;

class SurveyService
{
    private SurveyRepository $repository;

    public function __construct(SurveyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllSurveys(): array
    {
        return $this->repository->findAll();
    }

    public function getSurveyByPublicId(string $publicId): ?Survey
    {
        return $this->repository->findByPublicId($publicId);
    }

    public function createSurvey(array $data): Survey
    {
        return $this->repository->create($data);
    }
}