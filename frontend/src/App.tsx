import React from "react";
import Login from "./pages/Auth/Login";

import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
} from "react-router-dom";
import { DefaultRoute } from "./routes/Route";
import { AuthProvider } from "./contexts/AuthContext";

import axios from "axios";
import DefaultLayout from "./Layouts/DefaultLayout";

import ProtectedRoute from "./routes/ProtectedRoute";
import VinnetRequest from "./pages/VinnetAPI/VinnetRequest";
import ErrorPage from "./pages/ErrorPage/ErrorPage";
import Page200 from "./pages/Page200/Page200";
import TechcombankLayout from "./Layouts/TechcombankLayout";
import { TechcombankRoutes } from "./routes/TechcombankRoutes";
import GotItRequest from "./pages/GotItAPI/GotItRequest";
import GiftRejectionRequest from "./pages/VinnetAPI/GiftRejectionRequest";

// Fetch the CSRF token from the meta tag
const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute("content");

// Set the CSRF token as a default header for Axios
axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;

const App: React.FC = () => {
  return (
      <Router>
        <AuthProvider>
          <Routes>
            {/* ✅ Public routes (không yêu cầu đăng nhập) */}
            <Route path="/" element={<Login />} />
            <Route path="/login" element={<Login />} />
            <Route path="/vinnet-management/project/:serviceType/:url" element={<VinnetRequest/>} />
            <Route path="/got-it-management/project/:url" element={<GotItRequest/>} />
            <Route path="/gift-management/rejection/:url" element={<GiftRejectionRequest/>} />
            <Route path="/page200" element={< Page200 messageSuccess="" />} />
            <Route path="/error" element={<ErrorPage />} />

            {/* ✅ Protected routes (yêu cầu đăng nhập) */}
            <Route path="/techcombank/home" element={<Navigate to="/techcombank/dashboard" replace />} />

            { 
              DefaultRoute.map((item, index) => {
                if (item.path !== '/login') {
                  return (
                    <Route 
                      key={index}
                      path={item.path}
                      element={ 
                        <ProtectedRoute allowedRoles={item.roles}>
                          <DefaultLayout>
                            <item.component />
                          </DefaultLayout>
                        </ProtectedRoute> 
                      }
                    /> 
                  )
                }
              })
            }
            {
              TechcombankRoutes.map((item, index) => {
                return (
                  <Route 
                    key={index}
                    path={item.path}
                    element={
                      <TechcombankLayout>
                        <item.component/>
                      </TechcombankLayout>
                    }
                  />
                )
              })
            }

            {/* ✅ 404 fallback */}
            <Route 
              path="*" 
              element={
                <Navigate
                  to="/error"
                  state={{
                    errorCode: 4,
                    errorMessage: "Sorry, the page you are looking for does not exist. Please contact the Admistrator for asistance."
                  }}
                  replace
                />
              } /> 
          </Routes>
        </AuthProvider>
      </Router>
  );
};

export default App;