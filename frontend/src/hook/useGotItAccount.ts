import axios from "axios";
import { useState } from "react";
import { ApiConfig } from "../config/ApiConfig";

interface gotitAccountData {
    deposited: number,
    spent: number,
    balance: number
}

export function useGotItAccount(){
    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(false);
    const [ message, setMessage ] = useState("");

    const [ gotitAccount, setGotItAccount ] = useState<gotitAccountData>({
        deposited: 0,
        spent: 0,
        balance: 0
    });
    
    const addDeposit = async (amount: string) => {
        try{
            setError(false);
            setLoading(true);

            const token = localStorage.getItem('authToken')

            const res = await axios.post(ApiConfig.gotit.depositedAccount, {
                'account_type': 'gotit',
                'amount': amount
            },{
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            setGotItAccount(prev => ({
                ...prev,
                deposited: res.data.deposited
            }))
        } catch(error){
            setError(true);
        } finally {
            setLoading(false);
        }
    }

    return {
        loading,
        gotitAccount,
        addDeposit
    }
}