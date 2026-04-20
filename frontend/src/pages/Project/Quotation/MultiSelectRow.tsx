import { Autocomplete, Checkbox, TableCell, TableRow, TextField } from "@mui/material";
import { memo } from "react";

type Option = { value: string, label: string };

type Props = {
    row: {
        id: string,
        label: string,
        value: string[],
        options: Option[]
    };
    isEditing: boolean;
    onChange: (id: string, value: string[]) => void; 
}

const MultiSelectRow = memo(({ row, isEditing, onChange}: Props) => {
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
                <Autocomplete
                    multiple
                    disableCloseOnSelect //Khi chọn 1 option thì dropdown không bị đóng lại
                    options={row.options || []} 
                    value={row.options.filter((opt =>
                        (row.value || []).includes(opt.value)
                    )) || []}
                    disabled={!isEditing}
                    onChange={(event, newValue) => onChange(row.id, newValue.map(v => v.value))}
                    getOptionLabel={(option) => option.label} //quyết định hiển thị label
                    isOptionEqualToValue={(option, value) => 
                        option.value === value.value
                    } //tick checkbox nếu đúng value
                    renderOption={(props, option, { selected }) => (
                        <li {...props}>
                            <Checkbox
                                style={{ marginRight: 8 }}
                                checked={selected}
                            />
                            {option.label}
                        </li>
                    )}
                    renderInput={(params) => (
                        <TextField
                            {...params}
                            size="small"
                            placeholder="Select..."
                        />
                    )}
                />
            </TableCell>
        </TableRow>
    );

});

export default MultiSelectRow;