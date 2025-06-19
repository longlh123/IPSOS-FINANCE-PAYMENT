export interface ColumnFormat {
    label: string,
    name: string,
    type: "string" | "number" | "select" | "image" | "date" | "menu",
    width?: number, 
    options?: string[],
}