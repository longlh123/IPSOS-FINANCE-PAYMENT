import React from "react";
import { Box, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Typography } from "@mui/material";
import { getStoredUser } from "../../utils/authStorage";

const AccountInfo: React.FC = () => {
  const user = getStoredUser<any>();

  const rows = user
    ? [
        { label: "ID", value: user.id ?? "-" },
        { label: "Tên đăng nhập", value: user.username ?? "-" },
        { label: "Tên", value: user.first_name ?? "-" },
        { label: "Họ", value: user.last_name ?? "-" },
        { label: "Email", value: user.email ?? "-" },
        { label: "Vai trò", value: user.role ?? "-" },
      ]
    : [];

  return (
    <Box className="box-table">
      <Paper sx={{ p: 2 }}>
        <Typography variant="h6" sx={{ mb: 2 }}>
          Thông tin tài khoản
        </Typography>

        <TableContainer>
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell sx={{ width: "35%", fontWeight: 600 }}>Trường thông tin</TableCell>
                <TableCell sx={{ fontWeight: 600 }}>Giá trị</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {rows.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={2} align="center">
                    Không tìm thấy dữ liệu tài khoản
                  </TableCell>
                </TableRow>
              ) : (
                rows.map((row) => (
                  <TableRow key={row.label}>
                    <TableCell>{row.label}</TableCell>
                    <TableCell>{String(row.value)}</TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      </Paper>
    </Box>
  );
};

export default AccountInfo;
