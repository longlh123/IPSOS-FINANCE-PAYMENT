import { GridColDef } from "@mui/x-data-grid";

export interface TransactionData {
    id: string,
    transaction_id: string,
    symphony: string,
    internal_code: string,
    project_name: string,
    province_name: string,
    employee_id: string,
    first_name: string,
    last_name: string,
    full_name: string,
    interview_start: string,
    interview_end: string,
    shell_chainid: string
    respondent_id: string,
    respondent_phone_number: string,
    phone_number: string,
    project_respondent_status: string,
    channel: string,
    reject_message: string,
    service_code: string,
    amount: number,
    discount: number,
    payment_amt: number,
    payment_pre_tax: number,
    transaction_status: string,
    created_at: string
};

export const TableTransactionsConfig: GridColDef[] = [
    {
        field: "symphony",
        headerName: "Symphony",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "project_name",
        headerName: "Project Name",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "province_name",
        headerName: "Province",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "employee_id",
        headerName: "Interviewer ID",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "full_name",
        headerName: "Interviewer Name",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "transaction_id",
        headerName: "Transaction ID",
        flex: 1.5,
        minWidth: 300
    },
    {
        field: "shell_chainid",
        headerName: "Shell ChainID",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "respondent_id",
        headerName: "Respondent ID",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "respondent_phone_number",
        headerName: "Respondent Phone Number",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "phone_number",
        headerName: "Phone Number",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "service_code",
        headerName: "Service Code",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "project_respondent_status",
        headerName: "Project Respondent Status",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "channel",
        headerName: "Channel",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "reject_message",
        headerName: "Reject Message",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "amount",
        headerName: "Amount",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "discount",
        headerName: "Discount",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "payment_amt",
        headerName: "Payment Amount",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "payment_pre_tax",
        headerName: "Pre-tax Payment",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "transaction_status",
        headerName: "Transaction Status",
        flex: 1.5,
        minWidth: 150
    },
    {
        field: "created_at",
        headerName: "Created At",
        flex: 1.5,
        minWidth: 150
    },
];