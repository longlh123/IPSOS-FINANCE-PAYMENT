import axios from "axios";
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";

import TablePagination from "@mui/material/TablePagination";
import {
  IconButton,
  TableCell,
  TableRow,
  TableBody,
  TableHead,
  Table,
  TableContainer,
  Paper,
  Button,
  TextField,
  Box,
  CircularProgress,
} from "@mui/material";
import SdCardAlertOutlinedIcon from '@mui/icons-material/SdCardAlertOutlined';
import DeleteIcon from "@mui/icons-material/Delete";
import EditIcon from "@mui/icons-material/Edit";
import { TableCellRespondentConfig } from "../../config/TableRespondentConfig";
import { ApiConfig } from "../../config/ApiConfig";
import useDialog from "../../hook/useDialog";
import AlertDialog from "../AlertDialog/AlertDialog";
import ModalAddRespondent from "../Modals/Respondent/ModalAddRespondent";
import RefreshIcon from "@mui/icons-material/Refresh";
import AddIcon from "@mui/icons-material/Add";
import ModalEditRespondent from "../Modals/Respondent/ModalEditRespondent";
import { VisibilityConfig } from "../../config/RoleConfig";
import { toProperCase } from "../../utils/format-text-functions";

interface TableRespondentsProps {
  project_id: string
}

const TableRespondents: React.FC<TableRespondentsProps> = ({project_id}) => {
  const navigate = useNavigate();
  const token = localStorage.getItem("authToken");
  
  const [statusMessage, setStatusMessage] = useState("");
  const [isError, setIsError] = useState<boolean>(false);
  const [loadingData, setLoadingData ] = useState<boolean>(true);

  const [ respondents, setRespondents ] = useState<any>([]); //data

  const [ openModelAdd, setOpenModalAdd ] = useState<boolean>(false);

  //Chọn số trang (pagination)
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  
  const handleChangePage = (event: any, newPage: number) => {
    setPage(newPage);
  };
  const handleChangeRowsPerPage = (event: any) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  useEffect(() => {
    setLoadingData(true);
    setIsError(false);
    setStatusMessage("");

    const fetchProjectData = async () => {
      try {
        const url = ApiConfig.respondent.viewRespondents.replace('{project_id}', project_id);
        
        const response = await axios.get(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });
        
        setRespondents(response.data.data);
      } catch (error) {
        if (axios.isAxiosError(error)) {
          setStatusMessage(error.response?.data.message ?? error.message);
          setIsError(true);
        } 
      } finally {
        setLoadingData(false);
      }
    };

    fetchProjectData();
  }, []);
  
  return (
    <>
      <Box className="box-table">
        <div className="filter">
          <h2>List</h2>
          <div className="">
            <Button className="btnAdd" onClick={() => setOpenModalAdd(true)}>
              Add New Respondent
            </Button>
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
                    {TableCellRespondentConfig.map((item, index) => {
                      return <TableCell key={index}>{item.label}</TableCell>;
                    })}
                    <TableCell></TableCell>
                  </TableRow>
                </TableHead>
  
                <TableBody>
                  {respondents
                    .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                    .map((respondent: any) => (
                      <TableRow key={respondent.id} className="table-row" hover={true}>
                        {TableCellRespondentConfig.map((item, index) => (
                          <TableCell key={item.name} className="table-cell">
                            {/* { 
                              item.label === 'Status' ? (
                                <div>
                                  <span className={"txt-status-project " + project.status.toLocaleLowerCase().replace(" ", "-")} >{toProperCase(project[item.name])}</span>
                                </div>
                              ) : ((item.label.length == 0 && item.type === 'image' ? (
                                <div className="icon-project"><img src={logo}></img></div>
                                
                              ) : (renderContent(item, project)))) 
                            } */}
                          </TableCell>
                        ))}
                      </TableRow>
                    ))}
                </TableBody>
              </Table>
              <TablePagination
                rowsPerPageOptions={[6, 10, 25]}
                component="div"
                count={respondents.length}
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
  )
};

export default TableRespondents;
