import { ColumnFormat } from "./ColumnConfig";

export interface EmployeeData {
  id: string,
  employee_id: string,
  first_name: string,
  last_name: string,
  number_of_transaction: number,
  number_of_transaction_succesfully: number,
  number_of_phone: number,
  reject_message: string
}

export const TableCellParttimeEmployeesConfig: ColumnFormat[] = [
  {
    label: "",
    name: "profile_picture",
    type: "image",
    width: 50, 
  },
  {
    label: "Id",
    name: "employee_id",
    type: "string",
    width: 100, 
  },
  {
    label: "First Name",
    name: "first_name",
    type: "string",
    width: 100,
  },
  {
    label: "Last Name",
    name: "last_name",
    type: "string",
    width: 100,
  },
  {
    label: "Transactions",
    name: "number_of_transaction",
    type: "number",
    width: 200,
  },
  {
    label: "Successful transactions",
    name: "number_of_transaction_succesfully",
    type: "number",
    width: 200,
  },
  {
    label: "Transactions with respondent's phone number differing from the interview",
    name: "number_of_phone",
    type: "number",
    width: 400,
  },
  {
    label: "Reason for rejection",
    name: "reject_message",
    type: "string",
    width: 400,
  },
];
