import "../../assets/css/components.css";
import { useState } from "react";
import axios from "axios";
import {
  TextField,
  Box,
  FormControl,
  Typography,
  InputAdornment,
  IconButton,
} from "@mui/material";
import LoadingButton from "@mui/lab/LoadingButton";
import LockResetIcon from "@mui/icons-material/LockReset";
import { Visibility, VisibilityOff } from "@mui/icons-material";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import { ApiConfig } from "../../config/ApiConfig";

const SetPassword = () => {
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  const { clearMustChangePassword, logout } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async () => {
    if (password.length < 6) {
      setErrorMessage("Password must be at least 6 characters.");
      return;
    }
    if (password !== confirmPassword) {
      setErrorMessage("Passwords do not match.");
      return;
    }

    setLoading(true);
    setErrorMessage("");

    try {
      const token = localStorage.getItem("authToken");
      await axios.post(
        ApiConfig.account.setFirstPassword,
        { password, password_confirmation: confirmPassword },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      clearMustChangePassword();
      navigate("/project-management/projects", { replace: true });
    } catch (error) {
      if (axios.isAxiosError(error)) {
        setErrorMessage(
          error.response?.data?.message ?? "An error occurred. Please try again."
        );
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
        backgroundColor: "#f5f5f5",
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
          gap: 3,
        }}
      >
        <Typography variant="h5" textAlign="center">
          Set Your Password
        </Typography>
        <Typography variant="body2" textAlign="center" color="text.secondary">
          Your account is new. Please set a password before continuing.
        </Typography>

        {errorMessage && (
          <div className="message-invalid">
            <span>{errorMessage}</span>
          </div>
        )}

        <FormControl fullWidth>
          <TextField
            label="New Password"
            type={showPassword ? "text" : "password"}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            InputProps={{
              endAdornment: (
                <InputAdornment position="end">
                  <IconButton onClick={() => setShowPassword((v) => !v)}>
                    {showPassword ? <VisibilityOff /> : <Visibility />}
                  </IconButton>
                </InputAdornment>
              ),
            }}
          />
        </FormControl>

        <FormControl fullWidth>
          <TextField
            label="Confirm Password"
            type={showPassword ? "text" : "password"}
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
          />
        </FormControl>

        <LoadingButton
          fullWidth
          onClick={handleSubmit}
          endIcon={<LockResetIcon />}
          loading={loading}
          variant="contained"
        >
          SET PASSWORD
        </LoadingButton>

        <Typography
          variant="body2"
          textAlign="center"
          sx={{ cursor: "pointer", color: "text.secondary" }}
          onClick={logout}
        >
          Back to Login
        </Typography>
      </Box>
    </Box>
  );
};

export default SetPassword;
