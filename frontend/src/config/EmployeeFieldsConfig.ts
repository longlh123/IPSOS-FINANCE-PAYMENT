import { GridColDef } from "@mui/x-data-grid";
import { ColumnFormat } from "./ColumnConfig"

export interface EmployeeData {
  id: number,
  employee_id: string,
  first_name: string,
  last_name: string,
  full_name: string,
  role: string
};

export const TableParttimeEmployeesConfig: GridColDef[] = [
    {
        field: "employee_id",
        headerName: "ID",
        flex: 1
    },
    {
        field: "full_name",
        headerName: "Full Name",
        flex: 1.5
    }
];