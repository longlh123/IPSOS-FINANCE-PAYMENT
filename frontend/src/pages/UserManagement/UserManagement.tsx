import React, { useMemo, useState } from "react";
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
  Stack,
  TextField,
} from "@mui/material";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { useUsers } from "../../hook/useUsers";

interface CreateUserFormState {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
  department_id: string;
  role_id: string;
}

const initialFormState: CreateUserFormState = {
  first_name: "",
  last_name: "",
  email: "",
  password: "",
  password_confirmation: "",
  department_id: "",
  role_id: "",
};

const UserManagement: React.FC = () => {
  const { users, departments, roles, loading, error, message, createUser } = useUsers();
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [searchTerm, setSearchTerm] = useState("");
  const [openCreateDialog, setOpenCreateDialog] = useState(false);
  const [formState, setFormState] = useState<CreateUserFormState>(initialFormState);

  const filteredRoles = useMemo(() => {
    if (!formState.department_id) return [];
    return roles.filter((role) => role.department_id === Number(formState.department_id));
  }, [roles, formState.department_id]);

  const filteredUsers = useMemo(() => {
    const keyword = searchTerm.trim().toLowerCase();
    if (!keyword) return users;

    return users.filter((user) => {
      const fullName = `${user.first_name ?? ""} ${user.last_name ?? ""}`.trim();

      return [
        String(user.id),
        user.email ?? "",
        user.role ?? "",
        fullName,
      ]
        .join(" ")
        .toLowerCase()
        .includes(keyword);
    });
  }, [users, searchTerm]);

  const paginatedUsers = useMemo(() => {
    const start = page * rowsPerPage;
    return filteredUsers.slice(start, start + rowsPerPage);
  }, [filteredUsers, page, rowsPerPage]);

  const columns: ColumnFormat[] = [
    { label: "ID", name: "id", type: "number", align: "left" },
    {
      label: "Full Name",
      name: "full_name",
      type: "string",
      renderCell: (row) => `${row.first_name ?? ""} ${row.last_name ?? ""}`.trim() || "-",
    },
    { label: "Email", name: "email", type: "string" },
    {
      label: "Role",
      name: "role",
      type: "string",
      renderCell: (row) => row.role ?? "-",
    },
  ];

  const handleOpenCreateDialog = () => {
    setFormState(initialFormState);
    setOpenCreateDialog(true);
  };

  const handleCloseCreateDialog = () => {
    setOpenCreateDialog(false);
  };

  const handleTextChange = (field: keyof CreateUserFormState, value: string) => {
    setFormState((prev) => ({
      ...prev,
      [field]: value,
    }));
  };

  const handleDepartmentChange = (event: SelectChangeEvent<string>) => {
    const departmentId = event.target.value;
    setFormState((prev) => ({
      ...prev,
      department_id: departmentId,
      role_id: "",
    }));
  };

  const handleRoleChange = (event: SelectChangeEvent<string>) => {
    const roleId = event.target.value;
    setFormState((prev) => ({
      ...prev,
      role_id: roleId,
    }));
  };

  const handleCreateUser = async () => {
    try {
      await createUser({
        name: formState.email,
        email: formState.email,
        password: formState.password,
        password_confirmation: formState.password_confirmation,
        first_name: formState.first_name,
        last_name: formState.last_name,
        department_id: Number(formState.department_id),
        role_id: Number(formState.role_id),
      });

      setOpenCreateDialog(false);
    } catch {
      // Error state is handled in useUsers and shown by ReusableTable alert.
      // Keep dialog open so user can correct the form values.
    }
  };

  const isCreateDisabled =
    loading ||
    !formState.first_name.trim() ||
    !formState.last_name.trim() ||
    !formState.email.trim() ||
    !formState.password ||
    !formState.password_confirmation ||
    !formState.department_id ||
    !formState.role_id;

  return (
    <Box>
      <ReusableTable
        title="User Management"
        columns={columns}
        data={paginatedUsers}
        loading={loading}
        error={error}
        message={message}
        page={page}
        rowsPerPage={rowsPerPage}
        total={filteredUsers.length}
        onPageChange={(_, nextPage) => setPage(nextPage)}
        onRowsPerPageChange={(event) => {
          const nextRowsPerPage = parseInt(event.target.value, 10);
          setRowsPerPage(nextRowsPerPage);
          setPage(0);
        }}
        topToolbar={
          <Stack
            direction={{ xs: "column", sm: "row" }}
            spacing={1.5}
            justifyContent="space-between"
            alignItems={{ xs: "stretch", sm: "center" }}
            sx={{ p: 2, pb: 0 }}
          >
            <TextField
              label="Search users"
              variant="outlined"
              size="small"
              value={searchTerm}
              onChange={(event) => {
                setSearchTerm(event.target.value);
                setPage(0);
              }}
              sx={{ width: "100%", maxWidth: 320 }}
            />

            <Button variant="contained" onClick={handleOpenCreateDialog}>
              Create User
            </Button>
          </Stack>
        }
      />

      <Dialog open={openCreateDialog} onClose={handleCloseCreateDialog} fullWidth maxWidth="sm">
        <DialogTitle>Create New User</DialogTitle>
        <DialogContent>
          <Stack spacing={2} sx={{ mt: 1 }}>
            <Stack direction={{ xs: "column", sm: "row" }} spacing={2}>
              <TextField
                label="First Name"
                value={formState.first_name}
                onChange={(event) => handleTextChange("first_name", event.target.value)}
                fullWidth
                required
              />
              <TextField
                label="Last Name"
                value={formState.last_name}
                onChange={(event) => handleTextChange("last_name", event.target.value)}
                fullWidth
                required
              />
            </Stack>

            <TextField
              label="Email"
              type="email"
              value={formState.email}
              onChange={(event) => handleTextChange("email", event.target.value)}
              fullWidth
              required
            />

            <Stack direction={{ xs: "column", sm: "row" }} spacing={2}>
              <TextField
                label="Password"
                type="password"
                value={formState.password}
                onChange={(event) => handleTextChange("password", event.target.value)}
                fullWidth
                required
              />
              <TextField
                label="Confirm Password"
                type="password"
                value={formState.password_confirmation}
                onChange={(event) => handleTextChange("password_confirmation", event.target.value)}
                fullWidth
                required
              />
            </Stack>

            <FormControl fullWidth required>
              <InputLabel id="department-label">Department</InputLabel>
              <Select
                labelId="department-label"
                label="Department"
                value={formState.department_id}
                onChange={handleDepartmentChange}
              >
                {departments.map((department) => (
                  <MenuItem key={department.id} value={String(department.id)}>
                    {department.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>

            <FormControl fullWidth required disabled={!formState.department_id}>
              <InputLabel id="role-label">Role</InputLabel>
              <Select
                labelId="role-label"
                label="Role"
                value={formState.role_id}
                onChange={handleRoleChange}
              >
                {filteredRoles.map((role) => (
                  <MenuItem key={role.id} value={String(role.id)}>
                    {role.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Stack>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseCreateDialog}>Cancel</Button>
          <Button variant="contained" onClick={handleCreateUser} disabled={isCreateDisabled}>
            Create
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default UserManagement;
