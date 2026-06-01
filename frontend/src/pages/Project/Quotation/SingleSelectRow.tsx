import { Autocomplete, TableCell, TableRow, TextField } from "@mui/material";
import { memo, useState } from "react";
import AlertDialog from "../../../components/AlertDialog/AlertDialog";

type Option = { value: string; label: string };

type Props = {
    row: {
        id: string;
        label: string;
        value: any; // string[] | Option[] | string | Option từ data cũ
        options: Option[];
    };
    isEditing: boolean;
    onChange: (id: string, value: string[]) => void;
};

// Normalize data cũ (Option | Option[] | string | string[]) về string
const toSingleString = (value: any): string => {
    if (!value) return "";
    if (Array.isArray(value)) {
        const first = value[0];
        if (!first) return "";
        return typeof first === "string" ? first : (first.label ?? "");
    }
    if (typeof value === "string") return value;
    if (typeof value === "object" && value.label) return value.label;
    return "";
};

const SingleSelectRow = memo(({ row, isEditing, onChange }: Props) => {
    const selected = toSingleString(row.value);
    const selectedOption = row.options.find((o) => o.label === selected) ?? null;

    const [pendingOption, setPendingOption] = useState<Option | null>(null);
    const [confirmOpen, setConfirmOpen] = useState(false);

    const handleChange = (_: any, newValue: Option | null) => {
        if (!newValue || newValue.label === selected) return;
        setPendingOption(newValue);
        setConfirmOpen(true);
    };

    const handleConfirm = () => {
        if (pendingOption) onChange(row.id, [pendingOption.label]);
        setConfirmOpen(false);
        setPendingOption(null);
    };

    const handleCancel = () => {
        setConfirmOpen(false);
        setPendingOption(null);
    };

    return (
        <>
            <TableRow
                sx={{
                    "&:hover": { backgroundColor: "rgba(0, 157, 156, 0.05)" },
                    "&:last-child td": { border: 0 },
                }}
            >
                <TableCell
                    width={300}
                    sx={{ fontWeight: 600, fontSize: "0.8125rem", color: "var(--text-color)" }}
                >
                    {row.label}
                </TableCell>
                <TableCell sx={{ fontSize: "0.8125rem", color: "var(--text-color)" }}>
                    {isEditing ? (
                        <Autocomplete
                            options={row.options}
                            value={selectedOption}
                            onChange={handleChange}
                            getOptionLabel={(option) => option.label}
                            isOptionEqualToValue={(option, value) => option.value === value.value}
                            renderInput={(params) => (
                                <TextField {...params} size="small" placeholder="Select..." />
                            )}
                            sx={{ maxWidth: 320 }}
                        />
                    ) : (
                        <span>{selected || "-"}</span>
                    )}
                </TableCell>
            </TableRow>

            <AlertDialog
                open={confirmOpen}
                title="Thay đổi loại hình dự án"
                message={`Thay đổi loại hình dự án sang "${pendingOption?.label}" sẽ ảnh hưởng đến nội dung quotation.\n\nBạn có chắc chắn muốn thay đổi không?`}
                showConfirmButton={true}
                onClose={handleCancel}
                onConfirm={handleConfirm}
            />
        </>
    );
});

export default SingleSelectRow;
