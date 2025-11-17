import { GridColDef } from "@mui/x-data-grid";
import { ColumnFormat } from "./ColumnConfig"

export interface EmployeeData {
    id: number,
    employee_id: string,
    first_name: string,
    last_name: string,
    full_name: string,
    gotit_count: number,
    vinnet_count: number,
    transaction_count: number
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
    },
    {
        field: "gotit_count",
        headerName: "GotIt",
        flex: 1
    },
    {
        field: "vinnet_count",
        headerName: "Vinnet",
        flex: 1
    },
    {
        field: "transaction_count",
        headerName: "Transaction Total",
        flex: 1
    }
];