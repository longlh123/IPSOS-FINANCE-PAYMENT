import axios from "axios";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Box, Button, Divider, Grid, InputLabel, TextField, Typography } from "@mui/material";

import { ProjectData, ProjectObject } from "../../config/ProjectDataConfig";
import Directional from "../../components/Directional/Directional";
import { ApiConfig } from "../../config/ApiConfig";

const EditProject = () => {
  const { id } = useParams<{ id: string }>();
  const [loading, setLoading] = useState<boolean>(true);
  const [statusMessage, setStatusMessage] = useState("");
  const [isError, setIsError] = useState<boolean | null>(null);

  const [project, setProject] = useState<ProjectObject | null>(null);

  const [formValues, setFormValues] = useState<ProjectData>({
    internal_code: '',
    project_name: '',
    platform: '',
    teams: [],
    project_types: [],
    planned_field_start: '',
    planned_field_end: ''
  });

  useEffect(() => {
    const fetchProject = async () => {
      setLoading(true);
      setStatusMessage('');
      setIsError(false);

      try 
      {
        const token = localStorage.getItem("authToken");
        
        const url = `${ApiConfig.project.viewProjects + "/" + id + '/show'}`;

        const response = await axios.get(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });
          
        setProject(response.data.data);
      } 
      catch (error) 
      {
        let errorMessage = '';

        if (axios.isAxiosError(error)) {
            errorMessage = error.response?.data.message ?? error.message;
        } else {
            errorMessage = (error as Error).message;
        }

        setStatusMessage(errorMessage);
        console.error('Error:', errorMessage);
      }
      finally
      {
        setLoading(false);
      }
    };

    fetchProject();
  }, [id, project]);

  // const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
  //   // const { name, value } = e.target;
  //   // if (project) {
  //   //   setProject({ ...project, [name]: value });
  //   //   const updatedFieldValues = fieldValues.map((field) =>
  //   //     field.name === name ? { ...field, value } : field
  //   //   );
  //   //   setFieldValues(updatedFieldValues);
  //   }
  // };

  // const handleSubmit = async () => {
  //   // setSubmitting(true);
  //   // try {
  //   //   const response = await fetch(`${ApiConfig.project.editProject}/${id}`, {
  //   //     method: "PUT",
  //   //     headers: {
  //   //       "Content-Type": "application/json",
  //   //     },
  //   //     body: JSON.stringify(project),
  //   //   });
  //   //   if (response.ok) {
  //   //     setTextDialog({
  //   //       textHeader: "Success",
  //   //       textContent: "Project updated successfully!",
  //   //     });
  //   //     openDialog();
  //   //     // Optionally re-fetch the updated project data
  //   //     const updatedProject = await response.json();
  //   //     setProject(updatedProject);
  //   //   } else {
  //   //     openDialog();
  //   //     setTextDialog({
  //   //       textHeader: "API Error",
  //   //       textContent: "Please wait for a response",
  //   //     });
  //   //   }
  //   // } catch (err) {
  //   //   console.error(err);
  //   //   openDialog();
  //   //   setTextDialog({
  //   //     textHeader: "API Error",
  //   //     textContent: "Please wait for a response",
  //   //   });
  //   // } finally {
  //   //   setSubmitting(false);
  //   // }
  // };

  return (
    <>
      <Directional title="Edit Project" />
      <Box
        className="container"
        sx={{ backgroundColor: "#FFF", padding: "20px", borderRadius: "10px" }}
      >
        <Grid container justifyContent="space-between" spacing={4}>
          <Grid item xs={6}>
            <InputLabel htmlFor="bac">
              Project Name
            </InputLabel>
            <TextField
              id="bac"
              name="project_name"
              value={"fasdf"}
              // onChange={handleInputChange}
              fullWidth
              margin="normal"
            />
          </Grid>
          {/* {fieldValues.map((item, index) => (
            <>
              <Grid item xs={6}>
                <Grid container spacing={1} alignItems="center">
                  <Grid item xs={3}>
                    <Typography sx={{ fontWeight: "500", fontSize: "14px" }}>
                      {item.name}
                    </Typography>
                  </Grid>
                  <Grid item xs={9}>
                    <TextField
                      className="textfield-add"
                      value={item.value || ""}
                      onChange={handleInputChange}
                    />
                  </Grid>
                </Grid>
              </Grid>
            </>
          ))} */}
        </Grid>
        <Divider sx={{ margin: "20px 0" }} />
        <h3 style={{ fontFamily: "var(--main-font)" }}>Provinces</h3>

        <Box className="btn-modal-footer" textAlign="end">
          <Button className="btn-modal-cancel">Cancel</Button>
          <Button className="btn-modal-submit" variant="contained">
            Save
          </Button>
        </Box>
      </Box>
    </>
  );
};

export default EditProject;
