import ConfirmPassword from "../pages/Auth/ConfirmPassword";
import Projects from "../pages/Project/Projects";
import ProjectSettings from "../pages/Project/ProjectSettings";
import EditProject from "../pages/Project/ProjectSettings";
import VinnetManagement from "../pages/VinnetAPI/VinnetManagement";
import Transactions from "../pages/Project/Transactions";
import ParttimeEmployees from "../pages/Project/ParttimeEmployees";
import TransactionsManager from "../pages/Project/TransactionsManager";
import Gifts from "../pages/Project/Gifts";

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
    path: "/project-management/projects/:id/gifts",
    component: Gifts,
  },
  {
    path: "/project-management/projects/:id/transactions",
    component: Transactions,
  },
  {
    path: "project-management/projects/:id/parttime-employees",
    component: ParttimeEmployees,
  },
  // {
  //   path: "project-management/transactions",
  //   component: TransactionsManager
  // },
  {
    path: "/vinnet-management/index",
    component: VinnetManagement,
  }
];
