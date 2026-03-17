import { Accordion, AccordionDetails, AccordionSummary, Autocomplete, Box, Button, Card, CardContent, CardHeader, Checkbox, FormControl, FormControlLabel, FormLabel, Grid, IconButton, MenuItem, Paper, Radio, RadioGroup, Select, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, TextField, Typography } from "@mui/material";
import { useEffect, useState } from "react";
import DeleteIcon from "@mui/icons-material/Delete";
import { ProjectData } from "../../../config/ProjectFieldsConfig";
import ArrowDownwardIcon from '@mui/icons-material/ArrowDownward';
import ArrowDropDownIcon from '@mui/icons-material/ArrowDropDown';

interface LayoutSchema {
    xs: number,
    sm: number,
    md: number
}

interface FieldSchema {
    name?: string;
    label?: string;
    type: string;
    required?: boolean;
    default?: string | number;
    layout?: LayoutSchema,
    options?: string[];
    fields?: FieldSchema[];
}

interface GroupSchema {
    type: string;
    key: string;
    title: string;
    collapsibel: boolean;
    default_open: boolean;
    fields: FieldSchema[];
}

interface DynamicFormProps {
    schema: GroupSchema[];
    onSubmit: (data: any) => void;
    projectData: ProjectData | null,
    initialQuotationData?: any,
    isEditting?: boolean,
}

