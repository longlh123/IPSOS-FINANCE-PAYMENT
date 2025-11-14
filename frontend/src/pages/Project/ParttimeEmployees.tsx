import { Paper } from "@mui/material";
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { useParams } from "react-router-dom";
import { useProjects } from "../../hook/useProjects";
import { useEffect, useState } from "react";
import { EmployeeData, TableParttimeEmployeesConfig } from "../../config/EmployeeFieldsConfig";

const ParttimeEmployees = () => {
    const { id } = useParams<{id: string}>();
    const { getEmployees } = useProjects();
    const [ employees, setEmployees ] = useState<EmployeeData[]>([]);
    const [formFieldsConfig, setFormFieldsConfig] = useState(TableParttimeEmployeesConfig);
 
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
        <>
            <Paper sx={{ height: 500, width: '100%', p: 2 }}>
                <DataGrid
                rows={employees}
                columns={formFieldsConfig}
                pageSizeOptions={[5, 10, 20]}
                initialState={{
                    pagination: { paginationModel: { pageSize: 5, page: 0 } },
                }}
                checkboxSelection
                sx={{
                    border: 0,
                    '& .MuiDataGrid-columnHeaders': { backgroundColor: '#f5f5f5' },
                }}
                />
            </Paper>
        </>
    )
}

export default ParttimeEmployees;