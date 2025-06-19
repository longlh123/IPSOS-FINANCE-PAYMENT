// ProtectedRoute.tsx
import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children }) => {
    const { token } = useAuth();
    const location = useLocation();
  
    if (!token) {
      return <Navigate to="/" state={{ from: location }} replace />;
    }
  
    if (location.pathname === '/') {
      return <Navigate to="/project-management/projects" replace />;
    }
  
    return <>{children}</>;
};

export default ProtectedRoute;
