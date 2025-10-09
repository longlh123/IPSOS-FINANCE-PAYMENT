import { useEffect, useState } from "react";
import axios from "axios";
import { useParams } from "react-router-dom";
import { ApiConfig } from "../../config/ApiConfig";
import TableRespondents from "../../components/Table/TableRespondents";
import Directional from "../../components/Directional/Directional";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { VisibilityConfig } from "../../config/RoleConfig";
import TableParttimeEmployees from "../../components/Table/TableParttimeEmployees";

const GiftManagement: React.FC = () => {
  const storedUser = localStorage.getItem('user');
  const user = storedUser ? JSON.parse(storedUser) : null;
  
  const visibilityConfig = VisibilityConfig[user.role as keyof typeof VisibilityConfig];

  const [statusMessage, setStatusMessage] = useState("");
  const [isError, setIsError] = useState<boolean | null>(null);

  const { id } = useParams<{ id: string }>();
  const [ project, setProject ] = useState<ProjectData | null>(null);

  useEffect(() => {
    const fetchProject = async () => {
      try {
        const token = localStorage.getItem("authToken");
        const url = `${ApiConfig.project.viewProjects + "/" + id + '/show'}`;

        const response = await axios.get(url, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
              'Show-Only-Enabled': '1',
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
    };

    fetchProject();
  }, [id, project]);
  
  return (
    <>
      {visibilityConfig.projects.components.visible_employees && (
        <>
          <Directional title={project?.internal_code + ' - ' + project?.project_name} />
          <TableParttimeEmployees project_id={id || ""} />
        </>
      )}
      {visibilityConfig.projects.components.visible_employees && (
        <>
          <Directional title="Respondents" />
          <TableRespondents />
        </>
      )}
    </>
  )
}

export default GiftManagement;