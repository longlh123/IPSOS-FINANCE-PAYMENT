import React, { useEffect, useState } from "react";
import { ColumnFormat } from "../../config/ColumnConfig";
import CloseIcon from "@mui/icons-material/Close";
import InboxOutlinedIcon from "@mui/icons-material/InboxOutlined";
import {
    Alert,
    Box,
    CircularProgress,
    Divider,
    IconButton,
    Paper,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TablePagination,
    TableRow,
    Typography,
} from "@mui/material";

export type ActionType = 'add' | 'fetch' | 'store' | 'clone' | 'approve' | 'submit' | 'import' | 'export' | 'update' | 'delete' | 'idle';

export interface ActionState {
    loading?: boolean;
    error?: boolean;
    message?: string;
    type?: ActionType;
}

interface ReusableTableProps {
    title?: string;
    columns: ColumnFormat[];
    data: any[];
    actionStatus: ActionState;
    total?: number;
    page?: number;
    rowsPerPage?: number;
    onPageChange?: (event: React.MouseEvent<HTMLButtonElement> | null, newPage: number) => void;
    onRowsPerPageChange?: (event: React.ChangeEvent<HTMLInputElement>) => void;
    filters?: React.ReactNode;
    actions?: (row: any) => React.ReactNode;
    topToolbar?: React.ReactNode;
}

const ReusableTable: React.FC<ReusableTableProps> = ({
    columns,
    data = [],
    actionStatus,
    page = 0,
    rowsPerPage = 10,
    total = 0,
    onPageChange,
    onRowsPerPageChange,
    topToolbar,
}) => {
    const [openAlert, setOpenAlert] = useState(false);

    useEffect(() => {
        setOpenAlert(Boolean(actionStatus.message));
    }, [actionStatus]);

    return (
        <Box sx={{ px: 2 }}>
            {openAlert && actionStatus.message && (
                <Alert
                    severity={actionStatus.error ? "error" : "success"}
                    sx={{ mb: 1.5, borderRadius: "8px", fontSize: "0.875rem" }}
                    action={
                        <IconButton
                            aria-label="close"
                            color="inherit"
                            size="small"
                            onClick={() => setOpenAlert(false)}
                        >
                            <CloseIcon fontSize="inherit" />
                        </IconButton>
                    }
                >
                    {actionStatus.message}
                </Alert>
            )}

            <Paper
                elevation={0}
                sx={{
                    border: "1px solid",
                    borderColor: "var(--body-color)",
                    borderRadius: "12px",
                    overflow: "hidden",
                    backgroundColor: "var(--background-color)",
                }}
            >
                {topToolbar && (
                    <>
                        <Box sx={{ px: 2, py: 1.25 }}>{topToolbar}</Box>
                        <Divider />
                    </>
                )}

                <TableContainer sx={{ maxHeight: "calc(100vh - 240px)", overflowX: "auto" }}>
                    <Table stickyHeader sx={{ tableLayout: "fixed", width: "100%" }}>
                        <TableHead>
                            <TableRow>
                                {columns.map((col, idx) => (
                                    <TableCell
                                        key={idx}
                                        align={col.align ?? "left"}
                                        sx={{
                                            backgroundColor: "var(--body-color)",
                                            fontWeight: 600,
                                            fontSize: "0.8125rem",
                                            color: "var(--text-color)",
                                            whiteSpace: "nowrap",
                                            overflow: "hidden",
                                            textOverflow: "ellipsis",
                                            width: col.width ?? "auto",
                                            py: 1.25,
                                            px: 1.5,
                                            borderBottom: "2px solid",
                                            borderBottomColor: "rgba(0, 157, 156, 0.2)",
                                        }}
                                    >
                                        {col.renderHeader ? col.renderHeader() : col.label}
                                    </TableCell>
                                ))}
                            </TableRow>
                        </TableHead>

                        <TableBody>
                            {actionStatus.loading ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={columns.length}
                                        align="center"
                                        sx={{ py: 6, border: 0 }}
                                    >
                                        <CircularProgress
                                            size={28}
                                            thickness={4}
                                            sx={{ color: "var(--main-color)" }}
                                        />
                                    </TableCell>
                                </TableRow>
                            ) : data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={columns.length}
                                        align="center"
                                        sx={{ py: 6, border: 0 }}
                                    >
                                        <InboxOutlinedIcon
                                            sx={{ fontSize: 40, color: "var(--text-primary-color)", mb: 1, opacity: 0.5 }}
                                        />
                                        <Typography
                                            variant="body2"
                                            sx={{ color: "var(--text-primary-color)", fontSize: "0.875rem" }}
                                        >
                                            Không có dữ liệu
                                        </Typography>
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.map((row, i) => (
                                    <TableRow
                                        key={i}
                                        sx={{
                                            transition: "background-color 0.15s ease",
                                            "&:hover": {
                                                backgroundColor: "rgba(0, 157, 156, 0.05)",
                                            },
                                            "&:last-child td": { border: 0 },
                                        }}
                                    >
                                        {columns.map((col, idx) => (
                                            <TableCell
                                                key={idx}
                                                align={col.align ?? "left"}
                                                sx={{
                                                    whiteSpace: "nowrap",
                                                    overflow: "hidden",
                                                    textOverflow: "ellipsis",
                                                    width: col.width ?? "auto",
                                                    fontSize: "0.8125rem",
                                                    color: "var(--text-color)",
                                                    py: 1,
                                                    px: 1.5,
                                                }}
                                            >
                                                {col.renderCell
                                                    ? col.renderCell(row)
                                                    : col.renderAction
                                                    ? col.renderAction(row)
                                                    : row[col.name]}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </TableContainer>

                <Divider />
                <TablePagination
                    component="div"
                    page={page}
                    count={total}
                    rowsPerPageOptions={[10, 25, 50, 100]}
                    rowsPerPage={rowsPerPage}
                    onPageChange={onPageChange ?? (() => {})}
                    onRowsPerPageChange={onRowsPerPageChange ?? (() => {})}
                    sx={{
                        fontSize: "0.8125rem",
                        "& .MuiTablePagination-selectLabel, & .MuiTablePagination-displayedRows": {
                            fontSize: "0.8125rem",
                        },
                    }}
                />
            </Paper>
        </Box>
    );
};

export default ReusableTable;
