import { useCallback, useEffect, useState } from "react";
import { TransactionData } from "../config/TransactionFieldsConfig";
import { ApiConfig } from "../config/ApiConfig";
import axios from "../config/axiosInstance";

export function useTransactions(projectId: number){

    const [ transactions, setTransactions ] = useState<TransactionData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");
 

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchTransactions = useCallback(async(page = 0, rowsPerPage = 0, searchTerm = "") => {
        try{
            setLoading(true);
            setError(false);
            setMessage("");

            const url = ApiConfig.project.viewTransactions.replace("{projectId}", projectId.toString());

            const response = await axios.get(url, {
                params: {
                    page: page + 1,
                    perPage: rowsPerPage,
                    searchTerm: searchTerm
                }
            });

            setTransactions(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);
            
        }catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Transactions');
        }finally{
            setLoading(false);
        }

    }, [projectId]);

    useEffect(() => {
        fetchTransactions(page, rowsPerPage, searchTerm)
    }, [page, rowsPerPage, searchTerm]);

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
        loading,
        error,
        message,
        fetchTransactions,

        refetch: fetchTransactions
    }
}