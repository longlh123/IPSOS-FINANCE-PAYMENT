import { useEffect, useState } from "react";
import { ActionState } from "../components/Table/ReusableTable";
import { ApiConfig } from "../config/ApiConfig";
import axios from "axios";

export function useNotifications(){

    const [ notifications, setNotifications ] = useState([]);
    const [ countUnRead, setCountUnRead ] = useState(0);

    const [ actionState, setActionState ] = useState<ActionState>({
        type: 'idle',
        loading: false,
        error: false,
        message: ""
    });

    const fetchNotifications = async (options?: { silent?: boolean}) => {
        try
        {
            setActionState((prev) => ({
                ...prev,
                type: 'fetch',
                loading: true,
                ...(options?.silent ? {} : { message: "", error: false })
            }));

            const token = localStorage.getItem('authToken');
                        
            const url = `${ApiConfig.account.viewNotifications}`;

            const response = await axios.get(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            setNotifications(response.data.data);

        }catch(error: any){
            setActionState((prev) => ({
                ...prev,
                loading: false,
                error: true,
                ...(options?.silent ? {} : {
                    message: error.response.data.error || 'Failed to get quotation versions!'
                })
            }));
        }
    };

    const fetchCountUnRead = async (options?: { silent?: boolean}) => {
        try
        {
            setActionState((prev) => ({
                ...prev,
                type: 'fetch',
                loading: true,
                ...(options?.silent ? {} : { message: "", error: false })
            }));

            const token = localStorage.getItem('authToken');
                        
            const url = `${ApiConfig.account.countUnRead}`;

            const response = await axios.get(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            setCountUnRead(response.data.count);

        }catch(error: any){
            setActionState((prev) => ({
                ...prev,
                loading: false,
                error: true,
                ...(options?.silent ? {} : {
                    message: error.response.data.error || 'Failed to get quotation versions!'
                })
            }));
        }
    }

    useEffect(() => {
        fetchCountUnRead();
    }, []);

    return {
        countUnRead,
        notifications,

        fetchNotifications
    };
}