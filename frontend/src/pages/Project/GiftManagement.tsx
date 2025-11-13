import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { useProjects } from "../../hook/useProjects";
import SearchTextBox from "../../components/SearchTextBox";

const GiftManagement: React.FC = () => {
  
  const { id } = useParams<{ id: string }>();
  const { getProject } = useProjects();
  const [ project, setProject ] = useState<ProjectData | null>(null);

  useEffect(() => {
    const fetchProject = async () => {

      if(id){
        const p = await getProject(parseInt(id));
        setProject(p);
      }
    }

    fetchProject()
  }, [id])
  
  return (
    <>
      
    </>
  )
}

export default GiftManagement;