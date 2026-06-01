import { TableCell, TableRow } from "@mui/material";
import { memo, useEffect, useRef, useState } from "react";
import ReactQuill from "react-quill";
import "react-quill/dist/quill.snow.css";

type Props = {
    row: {
        id: string,
        label: string,
        value: string 
    };
    isEditing: boolean;
    isActive: boolean;
    placeholder?: string;
    onStartEdit: () => void;
    onStopEdit: () => void;
    onChange: ( id: string, value: string ) => void
} 

const RichTextRow = memo(({row, isEditing, isActive, placeholder, onStartEdit, onStopEdit, onChange}: Props) => {
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
    
    return (
        <TableRow
            sx={{
                "&:hover": { backgroundColor: "rgba(0, 157, 156, 0.05)" },
                "&:last-child td": { border: 0 },
            }}
        >
            <TableCell
                width={300}
                sx={{ fontWeight: 600, fontSize: "0.8125rem", color: "var(--text-color)" }}
            >
                {row.label}
            </TableCell>
            <TableCell
                sx={{ fontSize: "0.8125rem", color: "var(--text-color)" }}
                onClick={() => {
                    if(isEditing) onStartEdit();
                }}
            >
                {(isActive && isEditing) ? (
                    <ReactQuill
                        theme="snow"
                        value={row.value || ""}
                        onBlur={() => {
                            setTimeout(() => {
                                onStopEdit();
                            }, 0);
                        }}
                        onChange={(value) => onChange(row.id, value)}
                    />
                ) : (
                    <div
                        dangerouslySetInnerHTML={{ __html: row.value || placeholder || "-" }}
                        style={{ cursor: "pointer" }}
                    ></div>
                )}
            </TableCell>
        </TableRow>
    )
});

export default RichTextRow;