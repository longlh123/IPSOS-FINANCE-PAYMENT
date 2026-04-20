import { Autocomplete, Checkbox, IconButton, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, TextField } from "@mui/material";
import { memo, useEffect, useMemo, useState } from "react";
import { FieldSchema } from "./QuotationDynamicForm";
import SaveIcon from '@mui/icons-material/Save';
import DeleteIcon from '@mui/icons-material/Delete';
import { Rowing } from "@mui/icons-material";
import { RowType } from "./EditableRow";

export interface RepeaterRowData {
    [key: string]: any
};

type Props = {
    row: {
        id: string,
        label: string,
        value: RepeaterRowData[],
        fields: FieldSchema[]
    };
    isEditing: boolean;
    onChange: (id: string, data: RepeaterRowData[]) => void;
};

const RepeaterRow = memo(({ row, isEditing, onChange}: Props) => {

    const [ draft, setDraft ] = useState<RepeaterRowData>({});
    
    const repeaterRows = row.value || [];

    const isDraftValid = useMemo(() => {
        return row.fields.every(field => {
            const value = draft[field.name];

            if(field.required){
                if(Array.isArray(value)){
                    return value.length > 0;
                } else {
                    return value && String(value).length > 0;
                }
            }

            return true;
        });
    },[draft, row.fields]);

    const handleFieldChange = (fieldName: string, value: any) => {
        setDraft(prev => ({
            ...prev,
            [fieldName]: value
        }));
    }

    const handleSave = () => {
        const newRows = [...(row.value || []), draft];

        onChange(row.id, newRows);

        setDraft({});
    }

    const handleDelete = (index: number) => {
        const newRows = (row.value || []).filter((_,i) => i != index);

        onChange(row.id, newRows);
    }

    const renderSavedField = (field: FieldSchema, rowItem: RepeaterRowData) => {
        const value = rowItem[field.name];

        return (
            <TableCell
                key={field.name}
            >
                {Array.isArray(value)
                    ? value.map(v => v.label).join(',')
                    : value.label || value}
            </TableCell>
        );
    }

    const renderField = (field: FieldSchema) => {
        switch(field.type){
            case "number":
                return (
                    <TableCell
                        key={field.name}
                    >
                        <TextField
                            fullWidth
                            autoFocus
                            size="small"
                            disabled={!isEditing}
                            type={field.type}
                            value={draft[field.name] ?? ""}
                            onChange={(e) => handleFieldChange(field.name, e.target.value)}
                        />
                    </TableCell>
                )
            default:
                return (
                    <TableCell
                        key={field.name}
                    >
                        <Autocomplete
                            multiple
                            disableCloseOnSelect //Khi chọn 1 option thì dropdown không bị đóng lại
                            options={field.options || []} 
                            value={draft[field.name] ?? []}
                            disabled={!isEditing}
                            onChange={(event, newValue) => handleFieldChange(field.name, newValue)}
                            getOptionLabel={(option) => option.label} //quyết định hiển thị label
                            isOptionEqualToValue={(option, value) => (
                                option.value === value.value
                            )} //tick checkbox nếu đúng value
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
                );
        }
    }

    const renderMiniTable = (fields: FieldSchema[]) => { 
        return (
            <TableContainer>
                <Table size="small">
                    <TableHead>
                        <TableRow>
                            {fields.map((subField) => (
                                <TableCell key={subField.name}>
                                    {subField.label}
                                </TableCell>
                            ))}
                            <TableCell width={120} sx={{textAlign: "center"}}>
                                Actions
                            </TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {repeaterRows.map((rowItem, rowIndex) => (
                            <TableRow
                                key={rowIndex}
                            >
                                {row.fields.map((subField) => (
                                    renderSavedField(subField, rowItem)
                                ))}
                                <TableCell
                                    sx={{textAlign: "center"}}
                                >
                                    <IconButton
                                        color="error"
                                        disabled={!isEditing}
                                        onClick={() => handleDelete(rowIndex)}
                                    >
                                        <DeleteIcon />
                                    </IconButton>
                                </TableCell>
                            </TableRow>
                        ))}
                        <TableRow>
                            {fields.map((subField) => (
                                renderField(subField)
                            ))}
                            <TableCell
                                sx={{textAlign: "center"}}
                            >
                                <IconButton
                                    color="primary"
                                    disabled={!isDraftValid}
                                    onClick={handleSave}
                                >
                                    <SaveIcon />
                                </IconButton>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </TableContainer>
        )
    };

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
                { renderMiniTable(row.fields) }
            </TableCell>
        </TableRow>
    )
});

export default RepeaterRow;