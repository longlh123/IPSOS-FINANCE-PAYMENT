import { Button, Dialog, DialogActions, DialogContent, DialogContentText, DialogTitle, TextField } from "@mui/material";
import { FC, useEffect, useState } from "react"

interface UniversalInputProps {
    open: boolean,
    onClose: () => void,
    onSubmit: (value: string) => void,
    defaultValue?: "",
    title: string,
    label: string,
    placeholder?: string
}

const UniversalInputDialog: React.FC<UniversalInputProps> = ({open, onClose, onSubmit, defaultValue = "", title, label, placeholder}) => {
    const [ value, setValue ] = useState("");

    useEffect(() => {
        if (open) setValue(defaultValue)
    }, [open, defaultValue]);

    const handleSubmit= () => {
        onSubmit(value);
        setValue("");
    }

    return (
        <Dialog
            open={open}
            onClose={onClose}
            aria-labelledby="alert-dialog-title"
            aria-describedby="alert-dialog-description"
        >
            <DialogTitle  sx={{ width: "500px", marginBottom: "10px" }} id="alert-dialog-title">{title}</DialogTitle>
            <DialogContent>
                <TextField
                    autoFocus
                    label={label}
                    multiline={true}
                    rows={10}
                    fullWidth
                    value={value}
                    placeholder={placeholder}
                    onChange={(e) => setValue(e.target.value)}
                />
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose} color="primary">
                CANCEL
                </Button>
                <Button onClick={handleSubmit} color="primary" autoFocus>
                SUBMIT
                </Button>
            </DialogActions>
        </Dialog>
    );
}

export default UniversalInputDialog;