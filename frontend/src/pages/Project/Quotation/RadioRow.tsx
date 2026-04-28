import { FormControl, FormControlLabel, Radio, RadioGroup, TableCell, TableRow } from "@mui/material";
import { memo } from "react";

type Props = {
    row: {
        id: string,
        label: string,
        value: string,
        options: { value: string, label: string}[]
    };
    isEditing: boolean;
    onChange: (id: string, value: string) => void
}

const RadioRow = memo(({ row, isEditing, onChange}: Props) => {
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
            <TableCell>
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