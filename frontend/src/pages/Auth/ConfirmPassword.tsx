import "../../assets/css/components.css";
import React, { useState } from 'react';
import axios from 'axios';
import { useLocation, useNavigate } from 'react-router-dom';

import GuestLayout from '../../Layouts/GuestLayout';
import { VisibilityOff, Visibility } from "@mui/icons-material";
import LoadingButton from '@mui/lab/LoadingButton';
import SendIcon from '@mui/icons-material/Send';

import {
    Typography,
    InputLabel,
    OutlinedInput,
    IconButton,
    FormControl,
    Box,
    TextField
  } from "@mui/material";
import { ApiConfig } from "../../config/ApiConfig";

const useQuery = () => {
    return new URLSearchParams(useLocation().search);
};

const ConfirmPassword = () => {
    const [showPassword, setShowPassword] = useState(false);
    const handleClickShowPassword = () => setShowPassword(!showPassword);
    const [loading, setLoading] = useState(false);
    
    const query = useQuery();
    const navigate = useNavigate();
    const [email, setEmail] = useState(query.get('email') || '');
    const [token, setToken] = useState(query.get('token') || '');
    const [statusMessage, setStatusMessage] = useState('');

    const [inforResetPassword, setInforResetPassword] = useState({
        password: "",
        password_confirmation: "",
    });

    const handleChangeInput = (prev: string, value: string) => {
        setInforResetPassword({...inforResetPassword, [prev]: value});
    };
    
    const handleResetPassword = async () => {
        try{
            setLoading(true);

            const response = await axios.post(ApiConfig.account.resetPassword, {
                token: token,
                email: email,
                password: inforResetPassword.password,
                password_confirmation: inforResetPassword.password_confirmation,
            });

            navigate('/Login');
        }catch(error){
            if (axios.isAxiosError(error)) {
                setStatusMessage(error.response?.data.message);
            } else {
                console.log(error);
            }
        }finally{
            setLoading(false);
        }
    };

    return (
        <GuestLayout>
            <Box 
                sx={{ 
                    marginTop: 8,
                    display: 'flex',
                    flexDirection: 'column',
                    textAlign: 'justify',
                    gap: 3,
                    width: "100%",
                }}
            >
                <div>
                    <Typography variant="h4">
                        <span>Confirmation Password</span>
                    </Typography>
                </div>
                <div>
                    <Typography variant="h6">
                        This is a secure area of the application. Please confirm your password before continuing.
                    </Typography>
                </div>
                {statusMessage.length != 0 && (
                    <div className='message-invalid'>
                        <span>{statusMessage}</span>
                    </div>
                )}
                <div>
                    <FormControl className="TextFieldLogin" variant="outlined">
                        <InputLabel htmlFor="outlined-adornment-password">
                            Password
                        </InputLabel>
                        <OutlinedInput
                            id="outlined-adornment-password"
                            type={showPassword ? "text" : "password"}
                            onChange={(e) => handleChangeInput("password", e.target.value)}
                            endAdornment={
                            <IconButton onClick={handleClickShowPassword}>
                                {showPassword ? <VisibilityOff /> : <Visibility />}
                            </IconButton>
                            }
                            label="Password"
                        />
                    </FormControl>
                </div>
                <div>
                    <FormControl className="TextFieldLogin" variant="outlined">
                        <InputLabel htmlFor="outlined-adornment-password">
                            Confirm Password
                        </InputLabel>
                        <OutlinedInput
                            id="outlined-adornment-password"
                            type={showPassword ? "text" : "password"}
                            onChange={(e) => handleChangeInput("password_confirmation", e.target.value)}
                            endAdornment={
                            <IconButton onClick={handleClickShowPassword}>
                                {showPassword ? <VisibilityOff /> : <Visibility />}
                            </IconButton>
                            }
                            label="Confirm Password"
                        />
                    </FormControl>
                </div>
                <div className="item">
                    <LoadingButton
                        size="small"
                        onClick={handleResetPassword}
                        endIcon={<SendIcon />}
                        loading={loading}
                        loadingPosition="end"
                        variant="contained"
                        className='btn bg-primary'
                        >
                        <span>RESET PASSWORD</span>
                    </LoadingButton>
                </div>  
            </Box>
        </GuestLayout>
    );
}

export default ConfirmPassword;