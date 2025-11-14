import { GridColDef } from "@mui/x-data-grid";
import { ColumnFormat } from "./ColumnConfig"

export interface EmployeeData {
  id: number,
  employee_id: string,
  first_name: string,
  last_name: string,
  role: string
};

export const TableParttimeEmployeesConfig: GridColDef[] = [
    {
        field: "ID",
        headerName: "employee_id",
        flex: 1
    },
    {
        field: "First Name",
        headerName: "first_name",
        flex: 1
    },
    {
        field: "Last Name",
        headerName: "last_name",
        flex: 1.5
    }
];