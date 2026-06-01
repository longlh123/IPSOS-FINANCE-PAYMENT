import { 
    Button, 
    Paper, 
    Table, 
    TableBody, 
    TableContainer
} from "@mui/material";
import { useCallback, useEffect, useState } from "react";
import { DynamicFormProps, renderField } from "../../../utils/renderFields";

const QuotationDynamicForm: React.FC<DynamicFormProps> = ({ schema, initialData, isEditting, onSubmit, onProjectTypeChange }) => {

    const [ rows, setRows ] = useState<any>({});

    useEffect(() => {
        if(initialData) {
            setRows(initialData);
        }
    }, [initialData]);

    const updateRow = useCallback((id: string, value: any) => {
        setRows((prev: any) => ({
            ...prev,
            [id]: value
        }));
        if (id === 'project_types' && onProjectTypeChange) {
            const type = Array.isArray(value) ? value[0] : value;
            if (type) onProjectTypeChange(type);
        }
    }, [onProjectTypeChange]);

    const shouldShowBoosterCondition = rows['sam']

    const [ editingId, setEditingId ] = useState<string | null>(null);

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit(rows);
            }}
        >
            <Paper
                elevation={0}
                sx={{
                    border: "1px solid",
                    borderColor: "var(--body-color)",
                    borderRadius: "12px",
                    overflow: "hidden",
                    backgroundColor: "var(--background-color)",
                }}
            >
                <TableContainer>
                    <Table size="small">
                        <TableBody>
                            {schema.map((field) => renderField({
                                    field,
                                    rows,
                                    isEditting,
                                    editingId,
                                    setEditingId,
                                    updateRow
                                })
                            )}
                        </TableBody>
                    </Table>
                </TableContainer>
            </Paper>
            <Button
                type="submit"
                className="btn"
                sx={{ mt: 2 }}
                disabled={!isEditting}
            >
                Save
            </Button>
        </form>
    )
}

export default QuotationDynamicForm;