import { Accordion, AccordionDetails, AccordionSummary, Autocomplete, Box, Button, Card, CardContent, CardHeader, Checkbox, FormControl, FormControlLabel, FormLabel, Grid, IconButton, MenuItem, Paper, Radio, RadioGroup, Select, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, TextField, Typography } from "@mui/material";
import { useCallback, useEffect, useState } from "react";
import DeleteIcon from "@mui/icons-material/Delete";
import { ProjectData } from "../../../config/ProjectFieldsConfig";
import ArrowDownwardIcon from '@mui/icons-material/ArrowDownward';
import EditableRow from "./EditableRow";
import RichTextRow from "./RichTextRow";
import RadioRow from "./RadioRow";
import MultiSelectRow from "./MultiSelectRow";
import RepeaterRow from "./RepeaterRow";

interface LayoutSchema {
    xs: number,
    sm: number,
    md: number
}

export interface FieldSchema {
    name: string;
    label: string;
    type: string;
    required?: boolean;
    default?: string | number;
    layout?: LayoutSchema,
    options?: {value: string, label: string}[];
    fields?: FieldSchema[];
}

interface DynamicFormProps {
    schema: FieldSchema[];
    onSubmit: (data: any) => void;
    projectData: ProjectData | null,
    initialQuotationData?: any,
    isEditting: boolean,
}

