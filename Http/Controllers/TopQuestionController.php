<?php

namespace App\Http\Controllers;

use App\Http\Resources\TopQuestionResource;
use App\Models\TopQuestion;

class TopQuestionController extends Controller
{
    public function index(): array
    {
        $groupedTopQuestions = TopQuestion::select('question as title', 'answer as text', 'group_name')
        	->where('is_feedback', 0)
            ->get()->groupBy('group_name')->toArray();

        return TopQuestionResource::collection(array_values($groupedTopQuestions))->resolve();
    }

    public function feedback(): array
    {
        $groupedTopQuestions = TopQuestion::select('question as title', 'answer as text', 'group_name')
        	->where('is_feedback', 1)
            ->get()->groupBy('group_name')->toArray();

        return TopQuestionResource::collection(array_values($groupedTopQuestions))->resolve();
    }
}
