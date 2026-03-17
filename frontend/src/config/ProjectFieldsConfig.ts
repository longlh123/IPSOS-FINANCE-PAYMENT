import { ColumnFormat } from "./ColumnConfig";

export const TableCellConfig: ColumnFormat[] = [
  {
    label: "", // tên cột
    name: "", // value trong api
    type: "image",
    width: 10
  },
  {
    label: "Symphony",
    name: "symphony",
    type: "string",
    width: 100
  },
  {
    label: "Internal Code",
    name: "internal_code",
    type: "string",
    width: 100
  },
  {
    label: "Project Name",
    name: "project_name",
    type: "string",
    width: 100
  },
  {
    label: "Field Start",
    name: "planned_field_start",
    type: "date",
    width: 100
  },
  {
    label: "Field End",
    name: "planned_field_end",
    type: "date",
    width: 100
  },
];

export interface ProjectData {
    id?: number,
    internal_code?: string;
    project_name: string;
    symphony?: string;
    platform: string;
    teams: string[];
    project_types: string[];
    provinces?: { id: number, name: string }[];
    planned_field_start: string;
    planned_field_end: string;
    actual_field_start?: string;
    actual_field_end?: string;
    project_objectives?: string,
    remember_token?: string,
    remember_uuid?: string
};

export const ProjectGeneralFieldsConfig: ColumnFormat[] = [
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



