import { useCallback, useEffect, useState } from "react";
import axios from "axios";
import { ApiConfig } from "../config/ApiConfig";

export const useMetadata = () => {
    const [ metadata, setMetadata ] = useState<any>(null);
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState<string | null>(null);

    const fetchMetadata = useCallback(async () => {
        try{
            setLoading(true);
            setError(null);

            const response = await axios.get(ApiConfig.project.getMetadata);
            setMetadata(response.data.data);

        } catch(error: any){
            setError(error.message || "Failed to load metadata");
        } finally{
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchMetadata();
    }, [fetchMetadata]);

    return { metadata, loading, error, fetchMetadata };
};