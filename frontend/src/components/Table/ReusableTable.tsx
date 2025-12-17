import React, { ReactNode } from "react";
import { ColumnFormat } from "../../config/ColumnConfig";
import { Alert, Box, CircularProgress, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TablePagination, TableRow } from "@mui/material";
import SdCardAlertOutlinedIcon from '@mui/icons-material/SdCardAlertOutlined';

interface ReusableTableProps {
    title: string;
    columns: ColumnFormat[];
    data: any[];
    loading?: boolean;
    error?: boolean;
    message?: string | null;
    total?: number;
    page?: number;
    rowsPerPage?: number;
    onPageChange?: (event: React.MouseEvent<HTMLButtonElement> | null, newPage: number) => void;
    onRowsPerPageChange?: (event: React.ChangeEvent<HTMLInputElement>) => void;
    filters?: React.ReactNode;
    actions?: (row: any) => React.ReactNode;
};

const ReusableTable: React.FC<ReusableTableProps> = ({
    title,
    columns,
    data=[],
    loading,
    error = false,
    message = null,
    page = 0,
    rowsPerPage = 10,
    total = 0,
    onPageChange,
    onRowsPerPageChange,
    filters,
    actions
}) => {
    return (
        <Box className="box-table">
            {error && (
                <Alert severity= {error ? "error" : "success"} sx={{ width: "100%", alignItems: "center", mb: 2 }}>
                    <span 
                        dangerouslySetInnerHTML={{ __html: message ?? "" }}
                    ></span>
                </Alert>
            )} 
            
            <TableContainer component={Paper} className="table-container">
                <Table sx={{ tableLayout: 'auto', width: '100%' }}>
                    <TableHead className="header-table">
                        {columns.map((col, idx) => (
                            <TableCell 
                                key={idx}
                                sx={{
                                    whiteSpace: "nowrap",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis"
                                }}
                                align="center"
                            >
                                { col.renderHeader ? col.renderHeader() : col.label }
                            </TableCell>
                        ))}
                    </TableHead>
                    {
                        loading ? (
                            <Box display="flex" justifyContent="center" alignItems="center" height="100%">
                                <CircularProgress />
                            </Box>
                        ) : (
                            <TableBody>
                                {data.map((row, i) => (
                                    <TableRow
                                        key={i}
                                    >
                                        {columns.map((col, idx) => (
                                            <TableCell
                                                key={idx}
                                                sx={{
                                                    whiteSpace: "nowrap",
                                                    overflow: "hidden",
                                                    textOverflow: "ellipsis"
                                                }}
                                                align={ col.align ? col.align : "left" }
                                            >
                                                { col.renderCell ? (col.renderCell(row)) : (
                                                    col.renderAction ? (col.renderAction(row)) : (row[col.name]))}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))}
                            </TableBody>
                        )
                    }
                </Table>

                <TablePagination
                    component="div"
                    page={page}
                    count={total}
                    rowsPerPageOptions={[10, 25, 50, 100]}
                    rowsPerPage={rowsPerPage}
                    onPageChange={onPageChange ?? (() => {})}
                    onRowsPerPageChange={onRowsPerPageChange ?? (() => {})}
                ></TablePagination>
            </TableContainer>
        </Box>

    )
}

export default ReusableTable;