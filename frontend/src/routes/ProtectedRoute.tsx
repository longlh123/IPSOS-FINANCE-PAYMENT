// ProtectedRoute.tsx
import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles?: string[]
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children, allowedRoles }) => {
    const { token, user } = useAuth();
    const location = useLocation();

    //Chưa đăng nhập → quay về trang login
    if (!token) {
      return <Navigate to="/login" state={{ from: location }} replace />;
    }
    
    //Nếu đã login và cố vào "/" → chuyển sang trang chính
    if (location.pathname === '/') {
      return <Navigate to="/project-management/projects" replace />;
    }

    //Nếu có giới hạn role → kiểm tra
    if(allowedRoles && user && user.role && !allowedRoles.includes(user.role.toLowerCase())){
      return <Navigate
                to="/error"
                state={{
                  errorCode: 3,
                  errorMessage: "Bạn không có quyền truy cập trang này."
                }}
                replace
              />
    }
  
    return <>{children}</>;
};

export default ProtectedRoute;
