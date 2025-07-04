import axios from "axios";
import { useState, useEffect } from "react";
import TablePagination from "@mui/material/TablePagination";
import "./Table.css";
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
  Box,
  Popover,
  List,
  ListItem,
  ListItemText,
  CircularProgress,
  Menu,
  MenuItem,
} from "@mui/material";
import { ApiConfig } from "../../config/ApiConfig";
import useDialog from "../../hook/useDialog";
import AlertDialog from "../AlertDialog/AlertDialog";
import ModalAddProject from "../Modals/Project/ModalAddProject";
import ModalEditProject from "../Modals/Project/ModalEditProject";
import RefreshIcon from "@mui/icons-material/Refresh";
import SearchIcon from "@mui/icons-material/Search";
import SdCardAlertOutlinedIcon from '@mui/icons-material/SdCardAlertOutlined';
import { TableCellConfig } from "../../config/TableProjectConfig";
import { VisibilityConfig } from "../../config/RoleConfig";
import SummarizeIcon from "@mui/icons-material/Summarize";
import ModalReport from "../Modals/Report/ModalReport";

import { IconDelete } from "../Icon/IconDelete";
import ModalConfirmDelete from "../Modals/Confirm/ModalConfirmDelete";
import CircleIcon from "@mui/icons-material/Circle";
import { useNavigate } from "react-router-dom";
import { formatDate } from "../../utils/DateUtils";
import logo from "../../assets/img/Ipsos logo.svg";
import { Upload as UploadIcon } from '@phosphor-icons/react/dist/ssr/Upload';
import ModalImportExcel from "../Modals/Project/ModalImportExcel";
import { toProperCase } from "../../utils/format-text-functions";
import MoreVertIcon from '@mui/icons-material/MoreVert';
import GiftIcon from '@mui/icons-material/CardGiftcard';
import { IconEdit } from "../Icon/IconEdit";
import { useAuth } from "../../contexts/AuthContext";
import { STATUS_COMPLETED } from "../../constants/statusProjectConstants";

