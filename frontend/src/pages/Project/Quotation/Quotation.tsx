import { useEffect, useState } from "react";
import axios from "axios";
import { ApiConfig } from "../../../config/ApiConfig";
import QuotationDynamicForm from "./QuotationDynamicForm";
import { useQuotation } from "../../../hook/useQuotation";
import { useParams } from "react-router-dom";
import { Alert, Box, Chip, Divider, Grid, IconButton, MenuItem, Tab, Tabs, TextField, Tooltip, Typography } from "@mui/material";
import TabPanel from "../../../components/TabPanel";
import { QuotationVersionData } from "../../../config/QuotationConfig";
import EditIcon from "@mui/icons-material/Edit";
import CloseIcon from "@mui/icons-material/Close";
import DeleteIcon from "@mui/icons-material/Delete";
import SendIcon from '@mui/icons-material/Send';
import AddIcon from '@mui/icons-material/Add';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import useDialog from "../../../hook/useDialog";
import AlertDialog from "../../../components/AlertDialog/AlertDialog";

const Quotation: React.FC = () => {
    const { id } = useParams<{id: string}>();

    const [ formKey, setFormKey ] = useState(0);
    const [ schema, setSchema ] = useState([]);

    useEffect(() => {
        axios
            .get(ApiConfig.schema.quotationSchema)
            .then((res) => setSchema(res.data));
    }, []);

    const { loading, error, message: messageQuotation, project, versions, selectedVersion, setSelectedVersion, canEdit, setCanEdit, getQuotationVersions, addQuotation, updateQuotation, destroyQuotation } = useQuotation(Number(id));
    const [ openAlert, setOpenAlert ] = useState(false);
    const [ isEditing, setIsEditing ] = useState(false);

    const { open, title, message: messageDialog, showConfirmButton, openDialog, closeDialog, confirmDialog } = useDialog();
    
    const handleSaveVersion = async (data:any) => {
            
        let quotation = null;

        if(isEditing){
            quotation = await updateQuotation(data);
        } else {
            quotation = await addQuotation(data);
        }

        await getQuotationVersions();

        setIsEditing(false);
        setCanEdit(true);
        setOpenAlert(true);

        setFormKey(formKey + 1);
    };

    const handleCancel = () => {
        setSelectedVersion(selectedVersion);
        setIsEditing(false);

        setFormKey(formKey + 1);
    }

    const handleRemove = () => {
        openDialog({
            title: "Delete Version",
            message: `Are you sure that you want to this version "${selectedVersion?.version}"?`,
            showConfirmButton: true,
            onConfirm: async () => {
                await destroyQuotation();

                setFormKey(formKey + 1);

                setIsEditing(false);
                setCanEdit(true);
                setOpenAlert(true);
            }
        });
    }

    const handleApproveVersion = () => {

    };

    const [value, setValue] = useState('one');
    
    const handleChange = (event: React.SyntheticEvent, newValue: string) => {
        setValue(newValue);
    };

    return (
        <>
            {selectedVersion && (
                <Grid item xs={12} sm={6} md={4}>
                    {openAlert && (
                        <Alert 
                            severity= {error ? "error" : "success"} 
                            sx={{ width: "100%", alignItems: "center", mb: 2 }}
                            action={
                                <IconButton
                                    aria-label="close"
                                    color="inherit"
                                    size="small"
                                    onClick={() => setOpenAlert(false)}
                                >
                                    <CloseIcon fontSize="inherit" />
                                </IconButton>
                            }
                        >
                            <span 
                                dangerouslySetInnerHTML={{ __html: messageQuotation ?? "" }}
                            ></span>
                        </Alert>
                    )}
                    <Box
                        sx={{
                            display: "flex",
                            flexDirection: "column",
                            gap: 1,
                            p: 2,
                            border: "1px solid #eee",
                            borderRadius: 2,
                            backgroundColor: "#fafafa"
                        }}
                    >
                        <Box
                            sx={{
                                display: "flex",
                                alignItems: "center",
                                gap: 2
                            }}
                        >
                            <Typography variant="subtitle1" gutterBottom sx={{ mt: "4px"}}>
                                Version: 
                            </Typography>
                            <TextField
                                select
                                size="small"
                                variant="outlined"
                                value={selectedVersion?.id ?? ""}
                                disabled={isEditing}
                                onChange={(e) => {
                                    const version = versions?.find(v => v.id === Number(e.target.value));
                                    setSelectedVersion(version ?? null);
                                    setOpenAlert(false);
                                }}
                                sx={{width: "150px"}}
                            >
                                {versions?.map((v: QuotationVersionData) => {
                                    return (
                                        <MenuItem
                                            key={v.id} 
                                            value={v.id}
                                        >
                                            Version {v.version}
                                        </MenuItem>
                                    )
                                })}
                            </TextField>

                            {selectedVersion && (
                                <Chip
                                    label={selectedVersion.status}
                                    color={
                                        selectedVersion.status === "draft" ? "default" : selectedVersion.status === "submitted" ? "warning" : "success"
                                    }
                                    size="small"
                                />
                            )}

                            {canEdit && (
                                <>
                                    <Tooltip title={!isEditing ? "Edit" : "Cancel Edit"}>
                                        <IconButton
                                            size="small"
                                            onClick={() => {
                                                if(isEditing){
                                                    handleCancel();
                                                } else {
                                                    setIsEditing(true);
                                                }
                                            }}
                                        >
                                            {isEditing ? <CloseIcon/> : <EditIcon/>}
                                        </IconButton>
                                    </Tooltip>

                                    <Tooltip title={"Delete Draft"}>
                                        <IconButton
                                            color="error"
                                            onClick={() => handleRemove()}
                                            disabled={isEditing}
                                        >
                                            <DeleteIcon />
                                        </IconButton>
                                    </Tooltip>

                                    <Tooltip title={"Submit Version"}>
                                        <IconButton
                                            color="warning"
                                            onClick={() => handleRemove()}
                                            disabled={isEditing}
                                        >
                                            <SendIcon />
                                        </IconButton>
                                    </Tooltip>

                                    <Tooltip title={"Approve Version"}>
                                        <IconButton
                                            color="success"
                                            onClick={() => handleRemove()}
                                            disabled={isEditing}
                                        >
                                            <CheckCircleIcon />
                                        </IconButton>
                                    </Tooltip>

                                    <Tooltip title="Create new version">
                                        <IconButton
                                            color="success"
                                            onClick={handleRemove}
                                        >
                                            <AddIcon />
                                        </IconButton>
                                    </Tooltip>
                                </>
                            )}

                            {/* {canEdit && selectedVersion.status === 'draft' && (
                                <Tooltip title={"Delete Draft"}>
                                    <IconButton
                                        color="error"
                                        onClick={() => handleRemove()}
                                        disabled={isEditing}
                                    >
                                        <DeleteIcon />
                                    </IconButton>
                                </Tooltip>
                            )} */}
                        </Box>
                        <Divider style={{ margin: "10px 0" }} />
                        <Box
                            sx={{
                                display: "flex",
                                alignItems: "center",
                                gap: 2
                            }}
                        >
                            {selectedVersion && (
                                <Typography variant="body2" color="text.secondary">
                                    Created by: {" "} 
                                    <strong>
                                        {selectedVersion.created_user?.name} ({selectedVersion.created_user?.email})
                                    </strong>
                                </Typography>
                            )}

                            {selectedVersion && selectedVersion.created_at && (
                                <Typography variant="body2" color="text.secondary">
                                    Created at: {" "}
                                    {new Date(
                                        selectedVersion.created_at
                                    ).toLocaleDateString()}
                                </Typography>
                            )}
                        </Box>
                        {selectedVersion && selectedVersion.approved_user && selectedVersion.approved_at && (
                            <Box
                                sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    gap: 2
                                }}
                            >
                                <Typography variant="body2" color="text.secondary">
                                    Approved by: {" "} 
                                    <strong>
                                        {selectedVersion.approved_user?.name} ({selectedVersion.approved_user?.email})
                                    </strong>
                                </Typography>

                                <Typography variant="body2" color="text.secondary">
                                    Approved at: {" "}
                                    {new Date(
                                        selectedVersion.approved_at
                                    ).toLocaleDateString()}
                                </Typography>
                            </Box>
                        )}
                    </Box>
                </Grid>
            )}

            <Tabs
                value={value}
                onChange={handleChange}
                textColor="secondary"
                indicatorColor="secondary"
                aria-label="secondary tabs example"
                >
                <Tab value="one" label="GENERAL" />
                <Tab value="two" label="SAMPLE" />
                <Tab value="three" label="OPERATION" />
            </Tabs>

            <TabPanel value={value} index="one">
                <QuotationDynamicForm
                    key={formKey}
                    schema={schema}
                    projectData={project ?? null}
                    initialQuotationData={selectedVersion?.quotation}
                    isEditting={versions?.length === 0 ? true : isEditing}
                    onSubmit={handleSaveVersion}
                />
            </TabPanel>

            <AlertDialog
                open={open}
                title={title}
                message={messageDialog}
                showConfirmButton={showConfirmButton}
                onClose={closeDialog}
                onConfirm={confirmDialog}
            />
        </>
    )
}

export default Quotation;