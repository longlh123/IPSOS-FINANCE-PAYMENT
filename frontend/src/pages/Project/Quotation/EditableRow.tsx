import { TableCell, TableRow, TextField } from "@mui/material";
import { memo, useEffect, useRef, useState } from "react";
import { InputRule, useInputRule } from "../../../hook/useInputRule";

export type RowType = "text" | "number";

type Props = {
    row: {
        id: string,
        label: string,
        value: any,
        type: RowType,
        rule?: InputRule
    };
    isEditing: boolean;
    isActive: boolean;
    onStartEdit: () => void;
    onStopEdit: () => void;
    onChange: ( id: string, value: string ) => void;
} 

const EditableRow = memo(({ row, isEditing, isActive, onStartEdit, onStopEdit, onChange}: Props) => {

    const { apply } = useInputRule();

    const ref = useRef<HTMLTableCellElement>(null);

    useEffect(() => {
        if(!isActive) return;

        const handleClickOutSide = (e: MouseEvent) => {
            if(ref.current && !ref.current.contains(e.target as Node)){
                onStopEdit();
            }
        };

        document.addEventListener("mousedown", handleClickOutSide);

        return () => {
            document.removeEventListener("mousedown", handleClickOutSide);
        };
    }, [isActive, onStopEdit]);
    
    const renderInput = () => {
        switch(row.type){
            case "number":
                return (
                    <TextField
                        fullWidth
                        autoFocus
                        size="small"
                        type="number"
                        value={row.value || ""}
                        onChange={(e) => onChange(row.id, apply(e.target.value, row.rule))}
                        onBlur={onStopEdit}
                        onKeyDown={onStopEdit}
                    />
                )
            default:
                return (
                    <TextField
                        fullWidth
                        autoFocus
                        size="small"
                        value={row.value || ""}
                        onChange={(e) => onChange(row.id, apply(e.target.value, row.rule))}
                        onBlur={onStopEdit}
                        onKeyDown={onStopEdit}
                    />
                )
        }
    }

    const isUrl = (value: string) => {
        try{
            new URL(value);
            return true;
        } catch{
            return false;
        }
    } 

    return (
        <TableRow>
            <TableCell 
                width={300}
                sx={{
                    fontWeight: 600
                }}
            >
                {row.label}
            </TableCell>
            <TableCell
                ref={ref}
                onClick={() => {
                    if(isEditing) onStartEdit();
                }}
            >
                {
                    (isActive && isEditing) ? (
                        renderInput()
                    ) : (
                        (() => {
                            const value = row.value?.trim();

                            if(!value) return "-";

                            if(isUrl(value)){
                                return (
                                    <a
                                        href={value}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        onClick={(e) => e.stopPropagation()}
                                        style={{
                                            color: "#1976d2", 
                                            textDecoration: "underline"
                                        }}
                                    >
                                        {value}
                                    </a>
                                )
                            } else {
                                return (
                                    <span>{value}</span>
                                )
                            }
                        })()
                    )
                }
            </TableCell>
        </TableRow>
    )
});

export default EditableRow;