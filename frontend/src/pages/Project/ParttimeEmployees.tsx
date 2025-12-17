import { Alert, Box, Button, IconButton, Paper, Snackbar } from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { EmployeeCellConfig, EmployeeData } from "../../config/EmployeeFieldsConfig";
import useDialog from "../../hook/useDialog";
import UniversalInputDialog from "../../components/Dialogs/UniversalInputDialog";
import AlertDialog from "../../components/AlertDialog/AlertDialog";
import { useVisibility } from "../../hook/useVisibility";
import SearchTextBox from "../../components/SearchTextBox";
import axios from "axios";
import { useEmployees } from "../../hook/useEmployees";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";

const ParttimeEmployees = () => {
    const { id } = useParams<{id: string}>();
    const projectId = Number(id) || 0;

    const { employees, meta, total, page, setPage, rowsPerPage, setRowsPerPage, searchTerm, setSearchTerm, loading, error, message: messageEmployees, addEmployees } = useEmployees(projectId);

    const { canView } = useVisibility();
    
    const [ employeeCellConfig, setEmployeeCellConfig ] = useState(EmployeeCellConfig)
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
            // setEmployees(prev => prev.filter(emp => emp.employee_id !== selectedEmployee.employee_id));
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
        
        if (!id) return;

        setPage(0);

        const employee_ids = value.split("\n").map(x => x.trim().replace(/[^a-zA-Z0-9]/g, "")).filter(Boolean);
        
        await addEmployees(parseInt(id), employee_ids.join(','));

        setSnackbarOpen(error);
        setSnackbarMessage(messageEmployees);
        closeDialog();
    }

    const handleSearchChange = (value: string) => {
        setSearchTerm(value.toLocaleLowerCase());
    }

    const columns: ColumnFormat[] = [
        ...employeeCellConfig,
        {
            label: "Transactions",
            name: "transactions",
            type: "string",
            align: "center",
            flex: 1,
            renderHeader: () => (
                <div style={{ whiteSpace: "normal", lineHeight: 1.2, textAlign: "center" }}>
                    Transactions <br />
                    (Vinnet / Gotit / Refuse / Total)
                </div>
            ),
            renderCell: (row : any) => {
                return `${row.vinnet_total ?? 0} / ${row.gotit_total ?? 0} / ${row.other_total ?? 0} / ${row.transaction_total ?? 0}`;
            }
        },
        {
            label: "Actions",
            name: "actions",
            type: "menu",
            align: "center",
            flex: 1,
            renderAction: (row: any) => {
                const disabled = false;

                return (
                    <IconButton
                        color="error"
                        size="small"
                        disabled={disabled}
                        onClick={() => handleRemoveClick(row)}
                    >
                        <DeleteIcon />
                    </IconButton>
                )
            }
        }
    ];
    
    const handleChangePage = (event: any, newPage: number) => {
        setPage(newPage)
    }

    const handleChangeRowsPerPage = (event: any) => {
        setRowsPerPage(parseInt(event.target.value, 10));
        setPage(0);
    };

    return (
        <Box className="box-table">
            <div className="filter">
                <div className="filter-left">
                <h2 className="filter-title">Employees</h2>
                </div>
                <div className="filter-right">
                {canView("employees.functions.visible_import_employees") && (
                    <Button className="btn btn-primary" onClick={() => setOpenImportEmployeesDialog(true)}>
                        Import New Employees
                    </Button>
                )}
                </div>
            </div>
            <div className="filter">
                {/* LEFT: Add button */}
                <div className="filter-left">
                    
                </div>
    
                {/* RIGHT: Search + Date filter */}
                <div className="filter-right">
                    <SearchTextBox placeholder="Search id, name,..." onSearchChange={handleSearchChange} />
                </div>
            </div>
           
            <ReusableTable
                title="Employees"
                columns={columns}
                data={employees}
                loading={loading}
                error={error}
                message={message}
                page = {page}
                rowsPerPage = {rowsPerPage}
                total = {total}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage}
            ></ReusableTable>

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