import { useCallback, useEffect, useState } from "react";
import { CATIRespondentData } from "../config/MiniCATIFieldsConfig";
import axios from "axios";
import { ApiConfig } from "../config/ApiConfig";

export const useCATIRespondents = (employeeId: string) => {

    const [ catiRespondents, setCATIRespondents ] = useState<CATIRespondentData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");
    

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchCATISuppendedList = useCallback(async () => {
        try
        {
            setLoading(true);
            setError(false);
            setMessage("");
            
            const response = await axios.get(ApiConfig.minicati.getSuspendedList, {
                params: {
                    employee_id: employeeId,
                    page: page + 1,
                    perPage: rowsPerPage,
                    searchTerm: searchTerm
                }
            })

            const res = await axios.get(ApiConfig.minicati.getSuspendedList, {
                params: { employee_id: employeeId }
            });

            setCATIRespondents(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);
        } catch(error:any){
            console.log(error);
            setError(true);
            setMessage(error.message || 'Failed to fetch CATI Suppended List!');
        } finally {
            setLoading(false);
        }
    }, [page, rowsPerPage, searchTerm]);  

    useEffect(() => {
        fetchCATISuppendedList();
    }, [page, rowsPerPage, searchTerm])

    return {
        catiRespondents,
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
        message
    }
};