import { useCallback, useEffect, useState } from "react";
import { ApiConfig } from "../config/ApiConfig";
import axios from "axios";
import { QuotationVersionData } from "../config/QuotationConfig";
import { ProjectData } from "../config/ProjectFieldsConfig";

export function useQuotation(projectId?: number) {
    const [ project, setProject ] = useState<ProjectData | null>(null); 
    const [ versions, setVersions ] = useState<QuotationVersionData[] | null>(null);
    const [ selectedVersion, setSelectedVersion ] = useState<QuotationVersionData | null>(null);
    const [ canEdit, setCanEdit ] = useState<boolean>(false);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const getQuotationVersions = useCallback(async () => {
        try
        {
            if(!projectId) return;

            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');
                        
            const url = `${ApiConfig.project.viewQuotationVersions.replace("{projectId}", projectId.toString())}`;

            const response = await axios.get(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
            
            setProject(response.data.project);
            setVersions(response.data.versions);

            if(response.data.versions.length > 0){
                const latest = response.data.versions[0];
                setSelectedVersion(latest);

                setCanEdit(true);
            } else {
                setCanEdit(false);
            }

            setMessage(response.data.message);
        } catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Quotation');
        } finally{
            setLoading(false);
        }
    }, [projectId]);
    
    const addQuotation = useCallback(async (payload:any) => {
        try
        {
            if(!projectId) return;

            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');
                        
            const url = `${ApiConfig.project.addQuotation.replace("{projectId}", projectId.toString())}`;

            const response = await axios.post(url, {
                data: payload
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            return response.data.quotation;
        } catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Quotation');
        } finally{
            setLoading(false);
        }
    }, [projectId]);

    const updateQuotation = useCallback(async (payload: any) => {
        try
        {
            if (!projectId) return;
            if (!selectedVersion) return;

            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');
                        
            const url = `${ApiConfig.project.updateQuotation.replace("{projectId}", projectId.toString()).replace("{versionId}", selectedVersion.id.toString())}`;

            const response = await axios.put(url, {
                data: payload
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            setSelectedVersion(response.data.quotation);
            setCanEdit(false);
            setMessage(response.data.message);
        } catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Quotation');
        } finally{
            setLoading(false);
        }
    }, [projectId, selectedVersion]);

    const destroyQuotation = useCallback(async () => {
        try
        {
            if (!projectId) return;
            if (!selectedVersion) return;

            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');
                        
            const url = `${ApiConfig.project.destroyQuotation.replace("{projectId}", projectId.toString()).replace("{versionId}", selectedVersion.id.toString())}`;

            const response = await axios.delete(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            await getQuotationVersions();

            setMessage(response.data.message);
        } catch(error: any){
            setError(true);
            setMessage(error.message || 'Failed to fetch Quotation');
        } finally{
            setLoading(false);
        }
    }, [projectId, selectedVersion]);

    useEffect(() => {
        getQuotationVersions();
    }, [projectId]);

    return {
        loading,
        error,
        message,
        project,
        getQuotationVersions,
        versions,
        selectedVersion,
        setSelectedVersion,
        canEdit,
        setCanEdit,
        addQuotation,
        updateQuotation,
        destroyQuotation
    }
}