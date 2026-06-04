import { useState } from "react";
import EditableRow from "../pages/Project/Quotation/EditableRow";
import MultiSelectRow from "../pages/Project/Quotation/MultiSelectRow";
import RadioRow from "../pages/Project/Quotation/RadioRow";
import RepeaterRow from "../pages/Project/Quotation/RepeaterRow";
import RichTextRow from "../pages/Project/Quotation/RichTextRow";
import SectionRow from "../pages/Project/Quotation/SectionRow";
import SingleSelectRow from "../pages/Project/Quotation/SingleSelectRow";
import { FeedbacksMap, FeedbackThread } from "../config/QuotationConfig";

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
    options?: {value: string | number, label: string, parent?: string | number}[];
    fields?: FieldSchema[];
    confirmMessage?: string;
}

export interface DynamicFormProps {
    schema: FieldSchema[];
    onSubmit: (data: any) => void;
    initialData?: any,
    isEditting: boolean,
    onProjectTypeChange?: (projectType: string) => void,
    feedbacks?: FeedbacksMap,
    onFeedbackSave?: (section: string, content: string) => void | Promise<any>,
    onFeedbackResponse?: (section: string, status: 'resolved' | 'rejected', content: string) => void | Promise<any>,
}

interface Props {
    field: FieldSchema,
    rows: any,
    isEditting: boolean,
    editingId: string | null,
    setEditingId: (id: string | null) => void,
    updateRow: (id: string, value: any) => void,
    feedbacks?: FeedbacksMap,
    onFeedbackSave?: (section: string, content: string) => void | Promise<any>,
    onFeedbackResponse?: (section: string, status: 'resolved' | 'rejected', content: string) => void | Promise<any>,
}

export function renderField({
    field,
    rows,
    isEditting,
    editingId,
    setEditingId,
    updateRow,
    feedbacks,
    onFeedbackSave,
    onFeedbackResponse,
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

    if(field.type === 'single-select'){
        const confirmMessage = field.confirmMessage || (field.name === 'project_types' ? "Changing project type may affect other fields. Are you sure you want to change?" : undefined);
            
        return (
            <SingleSelectRow
                key={field.name}
                row={{ id: field.name, label: field.label, value: rows[field.name], options: field.options ?? [] }}
                isEditing={isEditting}
                onChange={updateRow}
                confirmMessage={confirmMessage}
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
                feedbackThread={feedbacks?.[field.name]}
                onFeedbackSave={onFeedbackSave ? (content: string) => onFeedbackSave(field.name, content) : undefined}
                onFeedbackResponse={onFeedbackResponse ? (status: 'resolved' | 'rejected', content: string) => onFeedbackResponse(field.name, status, content) : undefined}
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
                feedbackThread={feedbacks?.[field.name]}
                onFeedbackSave={onFeedbackSave ? (content: string) => onFeedbackSave(field.name, content) : undefined}
                onFeedbackResponse={onFeedbackResponse ? (status: 'resolved' | 'rejected', content: string) => onFeedbackResponse(field.name, status, content) : undefined}
            />
        )
    }
}