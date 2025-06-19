import "../assets/css/techcombank.css";
import React, { useState } from 'react';
import { Box, CssBaseline, Toolbar } from '@mui/material';
import TechcombankNavbar from '../components/Navbar/TechcombankNavbar';
import TechcombankSidebar from '../components/Sidebar/TechcombankSidebar';
import { Dashboard } from '@mui/icons-material';

type children = {
  children: React.ReactNode;
};

const TechcombankLayout: React.FC<children> = ({ children }) => {
  const [isSidebarOpen, setIsSidebarOpen] = useState<boolean>(false);

  const toggleSidebar = () => {
    setIsSidebarOpen(!isSidebarOpen);
  };

  return (
    <>
      <div className="wrapper">
        <TechcombankSidebar isOpen={isSidebarOpen} toggleSidebar={toggleSidebar} />
        <div className="content">
          <TechcombankNavbar
            navbarFullWidth={isSidebarOpen}
            toggleSidebar={toggleSidebar}
          />
          <div className="content-detail">{children}</div>
        </div>
      </div>
    </>
    // <>
    //     <CssBaseline />
    //     <TechcombankNavbar />
    //     <TechcombankSidebar />
    //     <div className="tcb-main">
    //         {children}
    //     </div>
    // </>
  );
};

export default TechcombankLayout;
