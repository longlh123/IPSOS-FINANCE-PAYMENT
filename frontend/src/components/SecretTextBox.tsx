import { ContentCopy, Visibility, VisibilityOff } from "@mui/icons-material";
import { Grid, IconButton, InputAdornment, TextField, Typography } from "@mui/material"
import { useState } from "react";

interface SecretTextBoxProps {
    label: string,
    value: string
}

const SecretTextBox: React.FC<SecretTextBoxProps> = ({label, value}) => {
    const [ show, setShow ] = useState(false);
    
    const handleToggleVisibility = () => {
        setShow((prev) => !prev);
    };

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(value);
            alert("Copied to clipboard!");
        } catch (err) {
            console.error("Failed to copy: ", err);
        }
    };

    return (                            
        <div style={{ marginBottom: "1rem" }}>
            <Typography variant="body2" gutterBottom>
                { label }
            </Typography>
            <TextField
                type = { show ? "text" : "password" }
                value = { value }
                fullWidth
                InputProps={{
                    readOnly: true, //chỉ hiển thị, không cho sửa
                    endAdornment:(
                        <InputAdornment position="end">
                            <IconButton onClick={handleToggleVisibility}>
                                {show ? <VisibilityOff /> : <Visibility />}
                            </IconButton>
                            <IconButton onClick={handleCopy}>
                                <ContentCopy />
                            </IconButton>
                        </InputAdornment>
                    ) 
                }}
            />
        </div>
    )
}

export default SecretTextBox;