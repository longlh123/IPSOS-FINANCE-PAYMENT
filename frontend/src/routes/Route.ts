import ConfirmPassword from "../pages/Auth/ConfirmPassword";
import Projects from "../pages/Project/Projects";
import EditProject from "../pages/Project/EditProject";
import VinnetManagement from "../pages/VinnetAPI/VinnetManagement";
import GiftManagement from "../pages/Project/GiftManagement";

export const DefaultRoute = [
  {
    path: "/confirmpassword",
    component: ConfirmPassword,
  },
  {
    path: "/project-management/projects",
    component: Projects,
  }, 
  {
    path: "/project-management/projects/:id",
    component: EditProject,
  },
  {
    path: "/project-management/projects/:id/gift-management",
    component: GiftManagement,
  },
  {
    path: "/vinnet-management/index",
    component: VinnetManagement,
  }
];
