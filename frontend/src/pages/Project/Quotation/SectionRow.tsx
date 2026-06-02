import { memo, useEffect, useState } from "react";
import { Box, FormControl, FormControlLabel, Paper, Radio, RadioGroup, Table, TableBody, TableCell, TableContainer, TableRow } from "@mui/material";
import RadioRow from "./RadioRow";
import RangeRow from "./RangeRow";
import MultiSelectRow from "./MultiSelectRow";
import SingleSelectRow from "./SingleSelectRow";
import RichTextRow from "./RichTextRow";
import EditableRow from "./EditableRow";
import { FieldSchema } from "../../../utils/renderFields";

export interface SectionRowData {
    [key: string]: any
}

type Props = {
    row: {
        id: string,
        label: string,
        value: SectionRowData,
        fields: FieldSchema[]
    };
    isEditing: boolean,
    onChange: (id: string, value: SectionRowData) => void;
}

const SectionRow = memo(({row, isEditing, onChange}: Props) => {
    const [ draft, setDraft ] = useState<SectionRowData>(row.value || {});

    const handleFieldChange = (fieldName: string, value: any) => {
        let newDraft = {
            ...draft,
            [fieldName]: value
        };

        // Clear dependent fields when parent changes (applies to both single-select and multi-select)
        if (fieldName === 'industry') {
            newDraft = { ...newDraft, category: undefined, subcategory: undefined };
        } else if (fieldName === 'category') {
            newDraft = { ...newDraft, subcategory: undefined };
        }

        setDraft(newDraft);

        onChange(row.id, newDraft);
    }

    useEffect(() => {
        setDraft(row.value || {});
    }, [row.value]);

    const [ editingId, setEditingId ] = useState<string | null>(null);

    const renderField = (field: FieldSchema) => {
        if(field.type === 'number'){

            const rule = field.name === 'project_name' ? "uppercaseNoSpecial" : (field.name === 'internal_code' ? "maskXXXX_XXXX" : undefined);
            
            return (
                <EditableRow
                    key={field.name}
                    row={{
                        id: field.name, 
                        label: field.label, 
                        type: field.type,
                        value: draft[field.name], 
                        ...(rule ? { rule } : {})
                    }}
                    isEditing={isEditing}
                    isActive={editingId === field.name}
                    onStartEdit={() => setEditingId(field.name)}
                    onStopEdit={() => setEditingId(field.name)}
                    onChange={handleFieldChange}
                />
            )
        }
        if(field.type === 'textarea'){
            const placeholder = field.name.startsWith('qc') ? "Theo stardard" : "-";

            return (
                <RichTextRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: draft[field.name]}}
                    isEditing={isEditing}
                    isActive={editingId === field.name}
                    placeholder={placeholder}
                    onStartEdit={() => setEditingId(field.name)}
                    onStopEdit={() => setEditingId(field.name)}
                    onChange={handleFieldChange}
                />
            )
        }
        if(field.type === 'radio'){
            return (
                <RadioRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: draft[field.name], options: field.options ?? []}}
                    isEditing={isEditing}
                    onChange={handleFieldChange}
                />
            )
        }
        if(field.type === 'range'){
            return (
                <RangeRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: draft[field.name]}}
                    isEditing={isEditing}
                    onChange={handleFieldChange}
                />
            )
        }
        if(field.type === 'multi-select'){
            let disabled = false;
            let options = field.options ?? [];

            if(field.name === 'category'){
                disabled = !isEditing || !draft['industry'];

                // industry có thể là single-select (string) hoặc multi-select (Option object)
                const rawIndustry = Array.isArray(draft['industry']) ? draft['industry'][0] : draft['industry'];
                const industryOption = typeof rawIndustry === 'object' && rawIndustry !== null
                    ? rawIndustry
                    : row.fields.find(f => f.name === 'industry')?.options?.find(o => o.label === rawIndustry);
                if (industryOption) {
                    options = options.filter(o => String(o.parent) === String(industryOption.value));
                }
            }
            if(field.name === 'subcategory'){
                disabled = !isEditing || !draft['industry'] || !draft['category'];

                // category có thể là single-select (string) hoặc multi-select (Option object)
                const rawCategory = Array.isArray(draft['category']) ? draft['category'][0] : draft['category'];
                const categoryOption = typeof rawCategory === 'object' && rawCategory !== null
                    ? rawCategory
                    : row.fields.find(f => f.name === 'category')?.options?.find(o => o.label === rawCategory);
                if (categoryOption) {
                    options = options.filter(o => String(o.parent) === String(categoryOption.value));
                }
            }

            return (
                <MultiSelectRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: draft[field.name], options: options ?? []}}
                    isEditing={isEditing}
                    isDisabled={disabled}
                    onChange={handleFieldChange}
                />
            )
        }
        if(field.type === 'single-select'){
            let disabled = false;
            let options = field.options ?? [];

            if(field.name === 'category'){
                disabled = !isEditing || !draft['industry'];

                // industry có thể là single-select (string) hoặc multi-select (Option object)
                const rawIndustry = Array.isArray(draft['industry']) ? draft['industry'][0] : draft['industry'];
                const industryOption = typeof rawIndustry === 'object' && rawIndustry !== null
                    ? rawIndustry
                    : row.fields.find(f => f.name === 'industry')?.options?.find(o => o.label === rawIndustry);
                if (industryOption) {
                    options = options.filter(o => String(o.parent) === String(industryOption.value));
                }
            }
            if(field.name === 'subcategory'){
                disabled = !isEditing || !draft['industry'] || !draft['category'];

                // category có thể là single-select (string) hoặc multi-select (Option object)
                const rawCategory = Array.isArray(draft['category']) ? draft['category'][0] : draft['category'];
                const categoryOption = typeof rawCategory === 'object' && rawCategory !== null
                    ? rawCategory
                    : row.fields.find(f => f.name === 'category')?.options?.find(o => o.label === rawCategory);
                if (categoryOption) {
                    options = options.filter(o => String(o.parent) === String(categoryOption.value));
                }
            }

            return (
                <SingleSelectRow
                    key={field.name}
                    row={{id: field.name, label: field.label, value: draft[field.name], options}}
                    isEditing={isEditing}
                    isDisabled={disabled}
                    onChange={handleFieldChange}
                    confirmMessage={field.confirmMessage || undefined}
                />
            )
        }

    }

    const renderMiniTable = (fields: FieldSchema[]) => {
        return (
            <TableContainer component={Paper} variant="outlined">
                <Table size="small">
                    <TableBody>
                        {fields
                            .filter((subField) => !subField.hidden)
                            .map((subField) => (
                            renderField(subField)
                        ))}
                    </TableBody>
                </Table>
            </TableContainer>
        )
    }

    return (
        <TableRow
            sx={{
                "&:hover": { backgroundColor: "rgba(0, 157, 156, 0.05)" },
                "&:last-child td": { border: 0 },
            }}
        >
            <TableCell
                width={400}
                sx={{ fontWeight: 600, fontSize: "0.8125rem", color: "var(--text-color)" }}
            >
                {row.label}
            </TableCell>
            <TableCell sx={{ fontSize: "0.8125rem", color: "var(--text-color)" }}>
                { renderMiniTable(row.fields) }
            </TableCell>
        </TableRow>
    )
});

export default SectionRow;