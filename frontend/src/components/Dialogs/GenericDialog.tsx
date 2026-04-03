import { Button, Dialog, DialogActions, DialogContent, DialogTitle } from "@mui/material";

interface GenericDialogProps {
    open: boolean,
    title: string,
    children: React.ReactNode,
    onClose: () => void,
    onSubmit: () => void,
    submitText?: string,
    cancelText?: string
}

const GenericDialog: React.FC<GenericDialogProps> = ({ open, title, children, onClose, onSubmit, submitText = "SUBMIT", cancelText = "CANCEL" }) => {

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
            <DialogTitle>{title}</DialogTitle>
            <DialogContent>
                {children}
            </DialogContent>
            <DialogActions>
                <Button onClick={onClose}>{cancelText}</Button>
                <Button onClick={onSubmit} color="primary" autoFocus>{submitText}</Button>
            </DialogActions>
        </Dialog>
    )
}

export default GenericDialog;