import axios from "axios";
import React from "react";
import { createContext, ReactNode, useContext, useState } from "react";
import { useNavigate } from "react-router-dom";

interface UserDetail {
    first_name: string,
    last_name: string,
    role: string
}

interface AuthContextType {
    user: UserDetail | null;  
    token: string | null;
    login: (token: string, user: UserDetail) => void;
    logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [ token, setToken ] = useState<string | null>(localStorage.getItem('authToken'));
    const [ user, setUser ] = useState<UserDetail | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const navigate = useNavigate();

    React.useEffect(() => {
        const storedToken = localStorage.getItem("authToken");
        const storedUser = localStorage.getItem("user");
        const devAuthEnabled = process.env.REACT_APP_DEV_AUTH === "true";

        if (storedToken) {
            setToken(storedToken);
        } else if (process.env.NODE_ENV === "development" && devAuthEnabled) {
            const devToken = "dev-token";
            setToken(devToken);
            localStorage.setItem("authToken", devToken);
        }

        if (storedUser) {
            setUser(JSON.parse(storedUser));
        } else if (process.env.NODE_ENV === "development" && devAuthEnabled) {
            const devUser = { first_name: "Dev", last_name: "User", role: "admin" };
            setUser(devUser);
            localStorage.setItem("user", JSON.stringify(devUser));
        }

        setLoading(false);
    }, []);

    const login = (token: string, userData: UserDetail) => {
        setToken(token);
        setUser(userData);
        
        localStorage.setItem('authToken', token);
        localStorage.setItem('user', JSON.stringify(userData))

        const headers = {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
        };
        
        navigate('/project-management/projects', { replace: true }); // Redirect to the desired page after login
    };

    const logout = () => {
        setToken(null);
        setUser(null);

        localStorage.removeItem('authToken');
        localStorage.removeItem('user');

        navigate('/login', { replace: true }); // Redirect to login page after logout
    };

    return (
        <AuthContext.Provider value={{ user, token, login, logout }}>
          {!loading && children}
        </AuthContext.Provider>
    );
};

export const useAuth = (): AuthContextType => {
    const context = useContext(AuthContext);

    if(!context){
        throw new Error('useAuth must be used within an AuthProvider');
    }

    return context;
}
