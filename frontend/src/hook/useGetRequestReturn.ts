import { useEffect, useState, useCallback } from 'react';
import axios, { AxiosResponse } from 'axios';

interface UseGetRequestReturn<T> {
    isLoading: boolean,
    messageError: string | null,
    data: T | null,
    getRequest: (url: string) => Promise<void>;
};

function useGetRequest<T = any>(): UseGetRequestReturn<T> {
    const [ isLoading, setIsLoading ] = useState<boolean>(false);
    const [ messageError, setMessageError ] = useState<string | null>(null);
    const [ data, setData ] = useState<T | null>(null);

    const getRequest = useCallback(async (url: string): Promise<void> => {
        setIsLoading(true);
        setMessageError(null);
    
        try {
            const response: AxiosResponse<T> = await axios.get(url);
            setData(response.data);
        } catch (error: any) {
            if (axios.isAxiosError(error)) {
                setMessageError(error.response?.data.message);
            } else {
                setMessageError(error.message);
            }
        } finally {
            setIsLoading(false);
        }
    }, []);
    
    return {
        isLoading,
        messageError,
        data,
        getRequest
    };
}

export default useGetRequest;