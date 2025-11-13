import ConfirmPassword from "../pages/Auth/ConfirmPassword";
import Projects from "../pages/Project/Projects";
import ProjectSettings from "../pages/Project/ProjectSettings";
import EditProject from "../pages/Project/ProjectSettings";
import VinnetManagement from "../pages/VinnetAPI/VinnetManagement";
import GiftManagement from "../pages/Project/GiftManagement";
import TableParttimeEmployees from "../components/Table/TableParttimeEmployees";

export const DefaultRoute = [
  {
    path: "/confirmpassword",
    component: ConfirmPassword,
  },
  {
    path: "/project-management/projects",
    component: Projects,
    roles: ["admin", "scripter"]
  }, 
  {
    path: "/project-management/projects/:id/settings",
    component: ProjectSettings,
    roles: ["admin", "scripter"]
  },
  {
    path: "/project-management/projects/:id/gift-management",
    component: GiftManagement,
  },
  {
    path: "project-management/projects/:id/parttime-employees",
    component: TableParttimeEmployees,
  },
  {
    path: "/vinnet-management/index",
    component: VinnetManagement,
  }
];