const QuotationDynamicForm: React.FC<DynamicFormProps> = ({ schema, projectData, initialQuotationData, isEditting, onSubmit }) => {

    const [ rows, setRows ] = useState<any>({}); 

    useEffect(() => {
        if(initialQuotationData) {
            setRows(initialQuotationData);
        }
    }, [initialQuotationData]);

    const updateRow = useCallback((id: string, value: any) => {
        setRows((prev: any) => ({
            ...prev,
            [id]: value
        }));
    }, []);

    const [ tmpSampleType, setTmpSampleType ] = useState<string | null>(null);

    const [ editingId, setEditingId ] = useState<string | null>(null);

    const renderField = (field: FieldSchema) => {
        if(field.type === 'text' || field.type === 'number'){
            let disabled = field.name == 'internal_code' ? true : !isEditting;
            
            const rule = field.name === 'project_name' ? "uppercaseNoSpecial" : (field.name === 'internal_code' ? "maskXXXX_XXXX" : undefined);

            return (
                <EditableRow
                    key={field.name}
                    row={{
                        id: field.name, 
                        label: field.label, 
                        type: field.type,
                        value: rows[field.name], 
                        ...(rule ? { rule } : {})
                    }}
                    isEditing={isEditting}
                    isActive={editingId === field.name}
                    onStartEdit={() => setEditingId(field.name)}
                    onStopEdit={() => setEditingId(field.name)}
                    onChange={updateRow}
                />
            )
        }

        if(field.type === 'textarea'){
            return (
                <RichTextRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: rows[field.name]}}
                    isEditing={isEditting}
                    isActive={editingId === field.name}
                    onStartEdit={() => setEditingId(field.name)}
                    onStopEdit={() => setEditingId(field.name)}
                    onChange={updateRow}
                />
            )
        }

        if(field.type === 'radio'){
            return (
                <RadioRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: rows[field.name], options: field.options ?? []}}
                    isEditing={isEditting}
                    onChange={updateRow}
                />
            )
        }

        if(field.type === 'multi-select'){
            return (
                <MultiSelectRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: rows[field.name], options: field.options ?? []}}
                    isEditing={isEditting}
                    onChange={updateRow} 
                />
            )
        }

        if(field.type === 'repeater'){
            return (
                <RepeaterRow
                    key={field.name}
                    row={{id:field.name, label: field.label, value: rows[field.name], fields: field.fields ?? []}}
                    isEditing={isEditting}
                    onChange={updateRow}
                />
            )
        }

        

        // if(field.type === 'range'){
        //     return (
        //         <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
        //             <div style={{ marginBottom: "1rem" }}>
        //                 <Typography variant="body2" gutterBottom>
        //                     {field.label}
        //                 </Typography>
        //                 <Grid container spacing={2}>
        //                     {field.fields?.map((f) => {
        //                         const value = formData[field.name!]?.[f.name!] ?? "";

        //                         return (
        //                             <Grid item xs={6} key={f.name}>
        //                                 <TextField
        //                                     fullWidth
        //                                     size="small"
        //                                     type={f.type}
        //                                     label={f.label}
        //                                     value={value}
        //                                     required={f.required}
        //                                     disabled={!isEditting}
        //                                     onChange={(e) => handleChange(field.name!, {
        //                                         ...formData[field.name!], 
        //                                         [f.name!]: e.target.value
        //                                     })}
        //                                 />
        //                             </Grid>
        //                         )
        //                     })}
        //                 </Grid>
        //             </div>
        //         </Grid>
        //     )
        // }

        // if (field.type === "repeater") {
        //     return (
        //         <Grid item xs={12} key={field.name}>
        //             <Typography variant="subtitle1" sx={{ mt: 2 }}>
        //                 {field.label}
        //             </Typography>
        //             <TableContainer>
        //                 <Table size="small">
        //                     <TableHead>
        //                         <TableRow>
        //                             {field.fields?.map((subField) => (
        //                                 <TableCell key={subField.name}>
        //                                     {subField.label}
        //                                 </TableCell>
        //                             ))}
        //                         </TableRow>
        //                     </TableHead>
        //                     <TableBody>
        //                         {(formData[field.name!] || []).map(
        //                             (row: any, index: number) => (
        //                                 <TableRow key={index}>
        //                                     {field.fields?.map((subField) => (
        //                                         <TableCell key={subField.name}>
        //                                             <TextField
        //                                                 select={subField.type === "select"}
        //                                                 size="small"
        //                                                 fullWidth
        //                                                 label={subField.label}
        //                                                 value={row[subField.name!] || ""}
        //                                                 disabled={!isEditting}
        //                                                 onChange={(e) =>
        //                                                     handleRepeaterChange(
        //                                                         field.name!,
        //                                                         index,
        //                                                         subField.name!,
        //                                                         e.target.value
        //                                                     )
        //                                                 }
        //                                             >
        //                                                 {subField.options?.map((option) => (
        //                                                     <MenuItem
        //                                                         key={option}
        //                                                         value={option}
        //                                                     >
        //                                                         {option}
        //                                                     </MenuItem>
        //                                                 ))}
        //                                             </TextField>
        //                                         </TableCell>
        //                                     ))}

        //                                     <TableCell>
        //                                         <IconButton
        //                                             color="error"
        //                                             onClick={() =>
        //                                                 removeRepeaterRow(field.name!, index)
        //                                             }
        //                                             disabled={!isEditting}
        //                                         >
        //                                             <DeleteIcon />
        //                                         </IconButton>
        //                                     </TableCell>
        //                                 </TableRow>
        //                             )
        //                         )}
        //                     </TableBody>
        //                 </Table>
        //             </TableContainer>
                    
        //             <Button
        //                 variant="outlined"
        //                 size="small"
        //                 sx={{ mt: 1 }}
        //                 onClick={() => addRepeaterRow(field.name!)}
        //                 disabled={!isEditting}
        //             >
        //                 Add Row
        //             </Button>
        //         </Grid>
        //     );
        // }

        // if(field.type === "repeater_card"){
        //     return (
        //         <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
        //             {field.fields?.map((subField) => (
        //                 <Grid item xs={4}>
        //                     <div style={{ marginBottom: "1rem" }}>
        //                         <Typography variant="body2" gutterBottom>
        //                             {subField.label}
        //                         </Typography>
        //                         <TextField
        //                             select
        //                             size="small"
        //                             fullWidth
        //                             value={tmpSampleType}
        //                             disabled={!isEditting}
        //                             onChange={(e) => setTmpSampleType(e.target.value)}
        //                         >
        //                             {subField.options?.map((option) => (
        //                                 <MenuItem key={option} value={option}>
        //                                     {option}
        //                                 </MenuItem>
        //                             ))}
        //                         </TextField>
        //                     </div>
        //                 </Grid>
        //             ))}
        //             <Grid item>
        //                 <Button
        //                     variant="outlined"
        //                     size="small"
        //                     sx={{ mt: 1 }}
        //                     onClick={() => addRepeaterCardRow(field.name!)}
        //                     disabled={!tmpSampleType}
        //                 >
        //                     Add Sample
        //                 </Button>
        //             </Grid>
        //             {(formData[field.name!] || []).map((row: any, index: number) => (
        //                 <Card key={row.id} sx={{ mt: 2 }}>
        //                     <CardHeader
        //                         title={`Sample ${index + 1}: ${row.type}`}
        //                         action={
        //                             <IconButton
        //                                 onClick={() => removeRepeaterCardRow(field.name!, row.index)}
        //                             >
        //                                 <DeleteIcon/>
        //                             </IconButton>
        //                         }
        //                     />
        //                 </Card>
        //             ))}
        //         </Grid>
        //     )
        // }
    }

    return (
        <form
            onSubmit={(e) => {
                console.log(rows)
                e.preventDefault();
                onSubmit(rows);
            }}
        >
            <TableContainer component={Paper}>
                <Table size="small">
                    <TableBody>
                        {schema.map((field) => {
                            return renderField(field)
                        })}
                    </TableBody>
                </Table>
            </TableContainer>
            <Button
                type="submit"
                variant="contained"
                sx={{mt: 3}}
                disabled={!isEditting}
            >
                Save
            </Button>
        </form>
    )
}

export default QuotationDynamicForm;