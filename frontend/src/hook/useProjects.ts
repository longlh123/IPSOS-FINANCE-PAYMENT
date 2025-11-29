import { useEffect, useState, useCallback } from "react";
import axios from "axios";
import { ApiConfig } from "../config/ApiConfig";
import { ProjectData } from "../config/ProjectFieldsConfig";

export function useProjects() {

    const [ projects, setProjects ] = useState<ProjectData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState<string | null>(null);

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    
    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0); //Tổng số projects từ backend

    const fetchProjects = useCallback(async (page = 0, rowsPerPage = 0) => {
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
                params: {
                    page: page + 1,        // Laravel dùng page = 1,2,3...
                    per_page: rowsPerPage
                },
            });

            setProjects(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);

        }catch(error: any){
            setError(error.message || "Failed to fetch Projects");
        } finally{
            setLoading(false);
        }

    }, [page, rowsPerPage]);

    const addProject = useCallback(async (payload: Partial<ProjectData>) => {
        try{
            const token = localStorage.getItem("authToken");

            const response = await axios.post(ApiConfig.project.addProject, payload, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
            });

            await fetchProjects();
            return response.data.data;
        } catch(error){
            throw error;
        }
    }, [fetchProjects]);
    
    const updateProjectStatus = useCallback(async ( id: number, status: string) => {

        const token = localStorage.getItem("authToken");

        const url = ApiConfig.project.updateStatusOfProject.replace('{projectId}', id.toString());

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
    }, []);

    const getEmployees = useCallback(async (id: number) => {
        const token = localStorage.getItem("authToken");

        const url = `${ApiConfig.project.viewEmployees.replace("{projectId}", id.toString())}`;

        const response = await axios.get(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
        });

        return response.data.data;
    }, []); 

    const addEmployees = useCallback(async (id: number, employee_ids: string) => {
        const token = localStorage.getItem("authToken");

        const url = `${ApiConfig.project.addEmployees.replace("{projectId}", id.toString())}`;

        const response = await axios.post(url, {
            employee_ids
        }, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
        });
        
        await getEmployees(id);
        return response.data;
    }, [getEmployees]);
    
    useEffect(() => {
        fetchProjects(page, rowsPerPage);
    }, [page, rowsPerPage]);

    return {
        projects,
        meta,
        total,
        page,
        setPage,
        rowsPerPage,
        setRowsPerPage,
        loading,
        error,
        fetchProjects,
        getProject,
        addProject,
        updateProjectStatus,
        getTransactions,
        getEmployees,
        addEmployees
    };
}