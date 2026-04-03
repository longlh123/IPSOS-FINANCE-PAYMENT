import { Api } from "@mui/icons-material";
import axios from "axios";
import { useCallback, useEffect, useState } from "react";
import { ApiConfig } from "../config/ApiConfig";
import { RecipientListData } from "../config/TradeUnionFieldsConfig";

export function useTradeUnion() {
    const [ recipientLists, setRecipientLists ] = useState<RecipientListData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchRecipientLists = useCallback(async (page = 0, rowsPerPage = 0, searchTerm = "") => {
        try{
            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');

            const response = await axios.get(ApiConfig.tradeUnion.viewRecipientLists, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            setRecipientLists(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);
        } catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Recipient Lists');
        } finally{
            setLoading(false);
        }
    }, [page, rowsPerPage, searchTerm]);

    useEffect(() => {
        fetchRecipientLists(page, rowsPerPage, searchTerm);
    }, [page, rowsPerPage, searchTerm]);

    return {
        recipientLists,
        total,
        loading,
        error,
        message,
        fetchRecipientLists,
        page,
        setPage,
        rowsPerPage,
        setRowsPerPage,
        searchTerm,
        setSearchTerm
    }
}