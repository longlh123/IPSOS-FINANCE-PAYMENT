import { ColumnFormat } from "./ColumnConfig";

export interface RecipientListData {
    id: number;
    name: string;
    description: string;
    recipients_count: number;
}

export const RecipientListCellConfig: ColumnFormat[] = [
    {
        label: "Name",
        name: "name",
        type: "string",
        flex: 1.5
    },
    {
        label: "Description",
        name: "description",
        type: "string",
        flex: 2
    },
    {
        label: "Recipients Count",
        name: "recipients_count",
        type: "number",
        flex: 1,
        align: "right"
    }
]