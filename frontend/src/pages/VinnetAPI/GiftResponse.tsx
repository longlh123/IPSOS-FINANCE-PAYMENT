import { Alert, Box, Card, CardContent, Typography } from "@mui/material"
import GuestLayout from "../../Layouts/GuestLayout"
import ipsosLogo from "../../assets/img/Ipsos logo.svg"; // Assuming SVG file location
import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import ErrorOutlineIcon from "@mui/icons-material/ErrorOutline";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";

const GiftResponse = () => {
    const [ params ] = useSearchParams();

    const [ status, setStatus ] = useState<"success" | "error" | null>(null);
    const [ message, setMessage ] = useState("");

    useEffect(() => {
        const s = params.get('status');
        const m = params.get('message');

        if(s === 'success'){
            setStatus("success");
            setMessage(m ?? "");
        } else {
            setStatus("error");
            setMessage(m ?? "");
        }
    }, [params]);

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
                <CardContent
                    sx={{
                        py: 3,
                        background: "#fff",
                        textAlign: "center"
                    }}
                >
                    {status === "success" && (
                        <Typography
                            variant="h5"
                            sx={{ fontWeight: 600, mb: 2, display: "flex", alignItems: "center", justifyContent: "center", gap: 1, textAlign: "center" }}
                        >
                            <CheckCircleOutlineIcon color="success" />
                            Giao dịch thành công
                        </Typography>
                    )}
                    
                    {status === "error" && (
                        <Typography
                            variant="h5"
                            sx={{ fontWeight: 600, mb: 2, display: "flex", alignItems: "center", justifyContent: "center", gap: 1, textAlign: "center" }}
                        >
                            <ErrorOutlineIcon color="error" />
                            Giao dịch thất bại
                        </Typography>
                    )}

                    <Typography variant="body1" sx={{ mb: 2 }}>
                        Cảm ơn Quý Anh/Chị đã hợp tác cùng nghiên cứu của chúng tôi.
                    </Typography>

                    <Typography variant="body1" sx={{ mb: 2 }}>
                        {message}
                    </Typography>
                </CardContent>
            </Card>
        </GuestLayout>
    )
}

export default GiftResponse;