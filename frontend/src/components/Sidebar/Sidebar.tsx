/* eslint-disable jsx-a11y/anchor-is-valid */
import GridViewIcon from "@mui/icons-material/GridView";
import "../Sidebar/Sidebar.css";
import { Drawer, Divider } from "@mui/material";
import { useState, useEffect } from "react";
import { NavLink } from "react-router-dom";
import logo from "../../assets/img/Ipsos logo.png";
import { LibraryBooks } from "@mui/icons-material";
import { useVisibility } from "../../hook/useVisibility";

interface SideBarProps {
  isOpen: boolean;
  toggleSidebar: () => void;
}

const Sidebar: React.FC<SideBarProps> = ({ isOpen, toggleSidebar }) => {
  const [isSmallScreen, setIsSmallScreen] = useState<boolean>(false);

  const storedUser = localStorage.getItem("user");
  const user = storedUser ? JSON.parse(storedUser) : null;

  const { canView } = useVisibility();

  useEffect(() => {
    const mediaQuery = window.matchMedia("(max-width: 767px)"); // mobile < 768px
    setIsSmallScreen(mediaQuery.matches);

    const handleResize = (e: MediaQueryListEvent) => {
      setIsSmallScreen(e.matches);
    };

    mediaQuery.addEventListener("change", handleResize);
    return () => {
      mediaQuery.removeEventListener("change", handleResize);
    };
  }, []);

  return (
    <div className={`sidebar ${isOpen ? "open" : "close"}`}>
      <div className="logo-sidebar">
        <img src={logo} alt="Logo" />
      </div>

      <div className="menu">
        <div className="header-title-menu">
          <span>Project management</span>
        </div>

        <ul className="menu-links">
          {canView("sidebar.visible_projects") && (
            <li className="nav-link">
              <NavLink
                to="/project-management/projects"
                onClick={() => {
                  if (isSmallScreen) toggleSidebar(); // chỉ đóng nếu là mobile
                }}
              >
                <i className="icon">
                  <LibraryBooks style={{ fontSize: "18px" }} />
                </i>
                <span className="text nav-text">Projects</span>
              </NavLink>
            </li>
          )}
          
          {canView("sidebar.visible_transactions") && (
            <li className="nav-link">
              <NavLink
                to="/vinnet-management/index"
                onClick={() => {
                  if (isSmallScreen) toggleSidebar();
                }}
              >
                <i className="icon">
                  <GridViewIcon style={{ fontSize: "18px" }} />
                </i>
                <span className="text nav-text">Transactions</span>
              </NavLink>
            </li>
          )}
          {canView("sidebar.visible_transactions") && (
            <li className="nav-link">
              <NavLink
                to="/project-management/transactions"
                onClick={() => {
                  if (isSmallScreen) toggleSidebar();
                }}
              >
                <i className="icon">
                  <GridViewIcon style={{ fontSize: "18px" }} />
                </i>
                <span className="text nav-text">Transactions (New)</span>
              </NavLink>
            </li>
          )}
        </ul>

        <Divider style={{ margin: "18px 0" }} />
      </div>
    </div>
  );
};

export default Sidebar;
