// ProtectedRoute.tsx
import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles?: string[]
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children, allowedRoles: _allowedRoles }) => {
    const { token, user } = useAuth();
    const location = useLocation();

    if (!token) {
      return <Navigate to="/login" state={{ from: location }} replace />;
    }

    if (user?.must_change_password) {
      return <Navigate to="/set-password" replace />;
    }

    if (location.pathname === '/') {
      return <Navigate to="/project-management/projects" replace />;
    }

    // Nếu có giới hạn role -> kiểm tra theo role từ login response.
    // if(!isAllowedRole(user?.role, allowedRoles)){
    //   return <Navigate
    //           to="/error"
    //           state={{
    //             errorCode: 3,
    //             errorMessage: "Bạn không có quyền truy cập trang này."
    //           }}
    //           replace
    //         />
    // }
  
    return <>{children}</>;
};

export default ProtectedRoute;
