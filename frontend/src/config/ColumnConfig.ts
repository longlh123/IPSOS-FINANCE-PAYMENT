import React from "react";

export interface ColumnFormat {
    label: string,
    name: string,
    type: "string" | "number" | "select" | "image" | "date" | "menu",
    align?: "center" | "left" | "right"
    width?: number, 
    flex?: number,
    options?: { value: any, label: string }[],
    grid?: number,
    order?: number,
    visible?: boolean,
    renderHeader?: () => React.ReactNode,
    renderCell?: (row: any) => React.ReactNode,
    renderAction?: (row: any) => React.ReactNode,
}