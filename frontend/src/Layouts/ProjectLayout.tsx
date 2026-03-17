import React, { useState } from 'react';
import { Outlet, useParams } from 'react-router-dom';
import ProjectSidebar from '../components/Sidebar/ProjectSidebar';
import Navbar from '../components/Navbar/Navbar';

const ProjectLayout: React.FC = () => {
  const { id } = useParams<{id: string}>();
  const [ isSidebarOpen, setIsSidebarOpen ] = useState(true);
  
  const toggleSidebar = () => setIsSidebarOpen(prev => !prev);

  return (
    <div className="wrapper">
      <ProjectSidebar
        projectId={Number(id)}
        isOpen={isSidebarOpen}
        toggleSidebar={toggleSidebar}
      />

      <div className="content">
        <Navbar
          navbarFullWidth={isSidebarOpen}
          toggleSidebar={toggleSidebar}
        />

        <div className="content-detail">
          <Outlet/>
        </div>
      </div>
    </div>
  );
};

export default ProjectLayout;