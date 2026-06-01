import { useState } from "react";
import EditableRow from "../pages/Project/Quotation/EditableRow";
import MultiSelectRow from "../pages/Project/Quotation/MultiSelectRow";
import RadioRow from "../pages/Project/Quotation/RadioRow";
import RepeaterRow from "../pages/Project/Quotation/RepeaterRow";
import RichTextRow from "../pages/Project/Quotation/RichTextRow";
import SectionRow from "../pages/Project/Quotation/SectionRow";
import SingleSelectRow from "../pages/Project/Quotation/SingleSelectRow";

export interface LayoutSchema {
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
    hidden: boolean,
    options?: {value: string, label: string}[];
    fields?: FieldSchema[];
}

export interface DynamicFormProps {
    schema: FieldSchema[];
    onSubmit: (data: any) => void;
    initialData?: any,
    isEditting: boolean,
    onProjectTypeChange?: (projectType: string) => void,
}

interface Props {
    field: FieldSchema,
    rows: any,
    isEditting: boolean,
    editingId: string | null,
    setEditingId: (id: string | null) => void,
    updateRow: (id: string, value: any) => void
}

export function renderField({
    field,
    rows,
    isEditting,
    editingId,
    setEditingId,
    updateRow
}: Props) {

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
        if(field.name === 'project_types'){
            return (
                <SingleSelectRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], options: field.options ?? [] }}
                    isEditing={isEditting}
                    onChange={updateRow}
                />
            )
        }
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

    if(field.type === 'section'){
        return (
            <SectionRow
                key={field.name}
                row={{id: field.name, label: field.label, value: rows[field.name], fields: field.fields ?? []}}
                isEditing={isEditting}
                onChange={updateRow}
            />
        )
    }
}