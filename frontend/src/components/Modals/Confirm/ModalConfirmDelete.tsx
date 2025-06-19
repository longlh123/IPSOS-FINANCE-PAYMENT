import { Modal, Box, Button } from "@mui/material";

interface ModalProps {
  openModal: boolean;
  onClose: () => void;
}
const ModalConfirmDelete: React.FC<ModalProps> = ({ openModal, onClose }) => {
  return (
    <>
      <Modal open={openModal} onClose={onClose}>
        <Box className="modal-box-confirm" sx={{ textAlign: "center" }}>
          <h3>Are you sure you want delete this project?</h3>
          <Box className="btn-modal-footer" sx={{ margin: "25px 0 20px 0" }}>
            <Button className="btn-modal-cancel" onClick={onClose}>
              Cancel
            </Button>
            <Button className="btn-modal-submit" variant="contained">
              OK
            </Button>
          </Box>
        </Box>
      </Modal>
    </>
  );
};

export default ModalConfirmDelete;
