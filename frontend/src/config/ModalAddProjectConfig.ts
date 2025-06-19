import { ColumnFormat } from "./ColumnConfig";

export const ModalAddProjectConfig: ColumnFormat[] = [
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
    label: "Platform",
    name: "platform",
    type: "select",
    options: ['iField', 'Dimensions'],
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
];