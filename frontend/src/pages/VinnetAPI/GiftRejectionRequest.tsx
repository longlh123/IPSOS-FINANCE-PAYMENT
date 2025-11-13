import { useState } from "react";
import GuestLayout from "../../Layouts/GuestLayout";
import SendIcon from '@mui/icons-material/Send';
import { ApiConfig } from '../../config/ApiConfig';
import { Alert, Card, CardContent, Divider, TextField, Typography } from "@mui/material";
import LoadingButton from '@mui/lab/LoadingButton';
import axios from "axios";
import { useNavigate, useParams } from 'react-router-dom';

const GiftRejectionRequest = () => {
    const navigate = useNavigate();

    const [ rejectReason, setRejectReason ] = useState('');
    const [ loading, setLoading ] = useState(false);
    const [ isError, setIsError ] = useState(false);
    const [ errorMessage, setErrorMessage] = useState('');

    const { url } = useParams<{
        url?: string
    }>();

    const handleConfirm = async () => {
        try{
            setLoading(true);
            setIsError(false);
            setErrorMessage('');

            console.log('Rejection reason: ', rejectReason);

            const request = await axios.post(ApiConfig.vinnet.rejectTransaction, {
                url: url,
                reject_message: rejectReason
            }, {
                headers : {
                    'Content-Type': 'application/json'
                }
            });

            console.log(request.data);
            navigate('/page200?message=' + request.data.message);
        } catch (error){
            let errorMessage = '';

            if (axios.isAxiosError(error)) {
                errorMessage = error.response?.data.message ?? error.message;
            } else {
                errorMessage = (error as Error).message;
            }

            setIsError(true);
            setErrorMessage(errorMessage);
            console.error('Error:', errorMessage);
        } finally {
            setLoading(false);
        }
    }

    return (
        <GuestLayout>
            <Card className="welcome-container">
                <CardContent className='welcome-header'>
                    
                </CardContent>
                <CardContent className='welcome-body'>
                    <div className='item'>
                        <Typography variant="body1">
                            Chúng tôi rất tiếc khi bạn không muốn nhận quà từ chương trình.
                        </Typography>
                    </div>
                    <div className='item'>
                        <Typography variant="body2">
                            Vui lòng chia sẻ lý do để chúng tôi có thể cải thiện trải nghiệm cho những lần sau.
                        </Typography>
                    </div>
                    {isError && (
                        <div className='item'>
                            <Alert severity="error" className="message-invalid">{errorMessage}</Alert> 
                        </div>
                    )}
                    <div className='item'>
                        <TextField
                            label="Lý do bạn từ chối"
                            multiline
                            rows={4}
                            value={rejectReason}
                            onChange={(e) => setRejectReason(e.target.value)}
                            fullWidth
                            variant="outlined"
                            disabled={loading}
                        />
                    </div>
                </CardContent>
                <Divider/>
                <CardContent className='welcome-footer'>
                    <LoadingButton
                        onClick={handleConfirm}
                        size="large"
                        endIcon={<SendIcon />}
                        loading={loading}
                        loadingPosition="end"
                        variant="contained"
                        disabled={!rejectReason.trim()}
                        className='btn bg-vinnet-primary'
                        >
                            <span>GỬI LÝ DO</span>
                    </LoadingButton>
                </CardContent>
            </Card>
        </GuestLayout>
    )
}

export default GiftRejectionRequest;