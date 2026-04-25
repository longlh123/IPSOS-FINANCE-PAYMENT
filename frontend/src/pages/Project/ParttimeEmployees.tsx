import { Alert, Box, Button, Dialog, DialogActions, DialogContent, DialogTitle, FormControl, IconButton, InputLabel, MenuItem, Paper, Select, Snackbar } from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { EmployeeCellConfig, EmployeeData } from "../../config/EmployeeFieldsConfig";
import useDialog from "../../hook/useDialog";
import UniversalInputDialog from "../../components/Dialogs/UniversalInputDialog";
import AlertDialog from "../../components/AlertDialog/AlertDialog";
import { useVisibility } from "../../hook/useVisibility";
import SearchTextBox from "../../components/SearchTextBox";
import { useEmployees } from "../../hook/useEmployees";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { useProjects } from "../../hook/useProjects";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import Tooltip from '@mui/material/Tooltip';
import DriveEtaIcon from '@mui/icons-material/DriveEta';

const formatProjectTypes = (project: ProjectData | null) => {
    const projectWithLegacyTypes = project as (ProjectData & { project_project_types?: Array<string | { name?: string }> }) | null;
    const rawTypes = projectWithLegacyTypes?.project_project_types ?? project?.project_types ?? [];

    if (!Array.isArray(rawTypes) || rawTypes.length === 0) {
        return "-";
    }

    const normalizedTypes = rawTypes
        .map((typeItem) => {
            if (typeof typeItem === "string") {
                return typeItem;
            }

            return typeItem?.name ?? "";
        })
        .filter(Boolean);

    return normalizedTypes.length > 0 ? normalizedTypes.join(", ") : "-";
};


