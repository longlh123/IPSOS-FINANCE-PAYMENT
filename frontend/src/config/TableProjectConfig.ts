import { ColumnFormat } from "./ColumnConfig";

export const TableCellConfig: ColumnFormat[] = [
  {
    label: "", // tên cột
    name: "", // value trong api
    type: "image",
    width: 10
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
