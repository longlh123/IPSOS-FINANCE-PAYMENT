import EditableRow from "../pages/Project/Quotation/EditableRow";
import GroupRepeaterRow from "../pages/Project/Quotation/GroupRepeaterRow";
import MultiSelectRow from "../pages/Project/Quotation/MultiSelectRow";
import RadioRow from "../pages/Project/Quotation/RadioRow";
import RepeaterRow from "../pages/Project/Quotation/RepeaterRow";
import RichTextRow from "../pages/Project/Quotation/RichTextRow";
import SectionRow from "../pages/Project/Quotation/SectionRow";
import SingleSelectRow from "../pages/Project/Quotation/SingleSelectRow";
import { FeedbacksMap } from "../config/QuotationConfig";

export interface LayoutSchema {
    xs: number;
    sm: number;
    md: number;
}

export interface FieldSchema {
    name: string;
    label: string;
    placeholder: string;
    type: string;
    required?: boolean;
    default?: string | number;
    layout?: LayoutSchema;
    hidden: boolean;
    disabled?: boolean;
    rule?: string;
    options?: { value: string | number; label: string; parent?: string | number }[];
    fields?: FieldSchema[];
    confirmMessage?: string;
}

export interface DynamicFormProps {
    schema: FieldSchema[];
    onSubmit: (data: any) => void;
    initialData?: any;
    isEditting: boolean;
    onProjectTypeChange?: (projectType: string) => void;
    feedbacks?: FeedbacksMap;
    onFeedbackSave?: (section: string, content: string) => void | Promise<any>;
    onFeedbackResponse?: (section: string, status: 'resolved' | 'rejected', content: string) => void | Promise<any>;
}

interface Props {
    field: FieldSchema;
    rows: any;
    isEditting: boolean;
    editingId: string | null;
    setEditingId: (id: string | null) => void;
    updateRow: (id: string, value: any) => void;
    feedbacks?: FeedbacksMap;
    onFeedbackSave?: (section: string, content: string) => void | Promise<any>;
    onFeedbackResponse?: (section: string, status: 'resolved' | 'rejected', content: string) => void | Promise<any>;
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
    switch (field.type) {
        case 'text':
        case 'number':
            return (
                <EditableRow
                    key={field.name}
                    row={{
                        id: field.name,
                        label: field.label,
                        type: field.type as 'text' | 'number',
                        value: rows[field.name],
                        ...(field.rule ? { rule: field.rule as any } : {}),
                    }}
                    isEditing={field.disabled ? false : isEditting}
                    isActive={editingId === field.name}
                    onStartEdit={() => setEditingId(field.name)}
                    onStopEdit={() => setEditingId(field.name)}
                    onChange={updateRow}
                />
            );

        case 'textarea':
            return (
                <RichTextRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name] }}
                    isEditing={isEditting}
                    isActive={editingId === field.name}
                    onStartEdit={() => setEditingId(field.name)}
                    onStopEdit={() => setEditingId(field.name)}
                    onChange={updateRow}
                />
            );

        case 'radio':
            return (
                <RadioRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], options: field.options ?? [] }}
                    isEditing={isEditting}
                    onChange={updateRow}
                />
            );

        case 'single-select':
            return (
                <SingleSelectRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], options: field.options ?? [], placeholder: field.placeholder }}
                    isEditing={isEditting}
                    onChange={updateRow}
                    confirmMessage={field.confirmMessage}
                />
            );

        case 'multi-select':
            return (
                <MultiSelectRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], options: field.options ?? [], placeholder: field.placeholder }}
                    isEditing={isEditting}
                    onChange={updateRow}
                />
            );

        case 'repeater':
            return (
                <RepeaterRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], fields: field.fields ?? [] }}
                    isEditing={isEditting}
                    onChange={updateRow}
                    feedbackThread={feedbacks?.[field.name]}
                    onFeedbackSave={onFeedbackSave ? (content) => onFeedbackSave(field.name, content) : undefined}
                    onFeedbackResponse={onFeedbackResponse ? (status, content) => onFeedbackResponse(field.name, status, content) : undefined}
                />
            );

        case 'section':
            return (
                <SectionRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], fields: field.fields ?? [] }}
                    isEditing={isEditting}
                    onChange={updateRow}
                    feedbackThread={feedbacks?.[field.name]}
                    onFeedbackSave={onFeedbackSave ? (content) => onFeedbackSave(field.name, content) : undefined}
                    onFeedbackResponse={onFeedbackResponse ? (status, content) => onFeedbackResponse(field.name, status, content) : undefined}
                />
            );

        case 'group-repeater':
            return (
                <GroupRepeaterRow
                    key={field.name}
                    row={{ id: field.name, label: field.label, value: rows[field.name], fields: field.fields ?? [] }}
                    isEditing={isEditting}
                    onChange={updateRow}
                />
            );

        default:
            return null;
    }
}
