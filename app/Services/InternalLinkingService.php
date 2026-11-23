<?php

namespace App\Services;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\GradeLearningArea;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\Term;

class InternalLinkingService
{
    public function __construct(
        private readonly ReadinessService $readiness,
    ) {}

    /**
     * Internal links for a term landing page (Section 13).
     *
     * @return array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>
     */
    public function forTermLanding(Grade $grade, LearningArea $area, Term $term, ExamType $examType): array
    {
        $groups = [];

        // 1. Other ready exam types in the same term
        $otherExamTypes = $this->readiness->getReadyExamTypesForTerm($grade, $area, $term)
            ->filter(fn (ExamType $et) => $et->id !== $examType->id)
            ->map(fn (ExamType $et) => [
                'label' => "{$grade->name} {$area->name} {$term->name} {$et->name}",
                'url' => "/{$grade->slug}/{$area->slug}/{$term->slug}/{$et->slug}",
            ])->values()->all();

        if ($otherExamTypes !== []) {
            $groups[] = ['heading' => "Other {$term->name} Assessments", 'links' => $otherExamTypes];
        }

        // 2. Same subject in other ready terms
        $otherTerms = Term::query()->ordered()->whereKeyNot($term->id)->get()
            ->filter(fn (Term $t) => $this->readiness->evaluateTermLanding($grade, $area, $t, $examType)->ready)
            ->map(fn (Term $t) => [
                'label' => "{$grade->name} {$area->name} {$t->name} {$examType->name}",
                'url' => "/{$grade->slug}/{$area->slug}/{$t->slug}/{$examType->slug}",
            ])->values()->all();

        if ($otherTerms !== []) {
            $groups[] = ['heading' => "{$area->name} in Other Terms", 'links' => $otherTerms];
        }

        // 3. Previous & next grade ONLY where grade_learning_area pivot confirms subject exists AND landing is ready
        $adjacentGrades = Grade::query()
            ->active()
            ->whereIn('sort_order', [$grade->sort_order - 1, $grade->sort_order + 1])
            ->ordered()
            ->get();

        $adjacentLinks = [];
        foreach ($adjacentGrades as $adjGrade) {
            $existsInPivot = GradeLearningArea::query()
                ->where('grade_id', $adjGrade->id)
                ->where('learning_area_id', $area->id)
                ->verified()
                ->exists();

            if ($existsInPivot && $this->readiness->evaluateTermLanding($adjGrade, $area, $term, $examType)->ready) {
                $adjacentLinks[] = [
                    'label' => "{$adjGrade->name} {$area->name} {$term->name} {$examType->name}",
                    'url' => "/{$adjGrade->slug}/{$area->slug}/{$term->slug}/{$examType->slug}",
                ];
            }
        }

        if ($adjacentLinks !== []) {
            $groups[] = ['heading' => "{$area->name} in Adjacent Grades", 'links' => $adjacentLinks];
        }

        // 4. Parent hubs (only if ready)
        $hubLinks = [];
        if ($this->readiness->evaluateLearningAreaHub($grade, $area)->ready) {
            $hubLinks[] = ['label' => "All {$grade->name} {$area->name} Exams", 'url' => "/{$grade->slug}/{$area->slug}"];
        }
        if ($this->readiness->evaluateGradeHub($grade)->ready) {
            $hubLinks[] = ['label' => "All {$grade->name} Learning Areas", 'url' => "/{$grade->slug}"];
        }
        if ($hubLinks !== []) {
            $groups[] = ['heading' => 'Related Curriculum Hubs', 'links' => $hubLinks];
        }

        return $groups;
    }

    /**
     * Internal links for a national exam subject or year page (Section 13).
     *
     * @return array<int, array{heading: string, links: array<int, array{label: string, url: string}>}>
     */
    public function forNationalPage(NationalExam $exam, LearningArea $area, ?int $currentYear = null): array
    {
        $groups = [];

        $hubLinks = [];
        if ($this->readiness->evaluateNationalHub($exam)->ready) {
            $hubLinks[] = ['label' => "{$exam->name} Main Hub", 'url' => "/{$exam->slug}"];
        }
        if ($currentYear !== null && $this->readiness->evaluateNationalSubject($exam, $area)->ready) {
            $hubLinks[] = ['label' => "All {$exam->name} {$area->name} Years", 'url' => "/{$exam->slug}/{$area->slug}-past-papers"];
        }
        if ($hubLinks !== []) {
            $groups[] = ['heading' => "{$exam->name} Navigation", 'links' => $hubLinks];
        }

        $otherSubjects = $this->readiness->getReadySubjectsForNationalExam($exam)
            ->filter(fn (LearningArea $other) => $other->id !== $area->id)
            ->take(6)
            ->map(fn (LearningArea $other) => [
                'label' => "{$exam->name} {$other->name} Past Papers",
                'url' => "/{$exam->slug}/{$other->slug}-past-papers",
            ])->values()->all();

        if ($otherSubjects !== []) {
            $groups[] = ['heading' => "Other {$exam->name} Subjects", 'links' => $otherSubjects];
        }

        return $groups;
    }
}
