import { useCallback, useEffect, useState } from "react";
import { AccountData } from "../config/AccountFieldsConfig";
import { ApiConfig } from "../config/ApiConfig";
import axios from "axios";

export function useAccounts() {

    const [ accounts, setAccounts ] = useState<AccountData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");
    

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchAcounts = useCallback(async(page = 0, rowsPerPage = 0, searchTerm = "") => {
        try{
            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');
            
            const response = await axios.get(ApiConfig.account.viewAccounts, {
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

            setAccounts(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);
            
        }catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Accounts');
        }finally{
            setLoading(false);
        }

    }, [page, rowsPerPage, searchTerm]);

    const storeAccount = async (payload: AccountData) => {
        try{
            setLoading(true);
            setError(false);
            setMessage("");
            
            const token = localStorage.getItem('authToken');

            const response = await axios.post(ApiConfig.account.storeAccount, payload, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
            });

            setMessage(response.data.message);

        } catch(error: any){
            setError(true);
            setMessage(error.response.data.message || error.response.data.error || 'Failed to store Account');
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchAcounts(page, rowsPerPage, searchTerm)
    }, [page, rowsPerPage, searchTerm]);

    return {
        accounts,
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
        fetchAcounts,
        storeAccount,

        refetch: fetchAcounts
    }
}