const TableProject = () => {
  const navigate = useNavigate();

  const token = localStorage.getItem("authToken");
  
  const storedUser = localStorage.getItem('user');
  const user = storedUser ? JSON.parse(storedUser) : null;
  
  const visibilityConfig = VisibilityConfig[user.role as keyof typeof VisibilityConfig];

  const [statusMessage, setStatusMessage] = useState("");
  const [isError, setIsError] = useState<boolean>(false);
  const [loadingData, setLoadingData ] = useState<boolean>(true);

  const [ projects, setProjects ] = useState<any>([]); //data
  
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
        const response = await axios.get(ApiConfig.project.viewProjects, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
            'Show-Only-Enabled': '1',
          },
        });
        
        setProjects(response.data.data);
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

  // Define color mapping for each status
  const statusColors: { [key: string]: string } = {
    'planned': '#FFA500', // Orange
    'in coming': '#FFD700', // Gold
    'on going': '#00BFFF', // DeepSkyBlue
    'completed': '#32CD32', // LimeGreen
    'on hold': '#FF6347', // Tomato
    'cancelled': '#B22222', // FireBrick
  };

  // Define allowed transitions for each status
  const statusTransitions: { [key: string] : string[] } = {
    'planned' : ['in coming', 'cancelled'], 
    'in coming' : ['on going', 'on hold', 'cancelled'], 
    'on going' : ['completed', 'on hold', 'cancelled'], 
    'completed' : ['on going', 'on hold', 'cancelled'], 
    'on hold' : ['on going', 'completed', 'cancelled'], 
    'cancelled' : ['on going', 'on hold', 'cancelled']
  };

  const [anchorEl, setAnchorEl] = useState<HTMLElement | null>(null);
  const [anchorElStatus, setAnchorElStatus] = useState<HTMLElement | null>(null);
  const [selectedProject, setSelectedProject] = useState<any | null>(null);

  const opendStatusOfProject = Boolean(anchorEl);
  const idStatusOfProject = opendStatusOfProject ? "simple-popover" : undefined;

  const handleMenuStatusClose = () => {
    setAnchorElStatus(null);
    setSelectedProject(null);
  };
  
  const handleMenuStatusClick = (event: React.MouseEvent<HTMLElement>, project: any) => {
    setAnchorElStatus(event.currentTarget);
    setSelectedProject(project);
  };

  const handleMenuActionsClick = (event: React.MouseEvent<HTMLButtonElement>, project: any) => {
    setAnchorEl(event.currentTarget);
    setSelectedProject(project);
  };

  const handleMenuActionsClose = () => {
    setAnchorEl(null);
  };

  const handleAction = (action: string) => {
    handleMenuActionsClose();
    switch (action) {
      case 'gift_management':
        navigate(`/project-management/projects/${selectedProject.id}/gift-management`);
        break;
      case 'edit_project':
        navigate(`/project-management/projects/${selectedProject.id}`);
        break;
      case 'delete':
        setOpenModalConfirm(true);
        break;
      case 'report':
        handleOpenModalReport(selectedProject);
        break;
      default:
        break;
    }
  };

  const handleStatusChange = async (newStatus: string) => {
    if(selectedProject){
      try{
        // Make an API call to update the project status on the server
        const url = ApiConfig.project.updateStatusOfProject.replace('{project_id}', selectedProject.id);

        const response = await axios.put(url, { status: newStatus }, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });

        // Update the local state with the new status
        setProjects(projects.map((project: any) => 
          project.id === selectedProject.id ? { ...project, status: newStatus } : project
        ));

        handleMenuStatusClose();
      } catch (error) {
        console.log('Failed to update status', error);
      }
    }
  }

  const [ openImportExcelModal, setOpenImportExcelModal] = useState<boolean>(false);
  
  // const [role, setRole] = useState<string>("");
  const { open, openDialog, closeDialog } = useDialog();
  const [textDialog, setTextDialog] = useState({
    textHeader: "",
    textContent: "",
  });
  const [openModalAdd, setOpenModalAdd] = useState<boolean>(false);
  const [openModalEdit, setOpenModalEdit] = useState<boolean>(false);
  const [openModalReport, setOpenModalReport] = useState<boolean>(false);
  const [openModalConfirm, setOpenModalConfirm] = useState<boolean>(false);
  
  const handleCloseModal = () => {
    setOpenImportExcelModal(false);
    setOpenModalAdd(false);
    setOpenModalEdit(false);
    setOpenModalReport(false);
    setOpenModalConfirm(false);
  };

  const handleOpenModalReport = (project: any) => {
    setSelectedProject(project);
    setOpenModalReport(true);
  };
  
  const renderContent = (item: any, project: any) => {
    switch(true){
      case item.label === 'Status':
        return `<div>
                  <span className="status-options">${project[item.name]}</span>
                </div>`
      default:
        switch(true){
          case item.type === 'date':
            return formatDate(project[item.name]);
          case item.type === 'string':
            return project[item.name];
        }
    }
  }

  return (
    <>
      <Box className="box-table">
        <div className="filter">
          <h2>List</h2>
          <div className="">
            {visibilityConfig.projects.functions.visible_add_new_project && (
              <Button className="btnAdd" onClick={() => setOpenModalAdd(true)}>
                Add New Project
              </Button>
            )}
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
                    {TableCellConfig.map((item, index) => {
                      return <TableCell 
                              key={index}
                              align={ item.label === 'Status' ? 'center' : 'left' }
                              width={item.width}
                              >
                                {item.label}
                              </TableCell>;
                    })}
                    <TableCell sx={{
                      width: '50px',
                      textAlign: 'center'
                    }}>Status</TableCell>
                    <TableCell sx={{
                      width: '50px',
                      textAlign: 'center'
                    }}>Actions</TableCell>
                  </TableRow>
                </TableHead>
  
                <TableBody>
                  {projects
                    .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                    .map((project: any) => (
                      <TableRow key={project.id} className="table-row" hover={true}>
                        {TableCellConfig.map((item, index) => (
                          <TableCell 
                            key={item.name} 
                            className="table-cell"
                            width={item.width}
                            align={ item.label === 'Status' ? 'center' : 'left' }
                          >
                            { 
                              item.label === 'Status' ? (
                                <div>
                                  <span className={"txt-status-project " + project.status.toLocaleLowerCase().replace(" ", "-")} >{toProperCase(project[item.name])}</span>
                                </div>
                              ) : ((item.label.length == 0 && item.type === 'image' ? (
                                <div className="icon-project"><img src={logo}></img></div>
                              ) : (renderContent(item, project)))) 
                            }
                          </TableCell>
                        ))}
                        <TableCell
                          className="table-cell"
                          width={50}
                          align="center"
                        >
                          <IconButton
                            aria-label="status"
                            size="small"
                            onClick={(event) => handleMenuStatusClick(event, project)} style={{ cursor: 'pointer', background: 'transparent'}}
                          >
                            <div className={"box-status-button " + project.status.toLocaleLowerCase().replace(" ", "-")}>
                              <div>{toProperCase(project.status)}</div>
                            </div>
                          </IconButton>
                          <Popover
                            id={`status-popover-${selectedProject?.id}`}
                            open={visibilityConfig.projects.functions.visible_change_status_of_project && Boolean(anchorElStatus) && selectedProject?.id === project.id}
                            anchorEl={anchorElStatus}
                            onClose={handleMenuStatusClose}
                            anchorOrigin={{
                              vertical: "bottom",
                              horizontal: "left",
                            }}
                            transformOrigin={{
                              vertical: 'top',
                              horizontal: 'center',
                            }}
                          >
                            <List>
                              {statusTransitions[project.status].map((status_item, status_index) => (
                                <ListItem button key={status_index} onClick={
                                  () => {
                                    handleStatusChange(status_item);
                                  }
                                }>
                                  <ListItemText primary= {
                                    <div className="box-status-popover">
                                      <div className={"icon-status-project " + status_item.toLocaleLowerCase().replace(" ", "-")}></div>
                                      <div>{toProperCase(status_item)}</div>
                                      <>{console.log('Current Status:', status_item)}</>
                                      <>{console.log('Available Transitions:', toProperCase(status_item))}</>
                                    </div>
                                  }/>
                                </ListItem>
                              ))}
                            </List>
                          </Popover>
                        </TableCell>
                        <TableCell 
                          className="table-cell"
                          width={50}
                          align="center"
                        >
                          <IconButton
                            aria-label="actions"
                            onClick={(event) => handleMenuActionsClick(event, project)}
                            sx={{
                              backgroundColor: '#f6f6f6', 
                              borderRadius: '8px',
                              border: '1px solid #e8e8e8',
                              '&:hover': {
                                backgroundColor: '#e0e0e0',
                              },
                              padding: '5px',
                            }}
                          >
                            <MoreVertIcon />
                          </IconButton>
                          <Menu
                            anchorEl={anchorEl}
                            open={Boolean(anchorEl) && selectedProject?.id === project.id}
                            onClose={handleMenuActionsClose}
                          >
                            <MenuItem onClick={() => handleAction('edit_project')}>
                              {visibilityConfig.projects.functions.visible_edit_project && project.status !== STATUS_COMPLETED && (
                                <div className="actions-menu-item" style={{color: "rgb(99, 91, 255)"}}>
                                  <IconEdit /><span className="text">Edit Project</span>
                                </div>
                              )}
                            </MenuItem>
                            <MenuItem onClick={() => handleAction('gift_management')}>
                              {visibilityConfig.projects.functions.visible_view_gift_manager && (
                                <div className="actions-menu-item" style={{color: "var(--text-fifth-color)"}}>
                                  <GiftIcon fontSize="small" /><span className="text">Gift Management</span>
                                </div>
                              )}
                            </MenuItem>
                            <MenuItem onClick={() => handleAction('report')}>
                              <div className="actions-menu-item">
                                <SummarizeIcon fontSize="small" /><span className="text">View Report</span>
                              </div>
                            </MenuItem>
                          </Menu>
                        </TableCell>
                      </TableRow>
                    ))}
                </TableBody>
              </Table>
              <TablePagination
                rowsPerPageOptions={[10, 20, 50]}
                component="div"
                count={projects.length}
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
      
      {/* SHOW MODAL IMPORT EXCEL */}
      <ModalImportExcel openModal={openImportExcelModal} onClose={handleCloseModal} project={selectedProject} />

      {/* show Modal Add */}
      <ModalAddProject openModal={openModalAdd} onClose={handleCloseModal} />
      {/* show Modal edit */}
      <ModalEditProject
        openModal={openModalEdit}
        onClose={handleCloseModal}
        project={selectedProject}
      />
      {/* show Modal report */}
      <ModalReport
        openModal={openModalReport}
        onClose={handleCloseModal}
        reportValue={selectedProject}
      />
      {/* show dialog khi lỗi */}
      <AlertDialog
        openDialog={open}
        closeDialog={closeDialog}
        textHeader={textDialog.textHeader}
        textContent={textDialog.textContent}
      />
      <ModalConfirmDelete
        onClose={handleCloseModal}
        openModal={openModalConfirm}
      />
    </>
  );
};

export default TableProject;
