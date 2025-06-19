export interface ColumnConfig {
    label: string,
    name: string,
    type: "string" | "number" | "select" | "image" | "date",
    width: number,
    options?: string[],
}