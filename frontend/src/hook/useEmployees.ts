import { useCallback, useEffect, useState } from "react";
import { EmployeeData } from "../config/EmployeeFieldsConfig";
import axios from "axios";
import { ApiConfig } from "../config/ApiConfig";

export function useEmployees(projectId: number) {
    const [ employees, setEmployees ] = useState<EmployeeData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchEmployees = useCallback(async (page = 0, rowsPerPage = 0, searchTerm = '') => {
        try{
            setLoading(true);
            setError(false);

            const token = localStorage.getItem('authToken');

            const url = `${ApiConfig.employee.viewEmployees.replace("{projectId}", projectId.toString())}`;

            const response = await axios.get(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                params: {
                    page: page + 1,
                    perPage: rowsPerPage,
                    searchTerm: searchTerm
                }
            });

            setEmployees(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);
        } catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Employees');
        } finally{
            setLoading(false);
        }

    }, [projectId, page, rowsPerPage, searchTerm]);

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
        
        await fetchEmployees(page, rowsPerPage, searchTerm);
        
        setError(response.data.invalidEmployeeIds || response.data.existedEmployeeIds);
        setMessage(response.data.message);

    }, [fetchEmployees, page, rowsPerPage, searchTerm]);
    
    useEffect(() => {
        fetchEmployees(page, rowsPerPage, searchTerm)
    }, [projectId, page, rowsPerPage, searchTerm]);

    return {
        employees,
        meta,
        total,
        page,
        setPage,
        rowsPerPage,
        setRowsPerPage,
        searchTerm,
        setSearchTerm,
        loading,
        error,
        message,
        fetchEmployees,
        addEmployees
    }
}