<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TemplateParserService;

class QuotationTemplateController extends Controller
{
    public function parse(Request $request, TemplateParserService $parser)
    {
        $filePath = storage_path('schema/quotation_template.xlsx');

        $schema = $parser->parse($filePath);

        return response()->json($schema);
    }
}
