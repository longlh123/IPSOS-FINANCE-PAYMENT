import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { useProjects } from "../../hook/useProjects";
import { Button, Grid, TextField, Typography } from "@mui/material";
import SecretTextBox from "../../components/SecretTextBox";
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';

interface SettingsData {
    internal_code: string,
    project_name: string
}

const Settings = () => {
    const { id } = useParams<{ id: string }>();
    const projectId = Number(id) || 0;

    const [ selectedProject, setSelectedProject ] = useState<ProjectData | null>(null);
    const { getProject } = useProjects();

    const [ isEditing, setIsEditing ] = useState<boolean>(false);

    const handleSave = () => {
        
    }

    useEffect(() => {
        const fetchProject = async () => {
            try{
                const p = await getProject(projectId);
                setSelectedProject(p);
            }catch(error){
                console.log(error)
            }
        }

        fetchProject();
    }, [projectId]);

    return (
        <Grid container spacing={3}>
            <Grid item xs={12} sm={6}>
                <div style={{ marginBottom: "1rem" }}>
                    <Typography variant="body2" gutterBottom>
                        Internal Code
                    </Typography>
                    <TextField
                        fullWidth
                        size="small"
                        type="string"
                        value={selectedProject?.internal_code}
                        variant="outlined"
                        disabled={!isEditing}
                    />
                </div>
            </Grid>
            <Grid item xs={12} sm={6}>
                <div style={{ marginBottom: "1rem" }}>
                    <Typography variant="body2" gutterBottom>
                        Project Name
                    </Typography>
                    <TextField
                        fullWidth
                        size="small"
                        type="string"
                        value={selectedProject?.project_name}
                        variant="outlined"
                        disabled={!isEditing}
                    />
                </div>
            </Grid>
            <Grid item xs={12}>
                <SecretTextBox label="Token" value={selectedProject?.remember_token || ""} />
            </Grid>
            <Grid item xs={12}>
                <Button
                    variant="contained"
                    startIcon={!isEditing ? <EditOutlinedIcon /> : <SaveOutlinedIcon />} 
                    sx={{mt: 3}}
                    onClick={() => {
                        if(!isEditing){
                            setIsEditing(true);
                        } else {
                            handleSave();
                        }
                    }}
                >
                    {!isEditing ? "Edit" : "Save"}
                </Button>
            </Grid>
        </Grid>
    )
};

export default Settings;