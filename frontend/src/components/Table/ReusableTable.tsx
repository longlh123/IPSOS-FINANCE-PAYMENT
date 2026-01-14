import React, { ReactNode, useEffect, useState } from "react";
import { ColumnFormat } from "../../config/ColumnConfig";
import CloseIcon from "@mui/icons-material/Close";
import { Alert, Box, CircularProgress, IconButton, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TablePagination, TableRow } from "@mui/material";

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
    const [ openAlert, setOpenAlert] = useState(false);

    useEffect(() => {
        if(error || message){
            setOpenAlert(true);
        } else {
            setOpenAlert(false);
        }
    }, [error, message])

    return (
        <Box className="box-table">
            {openAlert  && (
                <Alert 
                    severity= {error ? "error" : "success"} 
                    sx={{ width: "100%", alignItems: "center", mb: 2 }}
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
                    <span 
                        dangerouslySetInnerHTML={{ __html: message ?? "" }}
                    ></span>
                </Alert>
            )} 
            
            <TableContainer component={Paper} className="table-container">
                <Table sx={{ tableLayout: 'auto', width: '100%' }}>
                    <TableHead className="header-table">
                        <TableRow>
                            {columns.map((col, idx) => (
                                <TableCell 
                                    key={idx}
                                    sx={{
                                        whiteSpace: "nowrap",
                                        overflow: "hidden",
                                        textOverflow: "ellipsis",
                                        width: col.name == "actions" ? 120 : "auto"
                                    }}
                                    align="left"
                                >
                                    { col.renderHeader ? col.renderHeader() : col.label }
                                </TableCell>
                            ))}
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {
                            loading ? (
                                <TableRow>
                                    <TableCell colSpan={columns.length} align="center">
                                        <CircularProgress />
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.map((row, i) => (
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
                                ))
                            )
                        }
                        
                    </TableBody>
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