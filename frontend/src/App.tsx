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
import SubmitPhoneNumber from "./pages/VinnetAPI/SubmitPhoneNumber";
import Page404 from "./pages/Page404/Page404";
import Page200 from "./pages/Page200/Page200";
import TechcombankLayout from "./Layouts/TechcombankLayout";
import { TechcombankRoutes } from "./routes/TechcombankRoutes";
import GotItRequest from "./pages/GotItAPI/GotItRequest";

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
            <Route path="/vinnet-management/project/:url" element={<SubmitPhoneNumber/>} />
            <Route path="/got-it-management/project/:url" element={<GotItRequest/>} />
            <Route path="/techcombank/home" element={<Navigate to="/techcombank/dashboard" replace />} />
            <Route path="/login" element={<ProtectedRoute><Navigate to="/employees-management/part-time-employees" replace /></ProtectedRoute>} />
            <Route path="/page200" element={< Page200 messageSuccess="" />} />
            <Route path="/" element={<Login />} />
            { 
              DefaultRoute.map((item, index) => {
                if (item.path !== '/login'){
                  return (
                    <Route 
                      key={index}
                      path={item.path}
                      element={ 
                        <ProtectedRoute>
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
            <Route path="*" element={<Page404 errMessage="Sorry, the page you are looking for does not exist. Please contact the Admistrator for asistance."/>} /> {/* This route will catch all undefined paths */}
          </Routes>
        </AuthProvider>
      </Router>
  );
};

export default App;