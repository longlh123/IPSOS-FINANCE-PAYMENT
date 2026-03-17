<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class TemplateParserService
{
    public function parse(string $filePath): array
    {
        $sheet = Excel::toArray([], $filePath);

        $groupsSheet = $this->getBySheetName($filePath, 'GROUPS');
        $fieldsSheet = $this->getBySheetName($filePath, 'FIELDS');
        $configSheet = $this->getBySheetName($filePath, 'CONFIG');
        $selectSheet = $this->getBySheetName($filePath, 'SELECT');

        $dropdownMap = $this->parseSelectSheet($selectSheet);
        $configMap = $this->parseConfigSheet($configSheet, $dropdownMap);
        $groupMap = $this->parseGroupSheet($groupsSheet);

        return $this->parseFieldSheet($fieldsSheet, $dropdownMap, $configMap, $groupMap);
    }
    
    private function getBySheetName(string $filePath, string $sheetName): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $excel = Excel::toArray([], $filePath);

        foreach($sheetNames as $index => $name){
            if($name === $sheetName){
                return $excel[$index];
            }
        }

        throw new \Exception("Missing sheet: {$sheetName}");
    }

    private function parseSelectSheet(array $rows): array
    {
        $map = [];

        foreach($rows as $index => $row){
            if($index === 0) continue;
            
            $key = $row[0] ?? null;
            $value = $row[1] ?? null;

            if(!$key || !$value) continue;

            $map[$key][] = $value;
        }

        return $map;
    }

    private function parseConfigSheet(array $rows, array $dropdownMap): array
    {
        $configMap = [];

        foreach($rows as $index => $row){
            if($index === 0) continue;

            $groupKey    = $row[0] ?? null;
            $fieldName   = $row[1] ?? null;
            $label       = $row[2] ?? null;
            $type        = $row[3] ?? null;
            $required    = $row[4] ?? null;
            $default     = $row[5] ?? null;
            $optionsKey  = $row[6] ?? null;

            if(!$groupKey || !$fieldName) continue;

            $field = [
                'name'     => trim($fieldName),
                'label'    => trim($label),
                'type'     => trim($type),
                'required' => (bool)$required,
                'default'  => $default
            ];

            if($type === 'select' && $optionsKey || $type === 'multi-select'){
                $field['options'] = $dropdownMap[$optionsKey] ?? [];
            }

            $configMap[$groupKey][] = $field;
        }

        return $configMap;
    }

    private function parseGroupSheet(array $rows): array
    {
        $groupMap = [];

        foreach($rows as $index => $row){
            if($index === 0) continue;

            $key = $row[0] ?? null;
            $title = $row[1] ?? null;
            $collapsible = $row[2] ?? null;
            $defaultOpen = $row[3] ?? null;

            if(!$key) continue;

            $groupMap[$key] = [
                'type' => 'group',
                'title' => trim($title),
                'collapsible' => (bool)$collapsible,
                'default_open' => (bool)$defaultOpen,
                'fields' => []
            ];
        }

        return $groupMap;
    }

    private function parseFieldSheet(array $rows, array $dropdownMap, array $configMap, array $groupMap): array
    {
        $schema = [];
        $currentGroupIndex = null;

        foreach($rows as $index => $row){
            if($index === 0) continue;

            $groupKey    = $row[0] ?? null;
            $fieldName   = $row[1] ?? null;
            $label       = $row[2] ?? null;
            $type        = $row[3] ?? null;
            $required    = $row[4] ?? null;
            $default     = $row[5] ?? null;
            $configKey   = $row[6] ?? null;
            $optionsKey  = $row[7] ?? null;
            $layoutXS    = $row[8] ?? null;
            $layoutSM    = $row[9] ?? null;
            $layoutMD    = $row[10] ?? null;

            if(!$fieldName) continue;

            $field = [
                'group_key' => $groupKey ? trim($groupKey) : null,
                'name'     => trim($fieldName),
                'label'    => trim($label),
                'type'     => trim($type),
                'required' => (bool)$required,
                'default'  => $default,
                'layout'   => [
                    'xs' => $layoutXS,
                    'sm' => $layoutSM,
                    'md' => $layoutMD
                ]
            ];

            if($type === 'select' || $type === 'multi-select' || $type === 'radio' || $type === 'checkbox'){
                $field['options'] = $dropdownMap[$optionsKey] ?? []; 
            }

            if($type === 'range' && $optionsKey){
                $field['fields'] = $groupMap[$optionsKey] ?? [];
            }

            if($type === 'repeater' && $configKey){
                $field['fields'] = $configMap[$configKey] ?? [];
            }

            if($type === 'repeater_card' && $configKey){
                $field['fields'] = $configMap[$configKey] ?? [];
            }

            if($groupKey && isset($groupMap[$groupKey])){
                
                if($currentGroupIndex === null || $schema[$currentGroupIndex]['key'] !== $groupKey){
                    $schema[] = [
                        'type' => 'group',
                        'key' => $groupKey,
                        'title' => $groupMap[$groupKey]['title'],
                        'collapsible' => $groupMap[$groupKey]['collapsible'],
                        'default_open' => $groupMap[$groupKey]['default_open'],
                        'fields' => []
                    ];

                    $currentGroupIndex = array_key_last($schema);
                }

                $schema[$currentGroupIndex]['fields'][] = $field;
            } else {
                $currentGroupIndex = null;
                $schema[] = $field;
            }
        }

        return $schema;
    }
}