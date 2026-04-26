import { ColumnFormat } from "./ColumnConfig"

export type RoleData = {
    id: number,
    name: string,
    department_id: number
}

export type DepartmentData = {
    id: number,
    name: string
}

export interface AccountData {
    id?: number,
    name: string,
    email: string,
    first_name: string,
    last_name: string,
    date_of_birth?: Date | null,
    address?: string,
    role: RoleData,
    department: DepartmentData,
    password?: string,
    password_confirmation?: string,
}

export const AccountCellConfig: ColumnFormat[] = [
    {
        label: "Name",
        name: "name",
        type: "string",
        flex: 1
    },
    {
        label: "Email",
        name: "email",
        type: "string",
        flex: 1.5
    }
];