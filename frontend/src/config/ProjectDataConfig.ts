export interface ProjectObject {
    [key: string]: any;
};

export interface ProjectData {
    internal_code: string;
    project_name: string;
    platform: string;
    teams: string[];
    project_types: string[];
    planned_field_start: string;
    planned_field_end: string;
};