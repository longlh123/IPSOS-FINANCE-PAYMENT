import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
} from "@mui/material";

interface AlertDialogProps {
  openDialog: boolean;
  closeDialog: () => void;
  textHeader: string;
  textContent: string;
}
const AlertDialog: React.FC<AlertDialogProps> = ({
  openDialog,
  closeDialog,
  textHeader,
  textContent,
}) => {
  return (
    <>
      <Dialog
        open={openDialog}
        onClose={closeDialog}
        aria-labelledby="alert-dialog-title"
        aria-describedby="alert-dialog-description"
      >
        <DialogTitle sx={{ width: "500px" }} id="alert-dialog-title">
          {textHeader}
        </DialogTitle>
        <DialogContent>
          <DialogContentText id="alert-dialog-description">
            {textContent}
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={closeDialog}>Agree</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default AlertDialog;
