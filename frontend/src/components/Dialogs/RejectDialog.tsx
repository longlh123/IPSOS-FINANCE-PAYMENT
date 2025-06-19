import { useState } from "react";
import { Button, Dialog, DialogActions, DialogContent, DialogTitle, TextField } from "@mui/material";

interface RejectDialogProps {
    open: boolean;
    onClose: () => void;
    onConfirm: (reason: string) => void;
    title: string;
    message: string;
}

const RejectDialog: React.FC<RejectDialogProps> = ({
    open,
    onClose,
    onConfirm,
    title,
    message,
  }) => {

    const [rejectReason, setRejectReason] = useState<string>('');
  
    const handleConfirm = () => {
        if (rejectReason.trim()) {
            onConfirm(rejectReason);
        }
        setRejectReason(''); // Clear the input after confirmation
        onClose(); // Close the dialog
    };
  
    return (
        <Dialog
            open={open}
            onClose={onClose}
            aria-labelledby="alert-dialog-title"
            aria-describedby="alert-dialog-description"
            fullWidth={true}
            maxWidth='sm'
        >
            <DialogTitle id="alert-dialog-title">{title}</DialogTitle>
            <DialogContent>
                <TextField
                    placeholder={message}
                    multiline
                    rows={4}
                    value={rejectReason}
                    onChange={(e) => setRejectReason(e.target.value)}
                    fullWidth
                    style={{ paddingTop: '16px' }}
                />
            </DialogContent>
            <DialogActions>
            <Button onClick={onClose} color="primary">
                HUỶ
            </Button>
            <Button onClick={handleConfirm} color="primary" autoFocus>
                GỬI LÝ DO
            </Button>
            </DialogActions>
        </Dialog>
    );
  };
  
  export default RejectDialog;