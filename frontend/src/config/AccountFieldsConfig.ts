import { ColumnFormat } from "./ColumnConfig"

export interface AccountData {
    id?: number,
    name: string,
    email: string,
    first_name: string,
    last_name: string,
    date_of_birth?: Date | null,
    address?: string,
    role: string,
    department: string,
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
    },
    {
        label: "Role",
        name: "role",
        type: "string",
        flex: 1,
    },
    {
        label: "Deparment",
        name: "department",
        type: "string",
        flex: 1,
    }
];