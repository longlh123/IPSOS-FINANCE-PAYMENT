import React, { ReactNode, useEffect, useState } from "react";
import { ColumnFormat } from "../../config/ColumnConfig";
import CloseIcon from "@mui/icons-material/Close";
import { Alert, Box, CircularProgress, IconButton, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TablePagination, TableRow } from "@mui/material";

type ActionState = {
    fetch?: {
        loading?: boolean,
        error?: boolean
        message?: string
    };
    import?: {
        loading?: boolean,
        error?: boolean,
        message?: string
    };
    delete?: {
        loading?: boolean,
        error?: boolean,
        message?: string
    }
}

interface ReusableTableProps {
    title: string;
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
};

const ReusableTable: React.FC<ReusableTableProps> = ({
    title,
    columns,
    data=[],
    actionStatus,
    page = 0,
    rowsPerPage = 10,
    total = 0,
    onPageChange,
    onRowsPerPageChange,
    filters,
    actions,
    topToolbar
}) => {
    const [ openAlert, setOpenAlert] = useState(false);

    const fetchLoading = actionStatus.fetch?.loading;
    const fetchError = actionStatus.fetch?.error;
    const fetchMessage = actionStatus.fetch?.message;

    const deleteLoading = actionStatus.delete?.loading;
    const deleteError = actionStatus.delete?.error;
    const deleteMessage = actionStatus.delete?.message;

    const importLoading = actionStatus.import?.loading;
    const importError = actionStatus.import?.error;
    const importMessage = actionStatus.import?.message;

    useEffect(() => {
        if(fetchMessage || importMessage || deleteMessage){
            setOpenAlert(true);
        } else {
            setOpenAlert(false);
        }
    }, [fetchMessage, importMessage, deleteMessage])

    return (
        <Box className="box-table">
            {openAlert && (
                <Box sx={{ p: 2 }}>
                    {(fetchMessage) && (
                        <Alert 
                            severity= {fetchError ? "error" : "success"} 
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
                            {fetchMessage}
                        </Alert>
                    )}
                    {(importMessage) && (
                        <Alert 
                            severity= {importError ? "error" : "success"} 
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
                            {importMessage}
                        </Alert>
                    )}
                    {(deleteMessage) && (
                        <Alert 
                            severity= {deleteError ? "error" : "success"} 
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
                            {deleteMessage}
                        </Alert>
                    )}
                </Box>
            )}
            <TableContainer component={Paper} className="table-container">
                {topToolbar}

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
                            fetchLoading ? (
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