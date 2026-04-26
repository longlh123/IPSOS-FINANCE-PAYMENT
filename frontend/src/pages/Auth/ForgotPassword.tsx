import "../../assets/css/components.css";
import React, { useState } from "react";
import axios from "axios";
import { TextField, Box, FormControl, Typography } from "@mui/material";
import LoadingButton from '@mui/lab/LoadingButton';
import SendIcon from '@mui/icons-material/Send';
import { ApiConfig } from "../../config/ApiConfig";

const ForgotPassword = () => {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [statusMessage, setStatusMessage] = useState("");

  const handleForgotPassword = async () => {
    try {
      setLoading(true);

      const response = await axios.post(
        ApiConfig.account.forgotPassword,
        {
          email: email,
        }
      );

      setStatusMessage(response.data.status);
    } catch (error) {
      if (axios.isAxiosError(error)) {
        setStatusMessage(error.response?.data.message);
      } else {
        console.log(error);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box
      sx={{
          minHeight: "100vh",
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          backgroundColor: "#f5f5f5"
      }}
    >
      <Box 
        sx={{ 
            width: 500,
            p: 4,
            borderRadius: 2,
            backgroundColor: "#fff",
            boxShadow: 3,
            display: "flex",
            flexDirection: "column",
            gap: 3
        }}
      >
        <Typography variant="h5" textAlign="center">
            <span>Forgot Password</span>
        </Typography>
        <Typography variant="body2" textAlign="center">
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
        </Typography>
        {statusMessage.length != 0 && (
          <div className='message-invalid'>
              <span>{statusMessage}</span>
          </div>
        )}
        <FormControl fullWidth>
          <TextField
            label="Email"
            variant="outlined"
            onChange={(event) => setEmail(event.target.value)}
          />
        </FormControl>
        <LoadingButton
            fullWidth
            onClick={handleForgotPassword}
            endIcon={<SendIcon />}
            loading={loading}
            variant="contained"
        >
            SEND EMAIL
        </LoadingButton>
      </Box>
    </Box>
  );
};

export default ForgotPassword;
