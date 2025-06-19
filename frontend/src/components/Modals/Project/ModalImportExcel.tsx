import { useState } from "react";
import axios from "axios";
import "../../../assets/css/modal.css";
import {
    Box,
    Button,
    Divider,
    FormControl,
    Grid,
    InputLabel,
    MenuItem,
    Modal,
    Select,
    TextField,
    Typography,
} from "@mui/material";



interface ModalProps {
    openModal: boolean,
    onClose: () => void;
    project: any
}

const ModalImportExcel: React.FC<ModalProps> = ({
    openModal,
    onClose,
    project
}) => {
    const [file, setFile] = useState<File | null>(null)

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        if(event.target.files) {
            setFile(event.target.files[0]);
        }
    }

    const handleFileUpload = async () => {
        if(!file){
            alert('Please select a file first.');
            return;
        }

        try{
            const data = new FormData();
            data.append('file', file);

            const response = await axios.post('http://127.0.0.1:5000', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            });
            
            console.log('File uploaded successfully:', response.data);
        } catch(error) {
            console.error('Error uploading file: ', error);
        }
    }

    return (
        <Modal
            open = {openModal}
            onClose={onClose}
            aria-labelledby="modal-modal-title"
            aria-describedby="modal-modal-description"
        >
            <Box className="modal-box">
                <Typography
                id="modal-modal-title"
                variant="h6"
                component="h2"
                textAlign="center"
                >
                    Import Excel
                </Typography>
                <Divider />
                <Grid container rowGap={3} columnSpacing={3} className="content-modal">
                    <Grid item xs={12}>
                        <input type="file" accept=".xlsx, .xls" onChange={handleFileChange} />
                    </Grid>
                </Grid>
                <Box className="btn-modal-footer" textAlign="end">
                    <Button className="btn-modal-cancel" onClick={onClose}>
                        CANCEL
                    </Button>
                    <Button
                        className="btn-modal-submit"
                        variant="contained"
                        onClick={handleFileUpload}
                    >
                        IMPORT
                    </Button>
                </Box>
            </Box>
        </Modal>
    )

};

export default ModalImportExcel;