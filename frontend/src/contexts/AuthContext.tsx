import React from "react";
import { createContext, ReactNode, useContext, useState } from "react";
import { useNavigate } from "react-router-dom";
import { clearAuthData, getAuthToken, getStoredUser, saveAuthData } from "../utils/authStorage";

interface UserDetail {
    first_name: string,
    last_name: string,
    role: string
}

interface AuthContextType {
    user: UserDetail | null;  
    token: string | null;
    login: (token: string, user: UserDetail, rememberMe: boolean) => void;
    logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [ token, setToken ] = useState<string | null>(() => getAuthToken());
    const [ user, setUser ] = useState<UserDetail | null>(() => getStoredUser<UserDetail>());
    const [loading, setLoading] = useState<boolean>(true);
    const navigate = useNavigate();

    React.useEffect(() => {
        const storedToken = getAuthToken();
        const devAuthEnabled = process.env.REACT_APP_DEV_AUTH === "true";

        if (!storedToken && process.env.NODE_ENV === "development" && devAuthEnabled) {
            const devToken = "dev-token";
            setToken(devToken);
            sessionStorage.setItem("authToken", devToken);

            const devUser = { first_name: "Dev", last_name: "User", role: "admin" };
            setUser(devUser);
            sessionStorage.setItem("user", JSON.stringify(devUser));
        }

        setLoading(false);
    }, []);

    const login = (token: string, userData: UserDetail, rememberMe: boolean) => {
        setToken(token);
        setUser(userData);
        saveAuthData(token, userData, rememberMe);
        
        navigate('/project-management/projects', { replace: true });
    };

    const logout = () => {
        setToken(null);
        setUser(null);

        clearAuthData();

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
