import { ColumnFormat } from "../config/ColumnConfig";

export const TableCellConfig: ColumnFormat[] = [
    {
        label: "",
        name: "vinnet_invoice_date",
        type: "image",
        width: 50, 
    },
    {
        label: "Req. Uuid",
        name: "vinnet_payservice_requuid",
        type: "string",
        width: 400, 
    },
    {
        label: "Recip. #",
        name: "phone_number",
        type: "string",
        width: 100, 
    },
    {
        label: "Total Amount",
        name: "total_amt",
        type: "number",
        width: 300, 
    },
    {
        label: "Discount",
        name: "discount",
        type: "number",
        width: 300, 
    },
    {
        label: "Payment Amount",
        name: "payment_amt",
        type: "number",
        width: 300, 
    },
    {
        label: "Transaction Date",
        name: "created_at",
        type: "date",
        width: 300, 
    },
    {
        label: "Transaction Status",
        name: "vinnet_token_status",
        type: "string",
        width: 100, 
    },
    {
        label: "Status",
        name: "status",
        type: "string",
        width: 100, 
    },
    // {
    //     label: "Resp. #",
    //     name: "respondent_phone_number",
    //     type: "string",
    //     width: 100, 
    // },
    // {
    //     label: "Province",
    //     name: "province",
    //     type: "string",
    //     width: 100, 
    // },
    // {
    //     label: "Employee ID",
    //     name: "employee_id",
    //     type: "string",
    //     width: 100, 
    // },
    // {
    //     label: "Employee Name",
    //     name: "employee_name",
    //     type: "string",
    //     width: 100, 
    // },
    // {
    //     label: "Team",
    //     name: "team",
    //     type: "string",
    //     width: 50, 
    // },
    // {
    //     label: "Respondent ID",
    //     name: "shell_chainid",
    //     type: "string",
    //     width: 100, 
    // },
    // {
    //     label: "Field End",
    //     name: "planned_field_end",
    //     type: "date",
    //     width: 100, 
    // },
    // {
    //     label: "Field End",
    //     name: "planned_field_end",
    //     type: "date",
    //     width: 100, 
    // },
];