const QuotationDynamicForm: React.FC<DynamicFormProps> = ({ schema, onSubmit, projectData, initialQuotationData, isEditting }) => {

    const [ formData, setFormData ] = useState<any>({}); 

    useEffect(() => {
        if(initialQuotationData) {
            setFormData(initialQuotationData);
        }
    }, [initialQuotationData]);

    const handleChange = (name: string, value: any) => {
        setFormData((prev:any) => ({
            ...prev,
            [name]: value
        }));
    }

    const handleRepeaterChange = (
        parent: string,
        index: number,
        field: string,
        value: any
    ) => {
        const updated = [...(formData[parent] || [])];
        updated[index] = { ...updated[index], [field]: value };

        setFormData((prev: any) => ({
        ...prev,
        [parent]: updated
        }));
    };

    const addRepeaterRow = (name: string) => {
        setFormData((prev: any) => ({
        ...prev,
        [name]: [...(prev[name] || []), {}]
        }));
    };

    const removeRepeaterRow = (name: string, index: number) => {
        const updated = [...formData[name]];
        updated.splice(index, 1);

        setFormData((prev: any) => ({
        ...prev,
        [name]: updated
        }));
    };

    const addRepeaterCardRow = (name: string) => {
        const newRow = {
            id: Date.now(),
            type: tmpSampleType
        };

        setFormData((prev: any) => ({
            ...prev,
            [name]: [...(prev[name] || []), newRow]
        }))

        setTmpSampleType("");
    }

    const removeRepeaterCardRow = (name: string, index: number) => {
        const updated = [...formData[name]];
        updated.splice(index, 1);

        setFormData((prev: any) => ({
        ...prev,
        [name]: updated
        }));
    }

    const [ tmpSampleType, setTmpSampleType ] = useState<string | null>(null);

    const renderField = (field: FieldSchema) => {
        if(field.type === 'title'){
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.label}>
                    <Typography variant="h6" sx={{mt: 2}}>
                        {field.label}
                    </Typography>
                </Grid>
            );
        }

        if(field.type === 'text' || field.type === 'number'){
            let value = field.name == 'internal_code' ? projectData?.internal_code : 
                        field.name == 'project_name' ? projectData?.project_name : formData[field.name!];

            let disabled = field.name == 'internal_code' ? true : !isEditting;
            
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            {field.label}
                        </Typography>
                        <TextField
                            fullWidth
                            size="small"
                            type={field.type}
                            value={value}
                            required={field.required}
                            variant="outlined"
                            disabled={disabled}
                            onChange={(e) => handleChange(field.name!, e.target.value)}
                        />
                    </div>
                </Grid>
            )
        }

        if(field.type === 'textarea'){
            let value = field.name == 'project_objectives' ? projectData?.project_objectives : formData[field.name!];

            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            {field.label}
                        </Typography>
                        <TextField
                            fullWidth
                            size="small"
                            value={value}
                            variant="outlined"
                            multiline
                            rows={4}
                            disabled={!isEditting}
                            onChange={(e) => handleChange(field.name!, e.target.value)}
                        />
                    </div>
                </Grid>
            )
        }

        if(field.type === 'select'){
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            {field.label}
                        </Typography>
                        <TextField
                            select
                            size="small"
                            fullWidth
                            value={formData[field.name!] || ""}
                            disabled={!isEditting}
                            onChange={(e) => handleChange(field.name!, e.target.value)}
                        >
                            {field.options?.map((option) => (
                                <MenuItem key={option} value={option}>
                                    {option}
                                </MenuItem>
                            ))}
                        </TextField>
                    </div>
                </Grid>
            )
        }

        if(field.type === 'multi-select'){
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            {field.label}
                        </Typography>
                        <Autocomplete
                            multiple
                            disableCloseOnSelect
                            options={field.options || []}
                            value={formData[field.name!] || []}
                            disabled={!isEditting}
                            onChange={(event, newValue) => handleChange(field.name!, newValue)}
                            getOptionLabel={(option) => option}
                            renderOption={(props, option, { selected }) => (
                                <li {...props}>
                                    <Checkbox
                                        style={{ marginRight: 8 }}
                                        checked={selected}
                                    />
                                    {option}
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
                    </div>
                </Grid>
            )
        }

        if(field.type === 'radio'){
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    <div style={{ marginBottom: "1rem" }}>
                        <FormControl required={field.required} disabled={!isEditting}>
                            <FormLabel>
                                {field.label}
                            </FormLabel>
                            <RadioGroup
                                row
                                value={formData[field.name!] || ""}
                                onChange={(e) => {
                                    handleChange(field.name!, e.target.value)
                                }}
                            >
                                {field.options?.map((option) => (
                                    <FormControlLabel
                                        key={option}
                                        value={option}
                                        control={<Radio/>}
                                        label={option}
                                    />
                                ))}
                            </RadioGroup>
                        </FormControl>
                    </div>
                </Grid>
            )
        }

        if(field.type === 'range'){
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            {field.label}
                        </Typography>
                        <Grid container spacing={2}>
                            {field.fields?.map((f) => {
                                const value = formData[field.name!]?.[f.name!] ?? "";

                                return (
                                    <Grid item xs={6} key={f.name}>
                                        <TextField
                                            fullWidth
                                            size="small"
                                            type={f.type}
                                            label={f.label}
                                            value={value}
                                            required={f.required}
                                            disabled={!isEditting}
                                            onChange={(e) => handleChange(field.name!, {
                                                ...formData[field.name!], 
                                                [f.name!]: e.target.value
                                            })}
                                        />
                                    </Grid>
                                )
                            })}
                        </Grid>
                    </div>
                </Grid>
            )
        }

        if (field.type === "repeater") {
            return (
                <Grid item xs={12} key={field.name}>
                    <Typography variant="subtitle1" sx={{ mt: 2 }}>
                        {field.label}
                    </Typography>
                    <TableContainer>
                        <Table size="small">
                            <TableHead>
                                <TableRow>
                                    {field.fields?.map((subField) => (
                                        <TableCell key={subField.name}>
                                            {subField.label}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {(formData[field.name!] || []).map(
                                    (row: any, index: number) => (
                                        <TableRow key={index}>
                                            {field.fields?.map((subField) => (
                                                <TableCell key={subField.name}>
                                                    <TextField
                                                        select={subField.type === "select"}
                                                        size="small"
                                                        fullWidth
                                                        label={subField.label}
                                                        value={row[subField.name!] || ""}
                                                        disabled={!isEditting}
                                                        onChange={(e) =>
                                                            handleRepeaterChange(
                                                                field.name!,
                                                                index,
                                                                subField.name!,
                                                                e.target.value
                                                            )
                                                        }
                                                    >
                                                        {subField.options?.map((option) => (
                                                            <MenuItem
                                                                key={option}
                                                                value={option}
                                                            >
                                                                {option}
                                                            </MenuItem>
                                                        ))}
                                                    </TextField>
                                                </TableCell>
                                            ))}

                                            <TableCell>
                                                <IconButton
                                                    color="error"
                                                    onClick={() =>
                                                        removeRepeaterRow(field.name!, index)
                                                    }
                                                    disabled={!isEditting}
                                                >
                                                    <DeleteIcon />
                                                </IconButton>
                                            </TableCell>
                                        </TableRow>
                                    )
                                )}
                            </TableBody>
                        </Table>
                    </TableContainer>
                    
                    <Button
                        variant="outlined"
                        size="small"
                        sx={{ mt: 1 }}
                        onClick={() => addRepeaterRow(field.name!)}
                        disabled={!isEditting}
                    >
                        Add Row
                    </Button>
                </Grid>
            );
        }

        if(field.type === "repeater_card"){
            return (
                <Grid item xs={field.layout?.xs} sm={field.layout?.sm} md={field.layout?.md} key={field.name}>
                    {field.fields?.map((subField) => (
                        <Grid item xs={4}>
                            <div style={{ marginBottom: "1rem" }}>
                                <Typography variant="body2" gutterBottom>
                                    {subField.label}
                                </Typography>
                                <TextField
                                    select
                                    size="small"
                                    fullWidth
                                    value={tmpSampleType}
                                    disabled={!isEditting}
                                    onChange={(e) => setTmpSampleType(e.target.value)}
                                >
                                    {subField.options?.map((option) => (
                                        <MenuItem key={option} value={option}>
                                            {option}
                                        </MenuItem>
                                    ))}
                                </TextField>
                            </div>
                        </Grid>
                    ))}
                    <Grid item>
                        <Button
                            variant="outlined"
                            size="small"
                            sx={{ mt: 1 }}
                            onClick={() => addRepeaterCardRow(field.name!)}
                            disabled={!tmpSampleType}
                        >
                            Add Sample
                        </Button>
                    </Grid>
                    {(formData[field.name!] || []).map((row: any, index: number) => (
                        <Card key={row.id} sx={{ mt: 2 }}>
                            <CardHeader
                                title={`Sample ${index + 1}: ${row.type}`}
                                action={
                                    <IconButton
                                        onClick={() => removeRepeaterCardRow(field.name!, row.index)}
                                    >
                                        <DeleteIcon/>
                                    </IconButton>
                                }
                            />
                        </Card>
                    ))}
                </Grid>
            )
        }
    }

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit(formData);
            }}
        >
            <Grid container spacing={2}>
                {schema.map((field) => {
                    if(field.type === 'group'){
                        return (
                            <Grid item xs={12} key={field.key}>
                                <Accordion key={field.type} sx={{ width: "100%"}} defaultExpanded={field.default_open}>
                                    <AccordionSummary
                                        expandIcon={<ArrowDownwardIcon />}
                                    >
                                        <Typography component="span">{field.title}</Typography>
                                    </AccordionSummary>
                                    <AccordionDetails>
                                        <Grid container spacing={2}>
                                            {field.fields?.map(renderField)}
                                        </Grid>
                                    </AccordionDetails>
                                </Accordion>
                            </Grid>
                        )
                    }

                    return renderField(field)
                })}
            </Grid>
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