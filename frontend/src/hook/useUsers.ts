import { useCallback, useEffect, useState } from "react";
import { isAxiosError } from "axios";
import axios from "../config/axiosInstance";
import { ApiConfig } from "../config/ApiConfig";

export interface UserRow {
  id: number;
  name: string;
  email: string;
  first_name: string | null;
  last_name: string | null;
  role: string | null;
}

export interface UserDepartmentOption {
  id: number;
  name: string;
}

export interface UserRoleOption {
  id: number;
  name: string;
  department_id: number;
}

export interface CreateUserPayload {
  name?: string;
  email: string;
  password: string;
  password_confirmation: string;
  first_name: string;
  last_name: string;
  department_id: number;
  role_id: number;
}

export const useUsers = () => {
  const [users, setUsers] = useState<UserRow[]>([]);
  const [departments, setDepartments] = useState<UserDepartmentOption[]>([]);
  const [roles, setRoles] = useState<UserRoleOption[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  const resolveAxiosMessage = (err: unknown, fallback: string) => {
    if (!isAxiosError(err)) return fallback;

    const data = err.response?.data as any;
    if (data?.errors && typeof data.errors === "object") {
      const firstField = Object.keys(data.errors)[0];
      const firstError = firstField ? data.errors[firstField]?.[0] : null;
      if (firstError) return firstError;
    }

    return data?.message ?? err.message ?? fallback;
  };

  const fetchUsers = useCallback(async () => {
    try {
      setLoading(true);
      setError(false);
      setMessage(null);

      const response = await axios.get(ApiConfig.user.viewUsers);
      setUsers(response.data?.data ?? []);
    } catch (err: unknown) {
      setError(true);
      setMessage(resolveAxiosMessage(err, "Failed to fetch users"));
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchUserMetadata = useCallback(async () => {
    try {
      const response = await axios.get(ApiConfig.user.viewUserMetadata);
      setDepartments(response.data?.data?.departments ?? []);
      setRoles(response.data?.data?.roles ?? []);
    } catch (err: unknown) {
      setError(true);
      setMessage(resolveAxiosMessage(err, "Failed to fetch user metadata"));
    }
  }, []);

  const createUser = useCallback(async (payload: CreateUserPayload) => {
    try {
      setLoading(true);
      setError(false);
      setMessage(null);

      const response = await axios.post(ApiConfig.user.createUser, payload);
      await fetchUsers();

      setMessage(response.data?.message ?? "User created successfully.");
      return response.data;
    } catch (err: unknown) {
      setError(true);
      setMessage(resolveAxiosMessage(err, "Failed to create user"));

      throw err;
    } finally {
      setLoading(false);
    }
  }, [fetchUsers]);

  useEffect(() => {
    fetchUsers();
    fetchUserMetadata();
  }, [fetchUsers, fetchUserMetadata]);

  return {
    users,
    departments,
    roles,
    loading,
    error,
    message,
    fetchUsers,
    createUser,
  };
};
