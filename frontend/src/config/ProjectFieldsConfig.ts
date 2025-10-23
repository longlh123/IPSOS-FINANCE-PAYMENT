import { ColumnFormat } from "./ColumnConfig";

export interface ProjectData {
    internal_code: string;
    symphony: string;
    project_name: string;
    platform: string;
    teams: string[];
    project_types: string[];
    provinces?: { id: number, name: string }[];
    planned_field_start: string;
    planned_field_end: string;
    actual_field_start?: string;
    actual_field_end?: string;
    remember_token?: string,
    remember_uuid?: string
};

export const ProjectGeneralFieldsConfig: ColumnFormat[] = [
  {
    label: "Internal Code",
    name: "internal_code",
    type: "string",
    grid: 6,
    order: 1,
    visible: true
  },
  {
    label: "Symphony",
    name: "symphony",
    type: "string",
    grid: 6,
    order: 2,
    visible: true
  },
  {
    label: "Project Name",
    name: "project_name",
    type: "string",
    grid: 12,
    order: 3,
    visible: true
  },
  {
    label: "Platform",
    name: "platform",
    type: "select",
    options: [
      { value: 'iField', label: 'iField' },
      { value: 'Dimensions', label:  'Dimensions' },
      { value: 'Other', label: 'Other' }
    ],
    visible: false
  },
  {
    label: "Team",
    name: "teams",
    type: "select",
    options: [],
    visible: false
  },
  {
    label: "Project Types",
    name: "project_types",
    type: "select",
    options: [],
    visible: false
  },
  {
    label: "Planned Start FW",
    name: "planned_field_start",
    type: "date",
    visible: false
  },
  {
    label: "Planned End FW",
    name: "planned_field_end",
    type: "date",
    visible: false
  },
];

export const ProjectFieldsConfig: ColumnFormat[] = [
  ... ProjectGeneralFieldsConfig,
  {
    label: "Remember Token",
    name: "remember_token",
    type: "string",
    grid: 12,
    order: 4,
    visible: true
  },
  {
    label: "Actual Start FW",
    name: "actual_field_start",
    type: "date",
    visible: false
  },
  {
    label: "Actual End FW",
    name: "actual_field_end",
    type: "date",
    visible: false
  },
]



