import { Box, TableCell, TableRow } from "@mui/material";
import { memo } from "react";
import ReactQuill from "react-quill";
import "react-quill/dist/quill.snow.css";

type Props = {
    row: {
        id: string,
        label: string,
        value: string,
        placeholder?: string,
    };
    isEditing: boolean;
    isActive?: boolean;
    onStartEdit?: () => void;
    onStopEdit?: () => void;
    onChange: ( id: string, value: string ) => void
}

const RichTextRow = memo(({ row, isEditing, onChange }: Props) => {
    return (
        <TableRow
            sx={{
                "&:hover": { backgroundColor: "rgba(0, 157, 156, 0.05)" },
                "&:last-child td": { border: 0 },
            }}
        >
            <TableCell
                width={200}
                sx={{ fontWeight: 600, fontSize: "0.8125rem", color: "var(--text-color)" }}
            >
                {row.label}
            </TableCell>
            <TableCell sx={{ fontSize: "0.8125rem", color: "var(--text-color)" }}>
                <Box sx={{
                    '& .ql-toolbar': { display: isEditing ? 'block' : 'none' },
                    '& .ql-container': {
                        borderTop: isEditing ? undefined : '1px solid #ccc',
                        borderRadius: isEditing ? undefined : '4px',
                    },
                    opacity: isEditing ? 1 : 0.7,
                }}>
                    <ReactQuill
                        theme="snow"
                        readOnly={!isEditing}
                        value={row.value || ""}
                        placeholder={row.placeholder}
                        onChange={(value) => onChange(row.id, value)}
                    />
                </Box>
            </TableCell>
        </TableRow>
    )
});

export default RichTextRow;
