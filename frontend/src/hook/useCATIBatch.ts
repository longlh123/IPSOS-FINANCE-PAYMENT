import { useCallback, useEffect, useState } from "react";
import { CATIBatchData } from "../config/MiniCATIFieldsConfig";
import axios from "axios";
import { ApiConfig } from "../config/ApiConfig";

export const useCATIBatch = (projectId: number) => {

    const [ batches, setBatches ] = useState<CATIBatchData[]>([])
    
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ importLoading, setImportLoading ] = useState(false);
    const [ importError, setImportError ] = useState(false);
    const [ importMessage, setImportMessage ] = useState("");

    const [ destroyLoading, setDestroyLoading ] = useState(false);
    const [ destroyError, setDestroyError ] = useState(false);
    const [ destroyMessage, setDestroyMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");
    
    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchCATIBatches = useCallback(async () => {
        try
        {
            if(!projectId) return;

            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');

            const url = `${ApiConfig.minicati.showBatches.replace("{projectId}", projectId.toString())}`;

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

            setBatches(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);
        } catch(error:any){
            setError(true);
            setMessage(error.message || 'Failed to fetch CATI Batches!');
        } finally {
            setLoading(false);
        }
    }, [page, rowsPerPage, searchTerm])

    const importCATIBatch = async (file: any, batchName: string) => {
        try
        {
            setImportLoading(true);
            setImportError(false);
            setImportMessage("");

            const token = localStorage.getItem('authToken');

            const url = `${ApiConfig.minicati.importBatch.replace("{projectId}", projectId.toString())}`;

            const formData = new FormData();
            formData.append("file", file);
            formData.append("batch_name", batchName)

            const response = await axios.post(url, formData, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
            });

            setImportMessage(response.data.message);
        } catch(error: any){
            setImportError(true);
            setImportMessage(error.response.data.message || 'Failed to import CATI Batch!');
        } finally{
            setImportLoading(false);
        }
    };

    const destroyBatch = async (batchId: number) => {
        try
        {
            setDestroyLoading(true);
            setDestroyError(false);
            setDestroyMessage("");

            const token = localStorage.getItem('authToken');

            const url = `${ApiConfig.minicati.destroyBatch.replace("{projectId}", projectId.toString()).replace('{batchId}', batchId.toString()) }`;

            const response = await axios.delete(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
            });

            if(response.data.status_code === 400){
                setDestroyError(true);
                setDestroyMessage(response.data.error)
            } else {
                setDestroyMessage(response.data.message);
            }
        } catch(error: any){
            setDestroyError(true);
            setDestroyMessage(error.response.data.message || 'Failed to delete CATI Batch!');
        } finally{
            setDestroyLoading(false);
        }
    }

    useEffect(() => {
        fetchCATIBatches();
    }, [page, rowsPerPage, searchTerm])

    return {
        batches,
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

        importLoading,
        importError,
        importMessage,

        destroyLoading,
        destroyError,
        destroyMessage,

        fetchCATIBatches,
        importCATIBatch,

        destroyBatch
    }
}