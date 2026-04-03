import axios from "axios";
import { useCallback, useEffect, useState } from "react";
import { ApiConfig } from "../config/ApiConfig";

interface vinnetAccountData {
    deposited: number,
    spent: number,
    balance: number
}

export function useVinnetAccount() {

    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ vinnetAccount, setVinnetAccount ] = useState<vinnetAccountData>({
        deposited: 0,
        spent: 0,
        balance: 0
    });

    const fetchMerchantInfor = async () =>{
        try{
            setLoading(true);
            setError(false);
            setMessage("");

            const token = localStorage.getItem('authToken');

            const response = await axios.get(ApiConfig.vinnet.viewMerchantAccount, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                }
            });

            const rawAccountData: string = response.data.data;
            const parsedData = JSON.parse(rawAccountData);
            
            setVinnetAccount({
                deposited: Number(parsedData.deposited),
                spent: Number(parsedData.spent),
                balance: Number(parsedData.balance),
            });

        } catch(error){
            setError(true);

            let errorMessage = '';

            if (axios.isAxiosError(error)) {
                errorMessage = error.response?.data.message ?? error.message;
            } else {
                errorMessage = (error as Error).message;
            }

            setMessage(errorMessage);
            console.error('Error:', errorMessage);
        } finally{
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchMerchantInfor();
    }, []);

    return {
        vinnetAccount,
        loading,
        error,
        message
    }
}