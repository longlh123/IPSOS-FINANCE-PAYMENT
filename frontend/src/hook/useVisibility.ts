import { buildVisibility, RoleName } from "../config/RoleConfig";
import { useAuth } from "../contexts/AuthContext";

export const useVisibility = () => {
  const { user } = useAuth();
  const role = user?.role ?? "Admin";

  const config = buildVisibility(role as RoleName);

  const canView = (path: string): boolean => {
    const keys = path.split(".");
    let current: any = config;
    for (const key of keys) {
      if (!current || !(key in current)) return false;
      current = current[key];
    }
    return !!current;
  };

  return { canView, config };
};