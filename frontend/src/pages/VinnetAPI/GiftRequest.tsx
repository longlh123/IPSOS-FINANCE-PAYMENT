import { Alert, Box, Card, CardContent, Divider, Typography } from "@mui/material"
import GuestLayout from "../../Layouts/GuestLayout"
import ipsosLogo from "../../assets/img/Ipsos logo.svg"; // Assuming SVG file location
import { useState } from "react";
import LoadingButton from "@mui/lab/LoadingButton";
import SendIcon from '@mui/icons-material/Send';

const GiftRequest = () => {
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");
    const [ loadingConfirmed, setLoadingConfirmed ] = useState(false); 

    return (
        <GuestLayout>
            <Card>
                {/* Header */}
                <CardContent sx={{ py: 1.5, background: "linear-gradient(to right, #fff, #fff)" }}>
                    <Box display="flex" justifyContent="center">
                        <Box width={120}>
                        <img
                            src={ipsosLogo}
                            alt="Company logo"
                            style={{
                                width: "100%",
                                height: "auto",
                                objectFit: "contain",
                            }}
                        />
                        </Box>
                    </Box>
                </CardContent>
                {/* Body */}
                <CardContent sx={{ 
                                    py: 1.5, 
                                    background: "linear-gradient(to right, #fff, #fff)", 
                                    textAlign: "justify",
                                    lineHeight: 1.5

                                }}>
                    <Typography variant="body2" sx={{ lineHeight: 1.7, mb: 1.5 }}>
                    Xin cảm ơn Quý Anh/Chị đã hoàn thành khảo sát. 
                    Những ý kiến đóng góp của Anh/Chị có ý nghĩa quan trọng đối với hoạt động nghiên cứu của chúng tôi.
                    </Typography>

                    <Typography variant="body2" sx={{ lineHeight: 1.7, mb: 1.5 }}>
                    Để ghi nhận sự hợp tác này, chúng tôi xin gửi đến Anh/Chị <b>một phần quà tri ân</b>. 
                    Vui lòng cung cấp số điện thoại để chúng tôi liên hệ trao quà.
                    </Typography>

                    <Alert severity="info" sx={{ width: "100%", alignItems: "center", mb: 2 }}>
                        Thông tin cá nhân của Anh/Chị được bảo mật và chỉ sử dụng cho mục đích trao quà.
                    </Alert>

                    {error && (
                        <Alert severity="error" className="text-sm">
                        {message}
                        </Alert>
                    )}
                </CardContent>

                <Divider />
                
                <CardContent sx={{ py: 1.5, background: "linear-gradient(to right, #fff, #fff)" }}>
                    <LoadingButton
                        type="submit"
                        size="large"
                        endIcon={<SendIcon />}
                        loading={loadingConfirmed}
                        loadingPosition="end"
                        variant="contained"
                        className='btn bg-vinnet-primary'
                        >
                        <span>XÁC NHẬN</span>
                    </LoadingButton>
                </CardContent>
            </Card>
        </GuestLayout>
    )
}

export default GiftRequest;