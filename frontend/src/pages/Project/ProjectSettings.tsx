import { Tab, Tabs } from "@mui/material";
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { useProjects } from "../../hook/useProjects";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import TabPanel from "../../components/TabPanel";
import GeneralProject from "./GeneralProject";

const ProjectSettings = () => {

    const { id } = useParams<{ id: string }>();
    const { getProject } = useProjects();
    const [ project, setProject ] = useState<ProjectData | null>(null);

    useEffect(() => {
        const fetchProject = async () => {

            if(id){
                const p = await getProject(parseInt(id));
                setProject(p);
            }
        };
        
        fetchProject();
    }, [id]);

    const [value, setValue] = useState('one');

    const handleChange = (event: React.SyntheticEvent, newValue: string) => {
        setValue(newValue);
    };


    return (
        <>
            <Tabs
                value={value}
                onChange={handleChange}
                textColor="secondary"
                indicatorColor="secondary"
                aria-label="secondary tabs example"
                >
                <Tab value="one" label="GENERAL" />
                <Tab value="two" label="SAMPLE" />
                <Tab value="three" label="OPERATION" />
            </Tabs>

            <TabPanel value={value} index="one">
                <GeneralProject project={project} />
            </TabPanel>
        </>
    )
}

export default ProjectSettings;