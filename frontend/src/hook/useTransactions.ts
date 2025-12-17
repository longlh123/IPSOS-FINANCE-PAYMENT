import { useCallback, useEffect, useState } from "react";
import { TransactionData } from "../config/TransactionFieldsConfig";
import { ApiConfig } from "../config/ApiConfig";
import axios from "axios";

export function useTransactions(projectId: number){

    const [ transactions, setTransactions ] = useState<TransactionData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState<string | null>(null);

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");
    
    const today = new Date();
    const currentMonth = today.getMonth() + 1;
    const currentYear = today.getFullYear();

    const [ searchMonth, setSearchMonth ] = useState(currentMonth);
    const [ searchYear, setSearchYear ] = useState(currentYear);
    const [ exportAll, setExportAll ] = useState(false);

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchTransactions = useCallback(async(page = 0, rowsPerPage = 0, searchTerm = "", searchMonth = currentMonth, searchYear = currentYear, exportAll = false) => {
        try{
            setLoading(true);
            setError("");

            const token = localStorage.getItem('authToken');
            
            const url = `${ApiConfig.project.viewTransactions.replace("{projectId}", projectId.toString())}`;

            const response = await axios.get(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                params: {
                    page: page + 1,
                    perPage: rowsPerPage,
                    searchTerm: searchTerm,
                    searchMonth: searchMonth,
                    searchYear: searchYear,
                    export_all: exportAll
                }
            });

            setTransactions(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);

        }catch(error: any){
            setError(error.message || "Failed to fetch transactions.")
        }finally{
            setLoading(false);
        }

    }, [projectId, page, rowsPerPage, searchTerm, searchMonth, searchYear, exportAll]);

    useEffect(() => {
        fetchTransactions(page, rowsPerPage, searchTerm, searchMonth, searchYear, exportAll)
    }, [page, rowsPerPage, searchTerm, searchMonth, searchYear, exportAll, fetchTransactions]);

    return {
        transactions,
        meta,
        total,
        page,
        setPage,
        rowsPerPage,
        setRowsPerPage,
        searchTerm,
        setSearchTerm,
        searchMonth,
        setSearchMonth,
        searchYear,
        setSearchYear,
        exportAll,
        setExportAll,
        loading,
        error,
        fetchTransactions
    }
}