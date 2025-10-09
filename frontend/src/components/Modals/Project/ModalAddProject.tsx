import {
  Alert,
  Box,
  Button,
  Divider,
  FormControl,
  Grid,
  MenuItem,
  Modal,
  Select,
  TextField,
  Typography,
} from "@mui/material";
import "../../../assets/css/modal.css";
import { useEffect, useState } from "react";
import axios from "axios";
import { SelectChangeEvent } from "@mui/material/Select";
import { useNavigate } from "react-router-dom";
import { ColumnFormat } from "../../../config/ColumnConfig";
import { ProjectData } from "../../../config/ProjectFieldsConfig";
import { ProjectGeneralFieldsConfig } from "../../../config/ProjectFieldsConfig";
import { useProjects } from "../../../hook/useProjects";

interface ModalProps {
  openModal: boolean;
  onClose: () => void;
  metadata: {
    project_types: { id: number; name: string }[];
    departments: { id: number; name: string }[];
    teams: { id: number; name: string}[];
  }
}

const ModalAddProject: React.FC<ModalProps> = ({ openModal, onClose, metadata }) => {
  const navigate = useNavigate();
  
  const [statusMessage, setStatusMessage] = useState<string>("");
  const [isError, setIsError] = useState<boolean>(false);

  const { addProject } = useProjects();

  const [formFieldsConfig, setFormFieldsConfig] = useState(ProjectGeneralFieldsConfig);
  const [formValues, setFormValues] = useState<ProjectData>({
    internal_code: '',
    symphony: '',
    project_name: '',
    platform: '',
    teams: [],
    project_types: [],
    planned_field_start: '',
    planned_field_end: ''
  });
  
  useEffect(() => {
    if(metadata){
      setFormFieldsConfig((fieldsConfig) => 
        fieldsConfig.map((field) => {
          if(field.name === "project_types"){
            return {
              ...field,
              options: metadata.project_types.map((pt) => ({
                value: pt.id,
                label: pt.name
              })),
            };
          } else if(field.name === "departments"){
            return {
              ...field,
              options: metadata.departments.map((d) => ({
                value: d.id,
                label: d.name
              }))
            }
          } else if(field.name === "teams"){
            return{
              ...field,
              options: metadata.teams.map((team) => ({
                value: team.id,
                label: team.name
              }))
            }
          }
          return field;
        })
      )
    }
  }, [metadata]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    
    setFormValues({
      ...formValues,
      [name]: value
    });
  };

  const handleSelectChange = (e: SelectChangeEvent<string | string[]>) => {
    const { name, value } = e.target;

    if(name === 'platform'){
      setFormValues({
        ...formValues,
        platform : value as string
      });
    } else {
      setFormValues({
        ...formValues,
        [name]: Array.from(value)
      });
    }
  };

  const renderField = (
    column: ColumnFormat
  ) => {
    
    if (column.type === "string" || column.type === "number" || column.type === "date") {
      return (
        <TextField
          name={column.name}
          className="textfield-add"
          type={column.type === "string" ? "text" : column.type}
          placeholder={column.label}
          value={ formValues[column.name as keyof typeof formValues] }
          onChange={handleInputChange}
        />
      );
    } else if (column.type === "select") { //&& column.options?.length > 0
      const isMultiSelect = column.name === 'teams' || column.name === 'project_types';
      const currentValue = formValues[column.name as keyof typeof formValues];

      return (
        <FormControl fullWidth>
          <Select
            name={column.name}
            multiple={isMultiSelect}
            labelId={`${column.name}-label`}
            id={column.name}
            value={isMultiSelect ? (currentValue as string[]) : (currentValue as string)}
            onChange={handleSelectChange}
          >
            {column.options?.map((option, index) => (
              <MenuItem key={index} value={option.value}>
                {option.label}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      );
    } else {
      return null;
    }
  };
  
  const handleSave = async () => {
    try {
      const new_project = await addProject(formValues);
      console.log("Add successful");
      onClose();
      navigate(`/project-management/projects/${new_project.id}/settings`);
    } catch (error) {
      if (axios.isAxiosError(error)) {
        setStatusMessage(error.response?.data.message ?? error.message);
        setIsError(true);
      }
    }
  };

  return (
      <Modal
        open={openModal}
        onClose={onClose}
        aria-labelledby="modal-modal-title"
        aria-describedby="modal-modal-description"
      >
        <Box className="modal-box">
          <Typography
            id="modal-modal-title"
            variant="h6"
            component="h2"
            textAlign="center"
          >
            Add New Project
          </Typography>
          <Divider />
          <div className="error-control">
            {isError && (
              <Alert severity="error">{statusMessage}</Alert> 
            )}
          </div>
          <Grid
            container
            rowGap={3}
            columnSpacing={3}
            className="content-modal"
          >
            {formFieldsConfig.map((column, index) => (
              <Grid key={index} item xs={12}>
                <Grid container alignItems="center">
                  <Grid item xs={2}>
                    <Typography sx={{ fontWeight: "500", fontSize: "14px" }}>
                      {column.label}:
                    </Typography>
                  </Grid>
                  <Grid item xs={10}>
                    {renderField(column)}
                  </Grid>
                </Grid>
              </Grid>
            ))}
          </Grid>
          <Box className="btn-modal-footer" textAlign="end">
            <Button className="btn-modal-cancel" onClick={onClose}>
              Cancel
            </Button>
            <Button
              className="btn-modal-submit"
              variant="contained"
              onClick={handleSave}
            >
              Save
            </Button>
          </Box>
        </Box>
      </Modal>
  );
};

export default ModalAddProject;
