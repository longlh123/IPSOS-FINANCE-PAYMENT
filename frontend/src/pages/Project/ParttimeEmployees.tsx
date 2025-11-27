import { Alert, Box, Button, Paper, Snackbar } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { useParams } from "react-router-dom";
import { useProjects } from "../../hook/useProjects";
import { useEffect, useState } from "react";
import { EmployeeData, TableParttimeEmployeesConfig } from "../../config/EmployeeFieldsConfig";
import useDialog from "../../hook/useDialog";
import UniversalInputDialog from "../../components/Dialogs/UniversalInputDialog";
import AlertDialog from "../../components/AlertDialog/AlertDialog";
import { useVisibility } from "../../hook/useVisibility";
import SearchTextBox from "../../components/SearchTextBox";
import axios from "axios";

const ParttimeEmployees = () => {
    const { id } = useParams<{id: string}>();
    const { canView } = useVisibility();
    const { getEmployees, addEmployees } = useProjects();
    const [ employees, setEmployees ] = useState<EmployeeData[]>([]);
    const [ formFieldsConfig, setFormFieldsConfig] = useState(TableParttimeEmployeesConfig);
    const { open, title, message, showConfirmButton, openDialog, closeDialog, confirmDialog } = useDialog();
    const [ selectedEmployee, setSelectedEmployee ] = useState<EmployeeData | null>(null);
    
    const [ snackbarOpen, setSnackbarOpen ] = useState<boolean>(false);
    const [ snackbarMessage, setSnackbarMessage ] = useState<string>("");
    const [ isError, setIsError ] = useState<boolean>(false);

    const handleRemoveClick = (employee: EmployeeData) => {
        setSelectedEmployee(employee);
        openDialog({
            title: "Delete Employee",
            message: "Are you sure that you want to remove " + employee.employee_id + " from this project?",
            showConfirmButton: true
        });
    };

    const handleCancel = () => {
        closeDialog();
        setSelectedEmployee(null);
    }

    const handleConfirm = () => {
        if(!selectedEmployee || !id) return

        try{
            setEmployees(prev => prev.filter(emp => emp.employee_id !== selectedEmployee.employee_id));
        } catch(error){
            console.error('Failed to remove employee', error);
        }finally{
            closeDialog();
            setSelectedEmployee(null);
        }
    }

    const [openImportEmployeesDialog, setOpenImportEmployeesDialog ] = useState(false);
        
    const handleImportEmployeesCancel = () => {
        setOpenImportEmployeesDialog(false);
    }

    const handleImportEmployees = async (value: string) => {
        try{
            const employee_ids = value.split("\n").map(x => x.trim().replace(/[^a-zA-Z0-9]/g, "")).filter(Boolean);

            if (!id) {
                console.error("Project ID is undefined");
                setSnackbarMessage("Project ID is undefined");
                return;
            }

            const es = await addEmployees(parseInt(id), employee_ids.join(','));

            if(es.status_code == 400){
                setIsError(true);
                setSnackbarOpen(true);
                setSnackbarMessage(es.message);
            } else {
                setIsError(false);
                setEmployees(es); 
            }
        }catch(error){
            setIsError(true);
            setSnackbarOpen(true);

            if(axios.isAxiosError(error) && error.response?.status === 403){
                setSnackbarMessage(error.message);
            } else {
                setSnackbarMessage("Có lỗi xảy ra khi cập nhật danh sách phỏng vấn viên. Vui lòng thử lại.");
            }
        }finally{
            setOpenImportEmployeesDialog(false);
        }
    }

    const handleSearchChange = () => {

    }

    const columns = [
        ...formFieldsConfig,
        {
            field: 'actions',
            headerName: 'Actions',
            width: 150,
            sortable: false,
            filterable: false,
            renderCell: (params: any) => {
                const disabled = params.row.transaction_count < 0;

                return (
                    <Button
                        variant="outlined"
                        color="error"
                        size="small"
                        disabled={disabled}
                        onClick={() => handleRemoveClick(params.row)}
                    >
                    Remove
                    </Button>
                )
            }   
        }
    ];

    useEffect(() => {
        const fetchEmployees = async () => {
    
          if(id){
            const p = await getEmployees(parseInt(id));
            setEmployees(p);
          }
        }
    
        fetchEmployees()
    }, [id])
    
    return (
        <Box className="box-table">
            <div className="filter">
                <h2 className="filter-title">Projects</h2>
                <div className="filter-actions">
                {canView("employees.functions.visible_import_employees") && (
                    <Button className="btnAdd" onClick={() => setOpenImportEmployeesDialog(true)}>
                        Import New Employees
                    </Button>
                )}
                <SearchTextBox placeholder="Search id, name,..." onSearchChange={handleSearchChange} />
                </div>
            </div>
            <Snackbar
                open={snackbarOpen}
                autoHideDuration={10000}
                onClose={() => setSnackbarOpen(false)}
                anchorOrigin={{ vertical: "top", horizontal: "center" }}
                sx={{ zIndex: 9999 }}
            >
                <Alert severity= {isError ? "error" : "success"} onClose={() => setSnackbarOpen(false)}>
                    {snackbarMessage}
                </Alert>
                
            </Snackbar>
            <Paper sx={{ height: 500, width: '100%', p: 2 }}>
                <DataGrid
                    rows={employees}
                    columns={columns}
                    pageSizeOptions={[10, 20, 50]}
                    initialState={{
                        pagination: { paginationModel: { pageSize: 10, page: 0 } },
                    }}
                    checkboxSelection
                    sx={{
                        border: 0,
                        '& .MuiDataGrid-columnHeaders': { backgroundColor: '#f5f5f5' },
                    }}
                />
            </Paper>

            {/* Show ConfirmDialog */}
            <AlertDialog
                open={open}
                onClose={handleCancel}
                onConfirm={handleConfirm}
                showConfirmButton={showConfirmButton}
                title={title}
                message={message}
            />

            <UniversalInputDialog
                open={openImportEmployeesDialog}
                onClose={handleImportEmployeesCancel}
                onSubmit={handleImportEmployees}
                title="Add Employees"
                label="Import Employee IDs"
                placeholder="Paste codes here"
            />
        </Box>
    )
}

export default ParttimeEmployees;