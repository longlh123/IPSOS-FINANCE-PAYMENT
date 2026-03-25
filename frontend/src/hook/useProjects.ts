import { useEffect, useState, useCallback } from "react";
import axios from "../config/axiosInstance";
import { ApiConfig } from "../config/ApiConfig";
import { ProjectData } from "../config/ProjectFieldsConfig";
import dayjs, { Dayjs } from "dayjs";

export function useProjects() {

    const [ projects, setProjects ] = useState<ProjectData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState<string | null>(null);

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");

    const [ searchFromDate, setSearchFromDate ] = useState<Dayjs>(dayjs().startOf("year"));
    const [ searchToDate, setSearchToDate ] = useState<Dayjs>(dayjs().endOf("year"));

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0); //Tổng số projects từ backend

    const fetchProjects = useCallback(async (page = 0, rowsPerPage = 0, searchTerm = "", searchFromDate = dayjs().startOf("year"), searchToDate = dayjs().endOf("year")) => {
        try{
            setLoading(true);
            setError(null);

            const response = await axios.get(ApiConfig.project.viewProjects, {
                headers: {
                    'Show-Only-Enabled': '1',
                },
                params: {
                    page: page + 1,
                    perPage: rowsPerPage,
                    searchTerm: searchTerm,
                    searchFromDate: searchFromDate.format("YYYY-MM-DD"),
                    searchToDate: searchToDate.format("YYYY-MM-DD")
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

    }, []);

    const addProject = useCallback(async (payload: Partial<ProjectData>) => {
        try{
            const response = await axios.post(ApiConfig.project.addProject, payload);

            await fetchProjects();
            return response.data.data;
        } catch(error){
            throw error;
        }
    }, [fetchProjects]);
    
    const updateProjectStatus = useCallback(async ( id: number, status: string) => {
        const url = ApiConfig.project.updateStatusOfProject.replace('{projectId}', id.toString());

        const response = await axios.put(url, { status });

        await fetchProjects();
        return response.data.data;

    }, [fetchProjects]);

    const getProject = useCallback(async (id: number) => {
        const url = `${ApiConfig.project.viewProjects}/${id}/show`;

        const response = await axios.get(url);

        return response.data.data;

    }, []);
    
    useEffect(() => {
        fetchProjects(page, rowsPerPage, searchTerm, searchFromDate, searchToDate);
    }, [page, rowsPerPage, searchTerm, searchFromDate, searchToDate, fetchProjects]);

    return {
        projects,
        meta,
        total,
        page,
        setPage,
        rowsPerPage,
        setRowsPerPage,
        searchTerm,
        setSearchTerm,
        searchFromDate,
        setSearchFromDate,
        searchToDate,
        setSearchToDate,
        loading,
        error,
        fetchProjects,
        getProject,
        addProject,
        updateProjectStatus
    };
}