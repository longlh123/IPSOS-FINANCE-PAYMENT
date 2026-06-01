import { useCallback, useEffect, useRef, useState } from "react";
import axios from "axios";
import { ApiConfig } from "../../../config/ApiConfig";
import QuotationDynamicForm from "./QuotationDynamicForm";
import { useQuotation } from "../../../hook/useQuotation";
import { useParams, useSearchParams } from "react-router-dom";
import { Alert, Box, Button, Chip, Divider, Grid, IconButton, MenuItem, Tab, Tabs, TextField, Tooltip, Typography } from "@mui/material";
import TabPanel from "../../../components/TabPanel";
import { QuotationVersionData } from "../../../config/QuotationConfig";
import EditIcon from "@mui/icons-material/Edit";
import CloseIcon from "@mui/icons-material/Close";
import DeleteIcon from "@mui/icons-material/Delete";
import SendIcon from '@mui/icons-material/Send';
import AddIcon from '@mui/icons-material/Add';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import LinkIcon from '@mui/icons-material/Link';
import UndoIcon from '@mui/icons-material/Undo';
import useDialog from "../../../hook/useDialog";
import AlertDialog from "../../../components/AlertDialog/AlertDialog";
import GenericDialog from "../../../components/Dialogs/GenericDialog";
import { useAuth } from "../../../contexts/AuthContext";
import OperationsDynamicForm from "./OperationsDynamicForm";
import { useOperations } from "../../../hook/useOperations";
import EstimateCostTable, { EstimateCostNode, normalizeNodes } from "./EstimateCostTable";
import CalculateIcon from '@mui/icons-material/Calculate';

