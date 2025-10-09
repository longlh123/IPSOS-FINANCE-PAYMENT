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
import { TableCellRespondentConfig } from "../../config/TableRespondentConfig";
import { ApiConfig } from "../../config/ApiConfig";
import ModalAddProject from "../Modals/Project/ModalAddProject";

const TableRespondents: React.FC = () => {
  const [openModalAdd, setOpenModalAdd] = useState<boolean>(false);

  const handleCloseModal = () => {
    setOpenModalAdd(false)
  }
  
  return (
    <>
      {/* <Box className="box-table">
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
                            { { 
                              item.label === 'Status' ? (
                                <div>
                                  <span className={"txt-status-project " + project.status.toLocaleLowerCase().replace(" ", "-")} >{toProperCase(project[item.name])}</span>
                                </div>
                              ) : ((item.label.length == 0 && item.type === 'image' ? (
                                <div className="icon-project"><img src={logo}></img></div>
                                
                              ) : (renderContent(item, project)))) 
                            } }
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

      <ModalAddProject openModal={openModalAdd} onClose={handleCloseModal} metadata={metadata} /> */ }
    </>
  )
};

export default TableRespondents;
