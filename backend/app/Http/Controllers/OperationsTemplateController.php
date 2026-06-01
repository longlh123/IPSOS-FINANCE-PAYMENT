<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TemplateParserService;
use App\Models\Project;

class OperationsTemplateController extends Controller
{
    private TemplateParserService $parser;

    public function __construct(TemplateParserService $parser)
    {
        $this->parser = $parser;
    }

    public function parse(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $projectType = $request->query('project_type')
                        ?? $project->projectTypes()->pluck('name')->implode(',');

        $filePath = storage_path('schema/operations_template.xlsx');

        $schema = $this->parser->parse($filePath, $projectType);

        return response()->json($schema);
    }
}
