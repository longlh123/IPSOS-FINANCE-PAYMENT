import React from "react";
import Login from "./pages/Auth/Login";

import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
} from "react-router-dom";
import { AuthProvider } from "./contexts/AuthContext";

import axios from "axios";
import DefaultLayout from "./Layouts/DefaultLayout";

import ProtectedRoute from "./routes/ProtectedRoute";
import ErrorPage from "./pages/ErrorPage/ErrorPage";
import Page200 from "./pages/Page200/Page200";
import ProjectLayout from "./Layouts/ProjectLayout";
import Projects from "./pages/Project/Projects";
import Quotation from "./pages/Project/Quotation/Quotation";
import ParttimeEmployees from "./pages/Project/ParttimeEmployees";
import MiniCATI from "./pages/MiniCATI/MiniCATI";

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
            {/* ================= PUBLIC ROUTES ================= */}
            <Route path="/" element={<Login />} />
            <Route path="/login" element={<Login />} />
            <Route path="/page200" element={<Page200 messageSuccess="" />} />
            <Route path="/error" element={<ErrorPage />} />

            <Route path="/mini-cati" element={<MiniCATI />} />

            {/* ================= DEFAULT LAYOUT GROUP ================= */}
            <Route
              element={
                <ProtectedRoute>
                  <DefaultLayout />
                </ProtectedRoute>
              }
            >
              <Route
                path="/project-management/projects"
                element={<Projects />}
              />
            </Route>

            {/* ================= PROJECT LAYOUT GROUP ================= */}
            <Route
              path="/project-management/projects/:id"
              element={
                <ProtectedRoute allowedRoles={["admin", "scripter"]}>
                  <ProjectLayout />
                </ProtectedRoute>
              }
            >
              <Route
                path="quotation"
                element={<Quotation />}
              />
              <Route
                path="parttime-employees"
                element={<ParttimeEmployees />}
              />
            </Route>
            
            {/* ================= 404 ================= */}
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