import "../Navbar/Navbar.css";
import { useState } from "react";
import IconSolar from "../Icon/IconSolar";
import { IconButton } from "@mui/material";
import NotificationBell from "./NotificationBell";

interface NavBarProps {
  toggleSidebar: () => void;
  navbarFullWidth: boolean;
}
const Navbar: React.FC<NavBarProps> = ({ toggleSidebar, navbarFullWidth }) => {
  const [ darkMode, setDarkMode ] = useState();
  
  const handleDarkModeToggle = () => {
    //setDarkMode((prevDarkMode) => !prevDarkMode);
    document.body.classList.toggle("dark");
  };

  const styleIconNavbar = {
    color: darkMode ? "var(--text-color)" : "",
  };

  return (
    <>
      <div className={navbarFullWidth ? "navbar" : "navbar fullWidth"}>
        <div className="nav-left">
          <div className="toggle-open">
            <IconButton sx={styleIconNavbar} onClick={toggleSidebar}>
              <IconSolar />
            </IconButton>
          </div>
        </div>
        <div
          className="nav-right"
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px'
          }}
        >
          <NotificationBell />
        </div>
      </div>
    </>
  );
};

export default Navbar;
