import { ColumnFormat } from "./ColumnConfig";

export const EditProjectConfig: ColumnFormat[] = [
  {
    label: "Internal Code",
    name: "internal_code",
    type: "string"
  },
  {
    label: "Project Name",
    name: "project_name",
    type: "string"
  },
  {
    label: "Symphony",
    name: "symphony",
    type: "string",
  },
  {
    label: "Job Number",
    name: "job_number",
    type: "string",
  },
  {
    label: "Platform",
    name: "platform",
    type: "select",
    options: ['iField', 'Dimensions'],
  },
  {
    label: "Status",
    name: "status",
    type: "select",
    options: []
  },
  {
    label: "Team",
    name: "teams",
    type: "select",
    options: [],
  },
  {
    label: "Project Type",
    name: "project_types",
    type: "select",
    options: [],
  },
  {
    label: "Start FW",
    name: "planned_field_start",
    type: "date",
  },
  {
    label: "End FW",
    name: "planned_field_end",
    type: "date",
  },
  {
    label: "Actual Start FW",
    name: "actual_field_start",
    type: "date",
  },
  {
    label: "Actual End FW",
    name: "actual_field_end",
    type: "date",
  },
];
