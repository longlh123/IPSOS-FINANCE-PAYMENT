import { useEffect, useState, useCallback } from "react";
import axios from "axios";
import { ApiConfig } from "../config/ApiConfig";
import { ProjectData } from "../config/ProjectFieldsConfig";

export function useProjects() {

    const [ projects, setProjects ] = useState<ProjectData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState<string | null>(null);

    const fetchProjects = useCallback(async () => {
        try{
            setLoading(true);
            setError(null);

            const token = localStorage.getItem("authToken");

            const response = await axios.get(ApiConfig.project.viewProjects, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Show-Only-Enabled': '1',
                },
            });

            setProjects(response.data.data);

        }catch(error: any){
            setError(error.message || "Failed to fetch Projects");
        } finally{
            setLoading(false);
        }

    }, []);

    const addProject = useCallback(async (payload: Partial<ProjectData>) => {

        const token = localStorage.getItem("authToken");

        const response = await axios.post(ApiConfig.project.addProject, payload, {
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
        });

        await fetchProjects();
        return response.data.data;

    }, [fetchProjects]);
    
    const updateProjectStatus = useCallback(async ( id: number, status: string) => {

        const token = localStorage.getItem("authToken");

        const url = ApiConfig.project.updateStatusOfProject.replace('{project_id}', id.toString());

        const response = await axios.put(url, { status: status }, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
        });

        await fetchProjects();
        return response.data.data;

    }, [fetchProjects]);

    const getProject = useCallback(async (id: number) => {
        
        const token = localStorage.getItem("authToken");
        
        const url = `${ApiConfig.project.viewProjects + "/" + id + '/show'}`;

        const response = await axios.get(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });

        await fetchProjects();
        return response.data.data;

    }, [fetchProjects]);

    const getTransactions = useCallback(async (id: number) => {

        const token = localStorage.getItem("authToken");

        const url = `${ApiConfig.project.viewTransactions.replace("{projectId}", id.toString())}`;

        const response = await axios.get(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
        });

        return response.data.data;
    }, [])

    useEffect(() => {
        fetchProjects();
    }, [fetchProjects]);

    return {
        projects,
        loading,
        error,
        fetchProjects,
        getProject,
        addProject,
        updateProjectStatus,
        getTransactions
    };
}