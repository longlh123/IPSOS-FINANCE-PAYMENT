import { Box, Button, Dialog, DialogTitle, Divider, Grid, Table, TableContainer, TableHead, TableRow, TextField, Typography } from "@mui/material";
import { useVinnetAccount } from "../../hook/useVinnetAccount";
import numeral from "numeral";
import GenericDialog from "../../components/Dialogs/GenericDialog";
import { useState } from "react";
import axios from "axios";
import { ApiConfig } from "../../config/ApiConfig";
import { Skeleton } from "@mui/material";
import { useGotItAccount } from "../../hook/useGotItAccount";

const Transactions = () => {
    const { loading: vinnetLoading, vinnetAccount } = useVinnetAccount();
    const { gotitAccount, addDeposit } = useGotItAccount();
    const [ open, setOpen ] = useState(false);
    const [ amount, setAmount ] = useState("");
    const [ gotitDeposited, setGotItDeposited ] = useState(0);

    const token = localStorage.getItem("authToken");

    const handleAddDeposit = async () => {

        await addDeposit(amount);
    }
    
    return (
        <Grid container spacing={3}>
            <Grid item xs={12} sm={6}>
                <Box
                    sx={{
                        p: 3,
                        borderRadius: 2,
                        boxShadow: 3,
                        bgcolor: "background.paper",
                        border: "1px solid #ddd",
                    }}
                >
                    <Typography 
                        variant="h6" 
                        gutterBottom 
                        sx={{
                            mb: 2
                        }}
                    >
                        Vinnet Account
                    </Typography>
                    <Divider sx={{ mb: 2 }} />
                    
                    <Box sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mb: 1
                    }}>
                        <Typography color="text.secondary">Deposited:</Typography>
                        {vinnetLoading ? (
                            <Skeleton width={80} height={24} />
                        ) : (
                            <Typography>${numeral(vinnetAccount?.deposited).format("0,0")}</Typography>
                        )}
                    </Box>
                    <Box sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mb: 1
                    }}>
                        <Typography color="text.secondary">Balance:</Typography>
                        {vinnetLoading ? (
                            <Skeleton width={80} height={24} />
                        ) : (
                            <Typography>${numeral(vinnetAccount?.balance).format("0,0")}</Typography>
                        )}
                    </Box>
                    <Box sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mb: 1
                    }}>
                        <Typography color="text.secondary">Spent:</Typography>
                        {vinnetLoading ? (
                            <Skeleton width={80} height={24} />
                        ) : (
                            <Typography>${numeral(vinnetAccount?.spent).format("0,0")}</Typography>
                        )}
                    </Box>
                </Box>
            </Grid>
            <Grid item xs={12} sm={6}>
                <Box
                    sx={{
                        p: 3,
                        borderRadius: 2,
                        boxShadow: 3,
                        bgcolor: "background.paper",
                        border: "1px solid #ddd",
                    }}
                >
                    <Box
                        sx={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center"
                        }}
                    >
                        <Typography 
                            variant="h6"
                            gutterBottom
                        >
                            Got It Account
                        </Typography>
                        <Button 
                            variant="contained" 
                            size="small" 
                            sx={{
                                mb: 2
                            }}
                            onClick={() => setOpen(true)}
                        >
                            + Add Deposited
                        </Button>
                    </Box>

                    <Divider sx={{ mb: 2 }} />
                    
                    <Box sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mb: 1
                    }}>
                        <Typography color="text.secondary">Deposited:</Typography>
                        <Typography>${numeral(gotitAccount.deposited).format("0,0")}</Typography>
                    </Box>
                    <Box sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mb: 1
                    }}>
                        <Typography color="text.secondary">Balance:</Typography>
                        <Typography>${numeral(gotitAccount.balance).format("0,0")}</Typography>
                    </Box>
                    <Box sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        mb: 1
                    }}>
                        <Typography color="text.secondary">Spent:</Typography>
                        <Typography>${numeral(gotitAccount.spent).format("0,0")}</Typography>
                    </Box>
                </Box>
            </Grid>
            
            <GenericDialog
                open={open}
                title="Add Deposited"
                onClose = {() => setOpen(false)}
                onSubmit={() => handleAddDeposit()}
            >
                <Box 
                    sx={{ 
                        display: "flex",
                        flexDirection: "column",
                        gap: 1,
                        mt: 1
                    }}
                >
                    <TextField
                        label="Amount"
                        type="number"
                        fullWidth
                        value={amount}
                        onChange={(e) => setAmount(e.target.value)}
                        sx={{ mb: 2 }}
                    />
                </Box>
            </GenericDialog>
        </Grid>
    )
}

export default Transactions;