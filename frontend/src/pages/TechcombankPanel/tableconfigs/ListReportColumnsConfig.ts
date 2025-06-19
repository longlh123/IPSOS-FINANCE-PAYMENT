import { ColumnConfig } from "./columnconfigs"

export const ListReportColumnsConfig: ColumnConfig[] = [
    {
        label: 'ID',
        name: 'id',
        type: 'string',
        width: 50,
    },
    {
        label: 'Name',
        name: 'name',
        type: "string", 
        width: 200,
    },
    {
        label: 'Engament/ Project',
        name: 'engagment',
        type: "string",
        width: 100,
    },
    {
        label: 'Project Type',
        name: 'project_type',
        type: "string",
        width: 100, 
    },
    {
        label: 'Sent out',
        name: 'sent_out',
        type: "number",
        width: 50, 
    },
    {
        label: 'Respond',
        name: 'respond',
        type: "number",
        width: 100, 
    },
    {
        label: 'Respond Rate',
        name: 'respond_rate',
        type: "number",
        width: 100, 
    },
    {
        label: 'Completed & Qualified',
        name: 'completed_qualified',
        type: "number",
        width: 100, 
    },
    {
        label: 'Cancellation',
        name: 'cancellation',
        type: "number",
        width: 100, 
    },
    {
        label: 'Number of Question',
        name: 'number_of_question',
        type: "number",
        width: 100, 
    },
    {
        label: 'Opend Date',
        name: 'open_date',
        type: "date",
        width: 100, 
    },
    {
        label: 'Close Date',
        name: 'close_date',
        type: "date",
        width: 100, 
    },
    {
        label: 'Resource',
        name: 'resource',
        type: "string",
        width: 100, 
    },
];
