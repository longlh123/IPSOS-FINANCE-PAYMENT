import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
} from "@mui/material";

interface AlertDialogProps {
  open: boolean;
  title: string;
  message: string;
  showConfirmButton: boolean;
  onClose: () => void;
  onConfirm?: () => void;
}

const AlertDialog: React.FC<AlertDialogProps> = ({
  open,
  title,
  message,
  showConfirmButton,
  onClose,
  onConfirm
}) => {
  return (
    <>
      <Dialog
        open={open}
        onClose={onClose}
        aria-labelledby="alert-dialog-title"
        aria-describedby="alert-dialog-description"
      >
        <DialogTitle sx={{ width: "500px" }} id="alert-dialog-title">
          {title}
        </DialogTitle>
        <DialogContent>
          <DialogContentText id="alert-dialog-description">
            {message}
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          {showConfirmButton ? (
            <>
              <Button onClick={onClose}>CANCEL</Button>
              <Button onClick={onConfirm} color="primary" autoFocus>CONFIRM</Button>
            </>
          ) : (
            <>
              <Button onClick={onClose}>CLOSE</Button>
            </>
          )}
        </DialogActions>
      </Dialog>
    </>
  );
};

export default AlertDialog;
