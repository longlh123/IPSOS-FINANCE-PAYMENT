import { useState } from "react";
import Navbar from "../components/Navbar/Navbar";
import Sidebar from "../components/Sidebar/Sidebar";
import { truncate } from "fs";

type children = {
  children: React.ReactNode;
};

const DefaultLayout: React.FC<children> = ({ children }) => {
  const [isSidebarOpen, setIsSidebarOpen] = useState<boolean>(true);

  const toggleSidebar = () => {
    setIsSidebarOpen(!isSidebarOpen);
  };

  return (
    <>
      <div className="wrapper">
        <Sidebar isOpen={isSidebarOpen} toggleSidebar={toggleSidebar} />
        <div className="content">
          <Navbar
            navbarFullWidth={isSidebarOpen}
            toggleSidebar={toggleSidebar}
          />
          <div className="content-detail">{children}</div>
        </div>
      </div>
    </>
  );
};

export default DefaultLayout;
