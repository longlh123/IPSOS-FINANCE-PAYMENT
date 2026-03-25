import { useCallback, useEffect, useState } from "react";
import { EmployeeData } from "../config/EmployeeFieldsConfig";
import axios from "../config/axiosInstance";
import { isAxiosError } from "axios";
import { ApiConfig } from "../config/ApiConfig";

export function useEmployees(projectId: number) {
    const [ employees, setEmployees ] = useState<EmployeeData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");
    const [ sortBy, setSortBy ] = useState("employee_id");
    const [ sortDirection, setSortDirection ] = useState<"asc" | "desc">("asc");

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchEmployees = useCallback(async (page = 0, rowsPerPage = 0, searchTerm = '', sortBy = 'employee_id', sortDirection: 'asc' | 'desc' = 'asc') => {
        try{
            setLoading(true);
            setError(false);
            setMessage("");

            const url = ApiConfig.employee.viewEmployees.replace("{projectId}", projectId.toString());

            const response = await axios.get(url, {
                params: {
                    page: page + 1,
                    perPage: rowsPerPage,
                    searchTerm: searchTerm,
                    sortBy,
                    sortDirection
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

    }, [projectId]);

    const addEmployees = useCallback(async (id: number, employee_ids: string) => {
        const url = ApiConfig.project.addEmployees.replace("{projectId}", id.toString());

        const response = await axios.post(url, {
            employee_ids
        });
        
        await fetchEmployees(page, rowsPerPage, searchTerm);
        
        setError(response.data.invalidEmployeeIds.length > 0 || response.data.existedEmployeeIds.length > 0);
        setMessage(response.data.message);

    }, [fetchEmployees, page, rowsPerPage, searchTerm]);
    
    const removeEmployee = useCallback(async (project_id: number, employee_id: number) => {
        const url = ApiConfig.project.removeEmployee.replace("{projectId}", project_id.toString()).replace("{employeeId}", employee_id.toString());

        const response = await axios.delete(url);

        await fetchEmployees(page, rowsPerPage, searchTerm);

        setError(!response.data.success);
        setMessage(response.data.message);

    }, [fetchEmployees, page, rowsPerPage, searchTerm]);

    const addEmployeeToTravelExpense = useCallback(async (project_id: number, employee_id: number, province_id: number) => {
        try {
            const url = ApiConfig.project.addEmployeeToTravelExpense
                .replace("{projectId}", project_id.toString())
                .replace("{employeeId}", employee_id.toString());

            const response = await axios.post(url, {
                province_id,
            });

            setError(!response.data.success);
            setMessage(response.data.message);

            return response.data;
        } catch (error: any) {
            setError(true);

            if (isAxiosError(error)) {
                setMessage(error.response?.data?.message || error.message || "Failed to add employee to travel expense list");
            } else {
                setMessage(error?.message || "Failed to add employee to travel expense list");
            }

            throw error;
        }
    }, []);

    useEffect(() => {
        fetchEmployees(page, rowsPerPage, searchTerm, sortBy, sortDirection)
    }, [projectId, page, rowsPerPage, searchTerm, sortBy, sortDirection]);

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
        sortBy,
        setSortBy,
        sortDirection,
        setSortDirection,
        loading,
        error,
        message,
        fetchEmployees,
        addEmployees,
        removeEmployee,
        addEmployeeToTravelExpense
    }
}