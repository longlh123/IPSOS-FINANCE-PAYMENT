import { useState } from 'react';
import axios, { AxiosResponse } from 'axios';

interface UsePostRequestReturn<T> {
    isLoading: boolean;
    messageError: string | null;
    data: T | null;
    postRequest: (url: string, token: (string | null), postData: any) => Promise<void>;
};

function usePostRequest<T = any>(): UsePostRequestReturn<T> { 

    const [ isLoading, setIsLoading ] = useState<boolean>(false);
    const [ messageError, setMessageError ] = useState<string | null>(null);
    const [ data, setData ] = useState<T | null>(null);
    
    const postRequest = async (url: string, token: (string | null), postData: any): Promise<void> => {
        setIsLoading(true);
        setMessageError(null);

        try{
            // Create headers object
            const headers: any = {
                'Content-Type': 'application/json',
            };

            // Conditionally add Authorization header
            if (token) {
                headers['Authorization'] = token;
            }
            
            const response: AxiosResponse<T> = await axios.post(url, postData, headers);
            setData(response.data);
        } catch (error: any){
            if (axios.isAxiosError(error)) {
                setMessageError(error.response?.data.message ?? error.message);
            } else {
                setMessageError(error.message);
            }
        } finally {
            setIsLoading(false);
        }
    }; 
    
    return {
        isLoading,
        messageError,
        data,
        postRequest
    };
}

export default usePostRequest;