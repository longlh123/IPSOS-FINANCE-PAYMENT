import { Box, TextField, Typography } from "@mui/material";
import { useState } from "react";

interface CustomVoucherData {
    employee_id: string,
    respondent_id: string,
    phone_number: string
}

export default function CustomVoucherLog(){
    const [ formValues, setFormValues ] = useState<CustomVoucherData>({
        employee_id: "",
        respondent_id: "",
        phone_number: ""
    });

    const handleChange = (name: string, value: string) => {
        setFormValues((prev:any) => ({
            ...prev,
            [name]: value
        }));
    } 

    return (
        <div>
            <Box
                sx={{
                    minHeight: "100vh",
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    justifyContent: "center",
                    padding: 2,
                    backgroundColor: "#f5f5f5",
                }}
            >
                <div style={{ 
                    display: "flex", 
                    flexDirection: "row", 
                    marginBottom: "1rem" }}
                >
                    <Typography variant="body2" gutterBottom>
                        Interviewer ID
                    </Typography>
                    <TextField
                        fullWidth
                        size="small"
                        type="string"
                        value={formValues['employee_id']}
                        variant="outlined"
                        onChange={(e) => handleChange("employee_id", e.target.value)}
                    />
                </div>
                <div style={{ display: "flex", flexDirection: "row", marginBottom: "1rem" }}>
                    <Typography variant="body2" gutterBottom>
                        Respondent ID
                    </Typography>
                    <TextField
                        fullWidth
                        size="small"
                        type="string"
                        value={formValues['respondent_id']}
                        variant="outlined"
                        onChange={(e) => handleChange("respondent_id", e.target.value)}
                    />
                </div>
            </Box>
        </div>
    )
}