const ParttimeEmployees = () => {
    const { id } = useParams<{id: string}>();
    const projectId = Number(id) || 0;
    const [ projectSelected, setProjectSelected ] = useState<ProjectData | null>(null);

    const { getProject } = useProjects();
    const { employees, meta, total, page, setPage, rowsPerPage, setRowsPerPage, searchTerm, setSearchTerm, sortBy, setSortBy, sortDirection, setSortDirection, loading, error: errorEmployees, message: messageEmployees, addEmployees, removeEmployee, addEmployeeToTravelExpense } = useEmployees(projectId);
    const employeeRows = employees as Array<EmployeeData & { transaction_total?: number }>;
    
    const { canView } = useVisibility();
    const projectTypesDisplay = formatProjectTypes(projectSelected);
    
    const [ employeeCellConfig, setEmployeeCellConfig ] = useState(EmployeeCellConfig)
    const { open, title, message, showConfirmButton, openDialog, closeDialog, confirmDialog } = useDialog();
    const [ selectedEmployee, setSelectedEmployee ] = useState<EmployeeData | null>(null);
    const [ selectedRowIds, setSelectedRowIds ] = useState<Array<number | string>>([]);
    const [ bulkDeleteEmployeeIds, setBulkDeleteEmployeeIds ] = useState<number[]>([]);
    const [ openTravelProvinceDialog, setOpenTravelProvinceDialog ] = useState(false);
    const [ selectedTravelEmployees, setSelectedTravelEmployees ] = useState<EmployeeData[]>([]);
    const [ selectedProvinceId, setSelectedProvinceId ] = useState<number | "">("");

    const selectedRows = employeeRows.filter((row) => selectedRowIds.includes(row.id));
    const selectedRowsCount = selectedRows.length;
    const canBulkDelete = selectedRowsCount > 0 && selectedRows.every((row) => Number(row.transaction_total ?? 0) === 0);
    const canBulkAddToTravel = selectedRowsCount > 0;
    
    const handleRemoveClick = (employee: EmployeeData) => {
        setBulkDeleteEmployeeIds([]);
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

    const handleConfirm = async () => {
        if(!id) return;

        try {
            if (bulkDeleteEmployeeIds.length > 0) {
                const results = await Promise.allSettled(
                    bulkDeleteEmployeeIds.map((employeeId) => removeEmployee(parseInt(id), employeeId))
                );

                const successCount = results.filter((result) => result.status === "fulfilled").length;
                const failedCount = results.length - successCount;

                closeDialog();

                if (failedCount === 0) {
                    openDialog({
                        title: "Bulk delete completed",
                        message: `Deleted ${successCount} employee(s) successfully.`,
                        showConfirmButton: false
                    });
                } else {
                    openDialog({
                        title: "Bulk delete completed",
                        message: `Deleted ${successCount} employee(s), failed ${failedCount} employee(s).`,
                        showConfirmButton: false
                    });
                }

                setSelectedRowIds([]);
                setBulkDeleteEmployeeIds([]);
                setSelectedEmployee(null);
                return;
            }

            if (selectedEmployee) {
                await removeEmployee(parseInt(id), selectedEmployee.id);
                closeDialog();
                setSelectedEmployee(null);
                setBulkDeleteEmployeeIds([]);
            }
        } catch(error){
            console.error('Failed to remove employee', error);
        }
    }

    const [openImportEmployeesDialog, setOpenImportEmployeesDialog ] = useState(false);
        
    const handleImportEmployeesCancel = () => {
        setOpenImportEmployeesDialog(false);
    }

    const handleImportEmployees = async (value: string) => {
        
        if (!id || !value){
            setOpenImportEmployeesDialog(false);
            return;
        } 
        
        setPage(0);

        const employee_ids = value.split("\n").map(x => x.trim().replace(/[^a-zA-Z0-9]/g, "")).filter(Boolean);
        
        await addEmployees(parseInt(id), employee_ids.join(','));

        setOpenImportEmployeesDialog(false);
    }

    const handleSearchChange = (value: string) => {
        setSearchTerm(value.toLocaleLowerCase());
        setPage(0);
    }

    const handleSortChange = (nextSortBy: string, nextDirection: "asc" | "desc") => {
        setSortBy(nextSortBy);
        setSortDirection(nextDirection);
        setPage(0);
    }

    const handleAddToTravelListClick = async (employee: EmployeeData) => {
        if (!id || !projectSelected?.provinces || projectSelected.provinces.length === 0) {
            return;
        }

        setSelectedTravelEmployees([employee]);
        setSelectedProvinceId("");
        setOpenTravelProvinceDialog(true);
    }

    const handleBulkAddToTravelListClick = () => {
        if (!id || !projectSelected?.provinces || projectSelected.provinces.length === 0 || !canBulkAddToTravel) {
            return;
        }

        setSelectedTravelEmployees(selectedRows);
        setSelectedProvinceId("");
        setOpenTravelProvinceDialog(true);
    }

    const handleBulkDeleteClick = () => {
        if (!canBulkDelete || !id) return;

        const ids = selectedRows.map((row) => row.id);

        setBulkDeleteEmployeeIds(ids);
        setSelectedEmployee(null);
        openDialog({
            title: "Delete Selected Employees",
            message: `Are you sure that you want to remove ${ids.length} selected employee(s) from this project?`,
            showConfirmButton: true
        });
    }

    const handleCloseTravelProvinceDialog = () => {
        setOpenTravelProvinceDialog(false);
        setSelectedTravelEmployees([]);
        setSelectedProvinceId("");
    }

    const handleConfirmAddToTravelList = async () => {
        if (!id || selectedTravelEmployees.length === 0 || selectedProvinceId === "") return;

        try {
            const results = await Promise.allSettled(
                selectedTravelEmployees.map((employee) =>
                    addEmployeeToTravelExpense(parseInt(id), employee.id, Number(selectedProvinceId))
                )
            );

            const successCount = results.filter((result) => result.status === "fulfilled").length;
            const failedCount = results.length - successCount;

            if (failedCount === 0) {
                openDialog({
                    title: "Bulk add completed",
                    message: `Added ${successCount} employee(s) to travel list successfully.`,
                    showConfirmButton: false
                });
            } else {
                openDialog({
                    title: "Bulk add completed",
                    message: `Added ${successCount} employee(s), failed ${failedCount} employee(s).`,
                    showConfirmButton: false
                });
            }

            setSelectedRowIds([]);
            handleCloseTravelProvinceDialog();
        } catch (error) {
            console.error('Failed to add employee to travel expense list', error);
        }
    }

    const columns: ColumnFormat[] = [
        ...employeeCellConfig.map((col) => {
            if (col.name === "employee_id") {
                return {
                    ...col,
                    sortable: true,
                    sortKey: "employee_id"
                };
            }

            if (col.name === "full_name") {
                return {
                    ...col,
                    sortable: true,
                    sortKey: "full_name"
                };
            }

            return {
                ...col,
                sortable: false
            };
        }),
        {
            label: "Transactions",
            name: "transaction_total",
            sortKey: "transaction_total",
            sortable: true,
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
                const transactionTotal = Number(row.transaction_total ?? 0);
                const deleteDisabled = loading || transactionTotal > 0;
                const addToTravelDisabled = loading;

                return (
                    <>
                        <Tooltip title="Delete">
                            <span>
                                <IconButton
                                    color="error"
                                    size="small"
                                    disabled={deleteDisabled}
                                    onClick={() => handleRemoveClick(row)}
                                >
                                    <DeleteIcon />
                                </IconButton>
                            </span>
                        </Tooltip>
                        <Tooltip title="Add to travel list">
                            <span>
                                <IconButton
                                    color="primary"
                                    size="small"
                                    disabled={addToTravelDisabled}
                                    onClick={() => handleAddToTravelListClick(row)}
                                >
                                    <DriveEtaIcon />
                                </IconButton>
                            </span>
                        </Tooltip>
                    </>
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

    useEffect(() => {
        async function fetchProject(){
            try{
                const p = await getProject(projectId);
                setProjectSelected(p);
            }catch(error){
                console.log(error);
            }
        }
        
        fetchProject();
    }, [projectId]);

    useEffect(() => {
        setSelectedRowIds([]);
    }, [page, rowsPerPage, searchTerm, projectId]);

    return (
        <Box className="box-table">
            <div className="filter">
                <div className="filter-left">
                    <div className="project-info">
                        <div>
                            <strong>Project Name:</strong> {projectSelected?.project_name}
                        </div>
                        <div>
                            <strong>Symphony:</strong> {projectSelected?.symphony}
                        </div>
                        <div>
                            <strong>Project Type:</strong> {projectTypesDisplay}
                        </div>
                    </div>
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
                data={employeeRows}
                loading={loading}
                error={errorEmployees}
                message={messageEmployees}
                page = {page}
                rowsPerPage = {rowsPerPage}
                total = {total}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage}
                rowSelectionEnabled={true}
                selectedRowIds={selectedRowIds}
                onSelectedRowIdsChange={setSelectedRowIds}
                getRowId={(row: EmployeeData) => row.id}
                sortBy={sortBy}
                sortDirection={sortDirection}
                onSortChange={handleSortChange}
                topToolbar={
                    selectedRowsCount > 0 ? (
                        <Box sx={{ display: "flex", gap: 1, alignItems: "center", p: 1.5, borderBottom: "1px solid #eee", flexWrap: "wrap" }}>
                            <Box sx={{ fontWeight: 600, mr: 1 }}>{selectedRowsCount} selected</Box>
                            <Button
                                color="error"
                                variant="outlined"
                                disabled={loading || !canBulkDelete}
                                onClick={handleBulkDeleteClick}
                            >
                                Delete selected
                            </Button>
                            <Button
                                color="primary"
                                variant="contained"
                                disabled={loading || !canBulkAddToTravel}
                                onClick={handleBulkAddToTravelListClick}
                            >
                                Add selected to travel list
                            </Button>
                            <Button
                                variant="text"
                                onClick={() => setSelectedRowIds([])}
                                disabled={loading}
                            >
                                Clear selection
                            </Button>
                        </Box>
                    ) : null
                }
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

            <Dialog
                open={openTravelProvinceDialog}
                onClose={handleCloseTravelProvinceDialog}
                fullWidth
                maxWidth="xs"
            >
                <DialogTitle>Select province/city</DialogTitle>
                <DialogContent>
                    <Box sx={{ mb: 1, fontSize: 14, color: "text.secondary" }}>
                        Applying to {selectedTravelEmployees.length} employee(s)
                    </Box>
                    <FormControl fullWidth margin="dense" size="small">
                        <InputLabel id="travel-province-select-label">Province/City</InputLabel>
                        <Select
                            labelId="travel-province-select-label"
                            value={selectedProvinceId}
                            label="Province/City"
                            onChange={(event) => setSelectedProvinceId(event.target.value as number)}
                        >
                            {(projectSelected?.provinces ?? []).map((province) => (
                                <MenuItem key={province.id} value={province.id}>
                                    {province.name}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleCloseTravelProvinceDialog}>Cancel</Button>
                    <Button
                        variant="contained"
                        onClick={handleConfirmAddToTravelList}
                        disabled={selectedProvinceId === "" || loading}
                    >
                        Add
                    </Button>
                </DialogActions>
            </Dialog>
        </Box>
    )
}

export default ParttimeEmployees;