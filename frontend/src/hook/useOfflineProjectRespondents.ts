import { useCallback, useEffect, useState } from "react";
import { OfflineProjectRespondentData } from "../config/OfflineProjectRespondentFieldsConfig";
import { ApiConfig } from "../config/ApiConfig";
import axios from "axios";
import { DataArray } from "@mui/icons-material";
import { getServiceCode } from "../utils/VinnetFunctions";

interface ImportErrorDetail {
    row: number,
    type: string,
    respondent_id: string
}

export function useOfflineProjectRespondents(projectId: number) {
    
    const [ offineProjectRespondents, setOfflineProjectRespondents ] = useState<OfflineProjectRespondentData[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(10);
    const [ searchTerm, setSearchTerm ] = useState("");

    const [ meta, setMeta ] = useState<any>(null);
    const [ total, setTotal ] = useState(0);

    const fetchProjectRespondents = useCallback(async(page = 0, rowsPerPage = 0, searchTerm = "") => {
        try
        {
            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');
            
            const url = `${ApiConfig.project.viewOfflineProjectRespondents.replace("{projectId}", projectId.toString())}`;

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

            setOfflineProjectRespondents(response.data.data);
            setMeta(response.data.meta);
            setTotal(response.data.meta.total);

        }catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Transactions');
        }finally{
            setLoading(false);
        }
    }, [projectId, page, rowsPerPage, searchTerm]);

    const importOfflineProjectRespondents = useCallback(async(payload: any) => {
        try
        {
            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');

            const url = `${ApiConfig.project.addOfflineProjectRespondents.replace("{projectId}", projectId.toString())}`;

            const response = await axios.post(url, {
                project_respondents: payload
            }, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
            });

            await fetchProjectRespondents(page, rowsPerPage, searchTerm);

            setMessage(`Imported ${response.data.imported_count} respondents.`);

        } catch(error: any){
            setError(true);

            if (axios.isAxiosError(error)) {
                if(error.response?.status == 442){
                    const errors = error.response.data.errors ?? [];

                    const grouped = errors.reduce((acc: Record<string, any[]>, e: ImportErrorDetail) => {
                        acc[e.type] = acc[e.type] || [];
                        acc[e.type].push(e);
                        return acc;
                    }, {});

                    const groupText = (Object.entries(grouped) as [string, ImportErrorDetail[]][]).map(([type, items]) => {
                        const rows = items.map((i: any) => i.row).join(", ");
                        const label = type;
                        return `❌ ${label}: ${items.length} row(s) (${rows})`;
                    })
                    .join("<br/>");

                    setMessage(
                        `There are ${error.response.data.error_count} invalid rows.<br/><br/>${groupText}`
                    );
                } else {
                    setMessage(error.response?.data.message ?? error.message);
                }
            } else {
                setMessage(error.message);
            }

            throw error;
        } finally {
            setLoading(false);
        }
    }, [fetchProjectRespondents, page, rowsPerPage, searchTerm]);

    const removeProjectRespondent = useCallback(async (project_id: number, data: OfflineProjectRespondentData) => {
        const token = localStorage.getItem("authToken");

        const url = `${ApiConfig.project.removeProjectRespondent.replace("{projectId}", project_id.toString()).replace("{projectRespondentId}", data.id.toString())}`;

        const response = await axios.delete(url, {
            data: {
                shell_chainid: data.shell_chainid,
                respondent_id: data.respondent_id
            }, 
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        await fetchProjectRespondents(page, rowsPerPage, searchTerm);

        setError(!response.data.success);
        setMessage(response.data.message);

    }, [fetchProjectRespondents, page, rowsPerPage, searchTerm]);
    
    const offlineTransactionSending = useCallback(async (project_id: number, data: OfflineProjectRespondentData) => {
        try
        {
            setLoading(false);
            setError(false);
            setMessage("");

            const token = localStorage.getItem("authToken");

            const url = `${ApiConfig.project.offlineTransactionSending.replace("{projectId}", project_id.toString()).replace("{projectRespondentId}", data.id.toString())}`;

            const response = await axios.post(url, {
                    token: data.token,
                    service_type: "voucher",
                    service_code: getServiceCode(data.phone_number),
                    phone_number: data.phone_number,
                    provider: "gotit",
                    delivery_method: "sms"
                }, {
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                });

            await fetchProjectRespondents(page, rowsPerPage, searchTerm);

            setMessage(response.data.message);
        } catch(error: any){
            setError(true);

            if (axios.isAxiosError(error)) {
                setMessage(error.response?.data.message);
            } else {
                setMessage(error.message);
            }
        }finally{
            setLoading(false);
        }
    }, [fetchProjectRespondents, page, rowsPerPage, searchTerm]);

    useEffect(() => {
        fetchProjectRespondents(page, rowsPerPage, searchTerm);
    }, [projectId, page, rowsPerPage, searchTerm])

    return {
        offineProjectRespondents,
        loading,
        error,
        message,
        offlineTransactionSending,
        importOfflineProjectRespondents,
        removeProjectRespondent,
        page,
        rowsPerPage,
        searchTerm,
        total,
        setSearchTerm,
        setPage,
        setRowsPerPage
    };
}