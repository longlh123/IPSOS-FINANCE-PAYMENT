import "../Navbar/Navbar.css";
import { useState } from "react";
import IconSolar from "../Icon/IconSolar";
import AccountMenu from "./AccountMenu";
import IconMoon from "../Icon/IconMoon";
import { IconButton } from "@mui/material";
import IconNotify from "../Icon/IconNotify";
import IconSun from "../Icon/IconSun";

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
        <div className="nav-right">
          <AccountMenu />
          {/* <IconButton onClick={handleDarkModeToggle} sx={styleIconNavbar}>
            {darkMode ? <IconSun /> : <IconMoon />}
          </IconButton>
          <IconButton sx={styleIconNavbar}>
            <img
              src="https://cdn-icons-png.flaticon.com/128/197/197473.png"
              alt=""
              width="22"
              height="22"
            ></img>
          </IconButton>
          <IconButton sx={styleIconNavbar}>
            <IconNotify />
          </IconButton> */}
        </div>
      </div>
    </>
  );
};

export default Navbar;
