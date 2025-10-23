import { Box, Grid, TextField, Typography } from "@mui/material";
import SecretTextBox from "../../components/SecretTextBox";
import { ProjectData } from "../../config/ProjectFieldsConfig";

interface GeneralProjectProps {
    project: ProjectData | null
}

const GeneralProject: React.FC<GeneralProjectProps> = ({project}) => {

    if(!project) return <p>Loading...</p>;

    const options = (project?.provinces ?? []).map(province => ({
        id: province.id,
        name: province.name
    }));

    return (
        <Box component="form" noValidate autoComplete="off" sx={{ mt: 2 }} className="row">
            <Grid container spacing={2}>
                {/* {
                    Object.values(
                        ProjectGeneralFieldsConfig
                            .sort((a, b) => (a.order ?? 999) - (b.order ?? 999))
                    ).map((field, idx) => (
                        {if(field.visible)}
                    ))
                } */}

                <Grid item xs={12} sm={6}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            Internal Job
                        </Typography>
                        <TextField
                            fullWidth
                            value={project?.internal_code ?? ""}
                            variant="outlined"
                            InputProps={{ readOnly: true }}
                        />
                    </div>
                </Grid>

                {/* Project Name */}
                <Grid item xs={12} sm={6}>
                    <div style={{ marginBottom: "1rem" }}>
                        <Typography variant="body2" gutterBottom>
                            Project Name
                        </Typography>
                        <TextField
                            fullWidth
                            value={project?.project_name ?? ""}
                            variant="outlined"
                            InputProps={{ readOnly: true }}
                        />
                    </div>
                </Grid>
                <Grid item xs={12}>
                    <SecretTextBox label="Remember Token" value={project?.remember_token ?? ""}></SecretTextBox>
                </Grid>
                <Grid item xs={12}>
                    {/* <TicketMultiSelect 
                        label="Provinces" 
                        titleDialog="Select provinces"
                        options={options} 
                        selected_items={(project?.provinces ?? []).map(p => p.label)}
                        // onChange={}
                    /> */}
                </Grid> 
            </Grid>
        </Box>
    )
}

export default GeneralProject;