const Quotation: React.FC = () => {
    const { id } = useParams<{id: string}>();
    const projectId = Number(id) || 0;
    const [searchParams] = useSearchParams();

    const { user } = useAuth();
    const canMutate = user?.role !== 'Field Manager';

    const [ formKey, setFormKey ] = useState(0);
    const [ quotationSchema, setQuotationSchema ] = useState([]);
    const [ operationSchema, setOperationSchema ] = useState([]);
    const [ linkCopied, setLinkCopied ] = useState(false);

    const { actionState,
            project,
            versions,
            selectedVersion,
            setSelectedVersion,
            canEdit,
            setCanEdit,
            getQuotationVersions,
            addQuotation,
            updateQuotationVersion,
            destroyQuotationVersion,
            submitQuotationVersion,
            cloneQuotationVersion,
            approveQuotationVersion,
            withdrawQuotationVersion
    } = useQuotation(Number(id));

    const { selectedOperations, 
            actionState: operationsActionState, 
            addOperations, 
            getOperations 
    } = useOperations(projectId);

    const [ openAlert, setOpenAlert ] = useState(false);
    const [ alertSource, setAlertSource ] = useState<'quotation' | 'operations'>('quotation');
    const [ isEditing, setIsEditing ] = useState(false);

    const [ estimateCostItems, setEstimateCostItems ] = useState<EstimateCostNode[]>([]);
    const [ showEstimatedCost, setShowEstimatedCost ] = useState(false);

    const { open, title, message: messageDialog, showConfirmButton, openDialog, closeDialog, confirmDialog } = useDialog();
    
    const [ openCloneNewVersion, setOpenCloneNewVersion ] = useState<boolean>(false);
    const [ selectedCloneVersion, setSelectedCloneVersion ] = useState<QuotationVersionData | null>(null);

    const loadQuotationSchema = useCallback(async (projectType?: string) => {
        try {
            const token = localStorage.getItem('authToken');
            let url = ApiConfig.project.getQuotationSchema.replace("{projectId}", projectId.toString());
            if (projectType) url += `?project_type=${encodeURIComponent(projectType)}`;
            const response = await axios.get(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
            });
            setQuotationSchema(response.data);
        } catch (error: any) {
            console.error(error.response?.data || error.message);
        }
    }, [projectId]);

    const loadOperationSchema = useCallback(async (projectType?: string) => {
        try {
            const token = localStorage.getItem('authToken');
            let url = ApiConfig.project.getOperationsSchema.replace("{projectId}", projectId.toString());
            
            const response = await axios.get(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
            });

            setOperationSchema(response.data);
        } catch (error: any) {
            console.error(error.response?.data || error.message);
        }
    }, [projectId]);

    useEffect(() => {
        loadQuotationSchema();
    }, [loadQuotationSchema]);

    useEffect(() => {
        loadOperationSchema();
    }, [loadOperationSchema]);

    const versionParamApplied = useRef(false);

    // Auto-select version from ?version= query param — only on initial load
    useEffect(() => {
        if (versionParamApplied.current || !versions || versions.length === 0) return;
        const versionId = Number(searchParams.get('version'));
        if (versionId) {
            const target = versions.find(v => v.id === versionId);
            if (target) setSelectedVersion(target);
        } else if (!canMutate) {
            // Field Manager: prefer the first submitted version
            const submitted = versions.find(v => v.status === 'submitted');
            if (submitted) setSelectedVersion(submitted);
        }
        versionParamApplied.current = true;
    }, [versions, searchParams]);

    // Load operations data whenever the selected version changes
    useEffect(() => {
        if (!selectedVersion) return;
        getOperations(selectedVersion.id);
    }, [selectedVersion?.id]);

    // Sync estimateCostItems from loaded operations; reset tab if no data for new version
    useEffect(() => {
        const items = selectedOperations?.operations_data?.estimate_cost;
        if (Array.isArray(items) && items.length > 0) {
            setEstimateCostItems(normalizeNodes(items));
            setShowEstimatedCost(true);
        } else {
            setEstimateCostItems([]);
            setShowEstimatedCost(false);
            setValue(prev => prev === 'three' ? 'two' : prev);
        }
    }, [selectedOperations?.id]);

    const handleCopyLink = () => {
        if (!selectedVersion) return;
        const url = `${window.location.origin}${window.location.pathname}?version=${selectedVersion.id}`;
        navigator.clipboard.writeText(url).then(() => {
            setLinkCopied(true);
            setTimeout(() => setLinkCopied(false), 2000);
        });
    };

    const handleSaveOperations = async (data: any) => {
        try{
            if(!selectedVersion) return;
            await addOperations(selectedVersion.id, { ...data, estimate_cost: estimateCostItems });
            setAlertSource('operations');
            setOpenAlert(true);
        } catch(error: any){
            setAlertSource('operations');
            setOpenAlert(true);
        }
    }

    const handleSaveEstimateCost = async () => {
        try {
            if (!selectedVersion) return;
            const merged = {
                ...(selectedOperations?.operations_data ?? {}),
                estimate_cost: estimateCostItems
            };
            await addOperations(selectedVersion.id, merged);
            setAlertSource('operations');
            setOpenAlert(true);
        } catch (error: any) {
            setAlertSource('operations');
            setOpenAlert(true);
        }
    };


    const handleSaveVersion = async (data:any) => {
        try
        {
            let quotation = null;
            const currentVersionId = selectedVersion?.id;

            if(isEditing){
                quotation = await updateQuotationVersion(data);
            } else {
                quotation = await addQuotation(data);
            }

            await getQuotationVersions({ silent: true, keepSelectedId: currentVersionId });

            setIsEditing(false);
            setCanEdit(true);
            setAlertSource('quotation');
            setOpenAlert(true);

            setFormKey(formKey + 1);
        } catch(error: any){
            setAlertSource('quotation');
            setOpenAlert(true);
        }
    };

    const handleCancel = () => {
        setSelectedVersion(selectedVersion);
        setIsEditing(false);
        setFormKey(formKey + 1);
        loadQuotationSchema();
    }

    const handleRemove = () => {
        openDialog({
            title: "Delete Version",
            message: `Are you sure that you want to remove this version "${selectedVersion?.version}"?`,
            showConfirmButton: true,
            onConfirm: async () => {
                await destroyQuotationVersion();

                setFormKey(formKey + 1);

                setIsEditing(false);
                setCanEdit(true);
                setAlertSource('quotation');
                setOpenAlert(true);
            }
        });
    }

    const handleSubmit = () => {

        openDialog({
            title: 'Submit Version',
            message: `Are you sure that you want to submit this version?`,
            showConfirmButton: true,
            onConfirm: async () => {
                await submitQuotationVersion();

                setFormKey(formKey + 1);

                setIsEditing(false);
                setCanEdit(true);
                setAlertSource('quotation');
                setOpenAlert(true);
            }
        });
    };

    const handleApprove = () => {
        openDialog({
            title: 'Approve Version',
            message: `Are you sure that you want to approve this version?`,
            showConfirmButton: true,
            onConfirm: async () => {
                await approveQuotationVersion();
                setAlertSource('quotation');
                setOpenAlert(true);
            }
        });
    }

    const handleWithdraw = () => {
        openDialog({
            title: 'Withdraw Version',
            message: `Are you sure that you want to withdraw this version? It will return to draft and can be edited again.`,
            showConfirmButton: true,
            onConfirm: async () => {
                await withdrawQuotationVersion();
                setAlertSource('quotation');
                setOpenAlert(true);
            }
        });
    }

    const handleOpenCloneNewVersionDialog = () => {
        setOpenCloneNewVersion(true);
    }

    const handleCloneNewVersion = async () => {
        if(!selectedCloneVersion) return;

        await cloneQuotationVersion(selectedCloneVersion);

        setFormKey(formKey + 1);

        setOpenCloneNewVersion(false);
        setSelectedCloneVersion(null);

        setIsEditing(true);
        setCanEdit(true);
        setAlertSource('quotation');
        setOpenAlert(true);
    }

    //Tab
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
                            severity={(alertSource === 'operations' ? operationsActionState : actionState).error ? "error" : "success"}
                            sx={{ mb: 1.5, borderRadius: "8px", fontSize: "0.875rem" }}
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
                                dangerouslySetInnerHTML={{ __html: (alertSource === 'operations' ? operationsActionState : actionState).message ?? "" }}
                              ></span>
                        </Alert>
                    )}
                    <Box
                        sx={{
                            display: "flex",
                            flexDirection: "column",
                            gap: 1,
                            p: 2,
                            border: "1px solid",
                            borderColor: "var(--body-color)",
                            borderRadius: "12px",
                            backgroundColor: "var(--background-color)"
                        }}
                    >
                        <Box
                            sx={{
                                display: "flex",
                                alignItems: "center",
                                gap: 2
                            }}
                        >
                            <Typography sx={{ fontSize: "0.875rem", fontWeight: 600, color: "var(--text-color)", mt: "4px" }}>
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
                                sx={{width: "250px"}}
                            >
                                {versions?.map((v: QuotationVersionData) => {
                                    return (
                                        <MenuItem
                                            key={v.id} 
                                            value={v.id}
                                        >
                                            <Box
                                                sx={{
                                                    display: "flex",
                                                    justifyContent: "space-between",
                                                    alignItems: "center",
                                                    width: "100%"
                                                }}
                                            >
                                                <span>Version {v.version}</span>
                                                
                                                <Chip
                                                    label={v.status}
                                                    size="small"
                                                    color={
                                                        v.status === "draft" ? "default" : v.status === "submitted" ? "warning" : "success"
                                                    }
                                                />
                                            </Box>
                                            
                                        </MenuItem>
                                    )
                                })}
                            </TextField>
                            
                            {canMutate && canEdit && (
                                <>
                                    <Tooltip title={!isEditing ? "Edit" : "Cancel Edit"}>
                                        <IconButton
                                            size="small"
                                            onClick={() => {
                                                if(isEditing){
                                                    handleCancel();
                                                } else {
                                                    setOpenAlert(false);
                                                    setIsEditing(true);
                                                }
                                            }}
                                            disabled={!(selectedVersion.status === 'draft')}
                                        >
                                            {isEditing ? <CloseIcon/> : <EditIcon/>}
                                        </IconButton>
                                    </Tooltip>

                                    <Tooltip title={"Delete Draft"}>
                                        <IconButton
                                            color="error"
                                            onClick={() => handleRemove()}
                                            disabled={isEditing || (!isEditing && !(selectedVersion.status === 'draft'))}
                                        >
                                            <DeleteIcon />
                                        </IconButton>
                                    </Tooltip>
                                    
                                    {(selectedVersion.status === 'draft') && (
                                        <Tooltip title="Submit Version">
                                            <IconButton
                                                color="warning"
                                                onClick={handleSubmit}
                                                disabled={isEditing}
                                            >
                                                <SendIcon />
                                            </IconButton>
                                        </Tooltip>
                                    )}

                                    {selectedVersion.status === 'submitted' && (
                                        <>
                                            <Tooltip title="Approve Version">
                                                <IconButton
                                                    color="success"
                                                    onClick={handleApprove}
                                                    disabled={isEditing}
                                                >
                                                    <CheckCircleIcon />
                                                </IconButton>
                                            </Tooltip>

                                            <Tooltip title="Withdraw (Rút lại)">
                                                <IconButton
                                                    color="error"
                                                    onClick={handleWithdraw}
                                                    disabled={isEditing}
                                                >
                                                    <UndoIcon />
                                                </IconButton>
                                            </Tooltip>
                                        </>
                                    )}

                                    <Tooltip title="Create new version">
                                        <IconButton
                                            color="success"
                                            onClick={handleOpenCloneNewVersionDialog}
                                        >
                                            <AddIcon />
                                        </IconButton>
                                    </Tooltip>

                                </>
                            )}

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
                                <Typography sx={{ fontSize: "0.8125rem", color: "var(--text-primary-color)" }}>
                                    {selectedVersion.status === 'draft' ? 'Created by' : 'Submitted by'}: {" "} 
                                    <strong>
                                        {selectedVersion.created_user?.name} ({selectedVersion.created_user?.email})
                                    </strong>
                                </Typography>
                            )}

                            {selectedVersion && selectedVersion.created_at && (
                                <Typography sx={{ fontSize: "0.8125rem", color: "var(--text-primary-color)" }}>
                                    {selectedVersion.status === 'draft' ? 'Created at' : 'Submitted at'}: {" "}
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
                                <Typography sx={{ fontSize: "0.8125rem", color: "var(--text-primary-color)" }}>
                                    Approved by: {" "}
                                    <strong>
                                        {selectedVersion.approved_user?.name} ({selectedVersion.approved_user?.email})
                                    </strong>
                                </Typography>

                                <Typography sx={{ fontSize: "0.8125rem", color: "var(--text-primary-color)" }}>
                                    Approved at: {" "}
                                    {new Date(
                                        selectedVersion.approved_at
                                    ).toLocaleDateString()}
                                </Typography>
                            </Box>
                        )}

                        {selectedVersion && ['submitted', 'approved'].includes(selectedVersion.status) && (
                            <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                                <Typography sx={{ fontSize: "0.8125rem", color: "var(--text-primary-color)", whiteSpace: "nowrap" }}>
                                    Quotation Link:
                                </Typography>
                                <Typography
                                    sx={{
                                        fontSize: "0.8125rem",
                                        color: "var(--main-color)",
                                        overflow: "hidden",
                                        textOverflow: "ellipsis",
                                        whiteSpace: "nowrap",
                                        maxWidth: 420,
                                    }}
                                >
                                    {`${window.location.origin}${window.location.pathname}?version=${selectedVersion.id}`}
                                </Typography>
                                <Tooltip title={linkCopied ? "Copied!" : "Copy link"}>
                                    <IconButton
                                        size="small"
                                        onClick={handleCopyLink}
                                        sx={{ color: linkCopied ? "var(--main-color)" : "var(--text-secondary-color)" }}
                                    >
                                        <LinkIcon fontSize="small" />
                                    </IconButton>
                                </Tooltip>
                            </Box>
                        )}
                    </Box>
                </Grid>
            )}

            <Tabs
                value={value}
                onChange={handleChange}
                aria-label="quotation tabs"
                sx={{
                    "& .MuiTabs-indicator": { backgroundColor: "var(--main-color)" },
                    "& .MuiTab-root.Mui-selected": { color: "var(--main-color)" },
                    "& .MuiTab-root": { fontSize: "0.8125rem", fontWeight: 600 },
                }}
                >
                <Tab value="one" label="GENERAL" />
                <Tab value="two" label="OPERATIONS" />
                {showEstimatedCost && <Tab value="three" label="ESTIMATED COST" />}
            </Tabs>

            <TabPanel value={value} index="one">
                <QuotationDynamicForm
                    key={formKey}
                    schema={quotationSchema}
                    initialData={selectedVersion?.quotation_data}
                    isEditting={versions?.length === 0 ? true : isEditing}
                    onSubmit={handleSaveVersion}
                    onProjectTypeChange={loadQuotationSchema}
                />
            </TabPanel>
            <TabPanel value={value} index="two">
                <OperationsDynamicForm
                    key={formKey}
                    schema={operationSchema}
                    initialData={selectedOperations?.operations_data}
                    isEditting={!canMutate}
                    onSubmit={handleSaveOperations}
                />
                {!canMutate && !showEstimatedCost && (
                    <Button
                        variant="outlined"
                        startIcon={<CalculateIcon />}
                        onClick={() => {
                            setShowEstimatedCost(true);
                            setValue('three');
                        }}
                        sx={{
                            mt: 1,
                            borderColor: "var(--main-color)",
                            color: "var(--main-color)",
                            textTransform: "none",
                            '&:hover': { borderColor: "var(--main-color)", backgroundColor: "rgba(0,157,156,0.06)" }
                        }}
                    >
                        Generate Estimated Cost
                    </Button>
                )}
            </TabPanel>

            <TabPanel value={value} index="three">
                <EstimateCostTable
                    value={estimateCostItems}
                    isEditing={!canMutate}
                    onChange={setEstimateCostItems}
                />
                {!canMutate && (
                    <Button
                        className="btn"
                        sx={{ mt: 2 }}
                        onClick={handleSaveEstimateCost}
                    >
                        Save
                    </Button>
                )}
            </TabPanel>

            <AlertDialog
                open={open}
                title={title}
                message={messageDialog}
                showConfirmButton={showConfirmButton}
                onClose={closeDialog}
                onConfirm={confirmDialog}
            />

            <GenericDialog
                open={openCloneNewVersion}
                title="Create a New Version" 
                children={
                    <TextField
                        select
                        fullWidth
                        variant="outlined"
                        value={selectedCloneVersion?.id ?? ""}
                        onChange={(e) => {
                            const version = versions?.find(v => v.id === Number(e.target.value));
                            setSelectedCloneVersion(version || null);
                        }}
                    >
                        {versions?.map((v: QuotationVersionData) => {
                            return (
                                <MenuItem
                                    key={v.id} 
                                    value={v.id}
                                >
                                    <Box
                                        sx={{
                                            display: "flex",
                                            justifyContent: "space-between",
                                            alignItems: "center",
                                            width: "100%"
                                        }}
                                    >
                                        <span>Version {v.version}</span>
                                        
                                        <Chip
                                            label={v.status}
                                            size="small"
                                            color={
                                                v.status === "draft" ? "default" : v.status === "submitted" ? "warning" : "success"
                                            }
                                        />
                                    </Box>
                                </MenuItem>
                            )
                        })}
                    </TextField>
                } 
                onClose={() => setOpenCloneNewVersion(false)} 
                onSubmit={handleCloneNewVersion} 
                submitText = "SUBMIT"
                cancelText = "CANCEL"
            />
        </>
    )
}

export default Quotation;