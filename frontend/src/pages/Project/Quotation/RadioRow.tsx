import { FormControl, FormControlLabel, Radio, RadioGroup, TableCell, TableRow } from "@mui/material";
import { memo } from "react";

type Props = {
    row: {
        id: string,
        label: string,
        value: string,
        options: { value: string | number, label: string, parent?: string | number}[]
    };
    isEditing: boolean;
    onChange: (id: string, value: string) => void
}

const RadioRow = memo(({ row, isEditing, onChange}: Props) => {
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
            <TableCell sx={{ fontSize: "0.8125rem", color: "var(--text-color)" }}>
                <FormControl
                    disabled={!isEditing}
                >
                    <RadioGroup
                        row
                        value={String(row.value)}
                        onChange={(e) => onChange(row.id, e.target.value)}
                    >
                        {row.options.map((option) => (
                            <FormControlLabel
                                key={option.value}
                                value={option.value}
                                control={<Radio size="small"/>}
                                label={option.label}
                            />
                        ))}
                    </RadioGroup>
                </FormControl>
            </TableCell>
        </TableRow>
    );
});

export default RadioRow;