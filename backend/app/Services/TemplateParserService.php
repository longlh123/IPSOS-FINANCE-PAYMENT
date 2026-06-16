<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TemplateParserService
{
    public function parse(string $filePath, string $projectType): array
    {
        $mtime    = file_exists($filePath) ? filemtime($filePath) : 0;
        $cacheKey = 'quotation_schema_' . md5($filePath . $projectType) . '_' . $mtime;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($filePath, $projectType) {
            return $this->doParse($filePath, $projectType);
        });
    }

    private function doParse(string $filePath, string $projectType): array
    {
        $spreadsheet = IOFactory::load($filePath);

        $allSheets = [];
        foreach ($spreadsheet->getSheetNames() as $index => $name) {
            $allSheets[$name] = $spreadsheet->getSheet($index)->toArray(null, true, true, false);
        }

        foreach (['FIELDS', 'CONFIG', 'SELECT'] as $required) {
            if (!isset($allSheets[$required])) {
                throw new \Exception("Missing sheet: {$required}");
            }
        }

        $dropdownMap = $this->parseSelectSheet($allSheets['SELECT']);
        $configMap   = $this->parseConfigSheet($allSheets['CONFIG'], $dropdownMap, $projectType);

        return $this->parseFieldSheet($allSheets['FIELDS'], $dropdownMap, $configMap, $projectType);
    }

    private function parseSelectSheet(array $rows): array
    {
        $map = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $key   = $row[0] ?? null;
            $value = $row[1] ?? null;
            $label = $row[2] ?? null;

            if (!$key || !$value || !$label) continue;

            $map[$key][] = [
                'value' => trim((string) $value),
                'label' => trim((string) $label),
            ];
        }

        return $map;
    }

    private function parseConfigSheet(array $rows, array $dropdownMap, string $projectType): array
    {
        $configMap = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $groupKey           = $row[0] ?? null;
            $fieldName          = $row[1] ?? null;
            $label              = $row[2] ?? null;
            $type               = $row[3] ?? null;
            $required           = $row[4] ?? null;
            $default            = $row[5] ?? null;
            $optionsKey         = $row[6] ?? null;
            $projectTypeOptions = $row[7] !== null
                ? array_map('trim', explode(',', (string) $row[7]))
                : [];

            if (!$groupKey || !$fieldName) continue;

            $field = [
                'name'     => trim((string) $fieldName),
                'label'    => trim((string) $label),
                'type'     => trim((string) $type),
                'required' => (bool) $required,
                'default'  => $default,
                'hidden'   => !in_array($projectType, $projectTypeOptions),
            ];

            if (in_array($type, ['select', 'single-select', 'multi-select', 'radio', 'checkbox']) && $optionsKey) {
                if (str_starts_with($optionsKey, 'db:')) {
                    $table_name = str_replace('db:', '', $optionsKey);

                    if ($table_name === 'categories' || $table_name === 'subcategories') {
                        $parent_id = $table_name === 'categories' ? 'industry_id' : 'category_id';

                        $field['options'] = DB::table($table_name)
                            ->select("id as value", "name as label", "{$parent_id} as parent")
                            ->get()
                            ->map(fn($item) => [
                                'value'  => $item->value,
                                'label'  => $item->label,
                                'parent' => $item->parent,
                            ])
                            ->toArray();
                    } else {
                        $field['options'] = DB::table($table_name)
                            ->select('id as value', 'name as label')
                            ->get()
                            ->map(fn($item) => [
                                'value' => $item->value,
                                'label' => $item->label,
                            ])
                            ->toArray();
                    }
                } else {
                    $optionsKey      = str_replace('excel:', '', $optionsKey);
                    $field['options'] = $dropdownMap[$optionsKey] ?? [];
                }
            }

            $configMap[$groupKey][] = $field;
        }

        return $configMap;
    }

    private function parseFieldSheet(array $rows, array $dropdownMap, array $configMap, string $projectType): array
    {
        $schema = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $fieldName  = $row[0] ?? null;
            $label      = $row[1] ?? null;
            $type       = $row[2] ?? null;
            $required   = $row[3] ?? null;
            $default    = $row[4] ?? null;
            $configKey  = $row[5] ?? null;
            $optionsKey = $row[6] ?? null;
            $layoutXS   = $row[7] ?? null;
            $layoutSM   = $row[8] ?? null;
            $layoutMD   = $row[9] ?? null;
            $projectTypeOptions = $row[10] !== null
                ? array_map('trim', explode(',', (string) $row[10]))
                : [];

            if (!$fieldName) continue;

            $field = [
                'name'     => trim((string) $fieldName),
                'label'    => trim((string) $label),
                'type'     => trim((string) $type),
                'required' => (bool) $required,
                'default'  => $default,
                'hidden'   => !in_array($projectType, $projectTypeOptions),
                'layout'   => ['xs' => $layoutXS, 'sm' => $layoutSM, 'md' => $layoutMD],
            ];

            if (in_array($type, ['select', 'single-select', 'multi-select', 'radio', 'checkbox']) && $optionsKey) {
                if (str_starts_with($optionsKey, 'db:')) {
                    $table_name = str_replace('db:', '', $optionsKey);

                    $field['options'] = DB::table($table_name)
                        ->select('id as value', 'name as label')
                        ->get()
                        ->map(fn($item) => [
                            'value' => $item->value,
                            'label' => $item->label,
                        ])
                        ->toArray();
                } else {
                    $optionsKey       = str_replace('excel:', '', $optionsKey);
                    $field['options'] = $dropdownMap[$optionsKey] ?? [];
                }
            }

            if (in_array($type, ['repeater', 'section']) && $configKey) {
                $field['fields'] = $configMap[$configKey] ?? [];
            }

            $schema[] = $field;
        }

        return $schema;
    }
}
