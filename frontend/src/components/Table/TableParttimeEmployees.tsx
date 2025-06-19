import * as React from 'react';
import { useState, useEffect } from 'react';
import axios from 'axios';

import {
  Box,
  TableCell,
  TableRow,
  TableBody,
  TableHead,
  Table,
  TableContainer,
  TablePagination,
  Paper,
  CircularProgress,
  Avatar,
} from "@mui/material";

import SdCardAlertOutlinedIcon from '@mui/icons-material/SdCardAlertOutlined';

import { ApiConfig } from '../../config/ApiConfig';
import { useSelection } from '../../hook/useSelection';
import { VisibilityConfig } from '../../config/RoleConfig';
import { EmployeeData, TableCellParttimeEmployeesConfig } from '../../config/TableParttimeEmployeesConfig';
import { formatDate } from '../../utils/DateUtils';
import { toProperCase } from '../../utils/format-text-functions';
import numeral from 'numeral';

interface TableParttimeEmployeesProps {
  project_id: string
}

const TableParttimeEmployees: React.FC<TableParttimeEmployeesProps> = ({project_id}) => {
  
  const avatarColors = [
    'var(--avatar-primary-color)',
    'var(--avatar-secondary-color)',
    'var(--avatar-third-color)',
    'var(--avatar-forth-color)',
    'var(--avatar-fifth-color)',
    'var(--avatar-sixth-color)',
    'var(--avatar-seventh-color)',
    'var(--avatar-eighth-color)',
    'var(--avatar-ninth-color)',
    'var(--avatar-tenth-color)',
  ];
  
  const token = localStorage.getItem("authToken");
  
  const storedUser = localStorage.getItem('user');
  const user = storedUser ? JSON.parse(storedUser) : null;
  
  const visibilityConfig = VisibilityConfig[user.role as keyof typeof VisibilityConfig];

  const [statusMessage, setStatusMessage] = useState("");
  const [isError, setIsError] = useState<boolean>(false);
  const [loadingData, setLoadingData ] = useState<boolean>(true);

  const [employees, setEmployees] = useState<EmployeeData[]>([]);

  useEffect(() => {
    setLoadingData(true);

    const fetchEmployeesData = async () => {
      try {
        const url = ApiConfig.employee.viewEmployees.replace('{project_id}', project_id);
        
        const response = await axios.get(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });
        
        setEmployees(response.data.data);
      } catch (error) {
        if (axios.isAxiosError(error)) {
          setStatusMessage(error.response?.data.message ?? error.message);
          setIsError(true);
        } 
      } finally {
        setLoadingData(false);
      }
    };
    
    fetchEmployeesData();
  }, []);

  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);

  const handleChangePage = (event: any, newPage: number) => {
    setPage(newPage);
  };

  const handleChangeRowsPerPage = (event: any) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  const renderContent = (item: any, employee: any) => {
    switch(true){
      case item.label === 'Status':
        return `<div>
                  <span className="status-options">${employee[item.name]}</span>
                </div>`
      default:
        switch(true){
          case item.type === 'date':
            return formatDate(employee[item.name]);
          case item.type === 'string':
            return employee[item.name];
          case item.type === 'number':
            return numeral(employee[item.name]).format('0,000');
        }
    }
  }

  return (
    <>
      <Box className="box-table">
        <div className="filter">
          <h2>List</h2>
          <div className="">
            {/* <Button className="btnAdd" onClick={() => setOpenModalAdd(true)}>
              Add New Respondent
            </Button> */}
          </div>
        </div>
        
        {isError ? (
          <Box display="flex" justifyContent="center" alignItems="center" height="100%">
            <SdCardAlertOutlinedIcon />
            <div>{statusMessage}</div>
          </Box>
        ) : (
          loadingData ? (
            <Box display="flex" justifyContent="center" alignItems="center" height="100%">
              <CircularProgress />
            </Box>
          ) : (
            <TableContainer component={Paper} className="table-container">
              <Table>
                <TableHead>
                  <TableRow className="header-table">
                    {TableCellParttimeEmployeesConfig.map((item, index) => {
                      return <TableCell 
                                key={index}
                                align={item.type === 'number' ? 'center' : 'left'}
                                width={item.width}
                            >
                              {item.label}
                            </TableCell>;
                    })}
                    <TableCell></TableCell>
                  </TableRow>
                </TableHead>
  
                <TableBody>
                  {employees
                    .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                    .map((employee: EmployeeData) => (
                      <TableRow key={employee.id} className="table-row" hover={true}>
                        {TableCellParttimeEmployeesConfig.map((item, index) => (
                          <TableCell 
                            key={item.name} 
                            className="table-cell"
                            align={item.type === 'number' ? 'center' : 'left'}
                          >
                            { 
                              (item.label.length == 0 && item.type === 'image' ? (
                                <Avatar sx={{ width: 32, height: 32, backgroundColor: avatarColors[parseInt(employee.id) % 10] }}>{employee.first_name?.charAt(0)}</Avatar>
                                
                              ) : (renderContent(item, employee)))
                            }
                          </TableCell>
                        ))}
                      </TableRow>
                    ))}
                </TableBody>
              </Table>
              <TablePagination
                rowsPerPageOptions={[10, 20, 50]}
                component="div"
                count={employees.length}
                rowsPerPage={rowsPerPage}
                page={page}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage}
                sx={{ color: "var(--text-color)" }}
              />
            </TableContainer>
          )
        )}
      </Box>
    </>
  );
}

export default TableParttimeEmployees;