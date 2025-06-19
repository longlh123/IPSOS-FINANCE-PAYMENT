// src/pages/Page202.jsx
import React from 'react';
import { Box, Typography } from '@mui/material';
import { useLocation, useNavigate } from 'react-router-dom';

interface Page200Props {
    messageSuccess: string;
}

const Page200: React.FC<Page200Props> = ({messageSuccess}) => {
    const navigate = useNavigate();
    const location = useLocation();

    const searchParams = new URLSearchParams(location.search);
    const msgSuccess = searchParams.get('message') || messageSuccess;

    return (
        <Box 
            sx={{ 
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                height: '100vh',
                textAlign: 'center'
            }}
        >
            <Typography variant="h4" component="h1" gutterBottom>
                Success
            </Typography>
            <Typography variant="h6" component="p">
                {msgSuccess}
            </Typography>
        </Box>
    );
};

export default Page200;
