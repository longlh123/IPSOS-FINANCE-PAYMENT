import "../../assets/css/components.css";
import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import ipsosLogo from "../../assets/img/Ipsos logo.svg"; // Assuming SVG file location
import axios from "axios";

import { TextField, Button } from "@mui/material";
import { ApiConfig } from "../../config/ApiConfig";
import GuestLayout from '../../Layouts/GuestLayout';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import LoadingButton from '@mui/lab/LoadingButton';
import SendIcon from '@mui/icons-material/Send';

const ForgotPassword = () => {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [statusMessage, setStatusMessage] = useState("");

  const handleChangeInput = (value: string) => {
    setEmail(value);
  };

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
    <>
      <div className="LoginForm">
        <div className="header-form-login">
          <img src={ipsosLogo} alt="Ipsos Logo" />
          <h2>Forgot Password</h2>
          <p>
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
          </p>
        </div>
        <div className="form-validate">
          <div className="error-control">
            {/* {isLoggedIn && <Alert severity="error">{alertMessage}</Alert>} */}
          </div>
          <div className="email-control">
            <TextField
              label="Email"
              variant="outlined"
              className="TextFieldLogin"
            />
          </div>
          <div className="password-control"></div>
          {/* <div className="forgot-password">
            <span onClick={handleForgotPassword}>Forgot Password ?</span>
          </div> */}

          <div className="btn-login">
            <Button
              sx={{
                width: "100%",
                my: 4,
                backgroundColor: "var(--main-color) !important",
                color: "#fff",
                textTransform: "none !important",
              }}
            >
              Send Email
            </Button>
          </div>

          <div className="footer-form-login">
            <p onClick={() => navigate("/Login")}>Comeback Login</p>
          </div>
        </div>
      </div>
    </>
  );
};

export default ForgotPassword;
