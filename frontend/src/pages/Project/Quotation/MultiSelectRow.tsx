import { Autocomplete, Checkbox, TableCell, TableRow, TextField } from "@mui/material";
import { memo } from "react";

type Option = { value: string, label: string };

type Props = {
    row: {
        id: string,
        label: string,
        value: Option[],
        options: Option[]
    };
    isEditing: boolean;
    onChange: (id: string, value: any) => void; 
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
                {
                    (isEditing) ? (
                        <Autocomplete
                            multiple
                            disableCloseOnSelect //Khi chọn 1 option thì dropdown không bị đóng lại
                            options={row.options || []} 
                            value={row.value || []}
                            disabled={!isEditing}
                            sx={{
                                "& .Mui-disabled": {
                                    WebkitTextFillColor: "#000",
                                    opacity: 0.9
                                },
                                "& . MuiInputBase-root.Mui.disabled": {
                                    opacity: 0.9,
                                    WebkitTextFillColor: "#000",
                                }
                            }}
                            onChange={(event, newValue) => onChange(row.id, newValue)}
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
                    ) : (
                        (() => {
                            const value = row.value || [];
                            
                            if(value.length == 0){
                                return "-"
                            } else {
                                return (
                                    <span>{row.value.map((option) => option.label).join(', ')}</span>
                                )
                            }
                        })()
                    )
                }
                
            </TableCell>
        </TableRow>
    );

});

export default MultiSelectRow;