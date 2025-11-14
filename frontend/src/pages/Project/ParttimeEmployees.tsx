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
    

    interface Interviewer {
        id: number;
        name: string;
        code: string;
        email: string;
        region: string;
        status: string;
        joined_at: string;
    }

    const rows: Interviewer[] = [
        { id: 1, name: 'Nguyễn Văn A', code: 'HN001', email: 'a@ipsos.com', region: 'Hà Nội', status: 'Đang hoạt động', joined_at: '2024-10-01' },
        { id: 2, name: 'Trần Thị B', code: 'HCM014', email: 'b@ipsos.com', region: 'Hồ Chí Minh', status: 'Tạm nghỉ', joined_at: '2024-09-20' },
        { id: 3, name: 'Lê Văn C', code: 'DN032', email: 'c@ipsos.com', region: 'Đà Nẵng', status: 'Đang hoạt động', joined_at: '2024-10-12' },
    ];

    const columns: GridColDef[] = [
        { field: 'code', headerName: 'Mã PVV', flex: 1 },
        { field: 'name', headerName: 'Họ và tên', flex: 1.5 },
        { field: 'email', headerName: 'Email', flex: 1.5 },
        { field: 'region', headerName: 'Khu vực', flex: 1 },
        { field: 'status', headerName: 'Trạng thái', flex: 1 },
        { field: 'joined_at', headerName: 'Ngày tham gia', flex: 1 },
    ];
    
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