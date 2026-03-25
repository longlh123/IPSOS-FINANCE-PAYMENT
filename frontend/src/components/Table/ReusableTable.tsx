import React, { useEffect, useState } from "react";
import { ColumnFormat } from "../../config/ColumnConfig";
import CloseIcon from "@mui/icons-material/Close";
import { Alert, Box, Checkbox, CircularProgress, IconButton, Paper, Table, TableBody, TableCell, TableContainer, TableHead, TablePagination, TableRow, TableSortLabel } from "@mui/material";

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
    topToolbar?: React.ReactNode;
    rowSelectionEnabled?: boolean;
    selectedRowIds?: Array<number | string>;
    onSelectedRowIdsChange?: (ids: Array<number | string>) => void;
    getRowId?: (row: any) => number | string;
    isRowSelectable?: (row: any) => boolean;
    sortBy?: string;
    sortDirection?: "asc" | "desc";
    onSortChange?: (sortBy: string, sortDirection: "asc" | "desc") => void;
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
    actions,
    topToolbar,
    rowSelectionEnabled = false,
    selectedRowIds = [],
    onSelectedRowIdsChange,
    getRowId = (row: any) => row.id,
    isRowSelectable = () => true,
    sortBy,
    sortDirection = "asc",
    onSortChange
}) => {
    const [ openAlert, setOpenAlert] = useState(false);

    const selectableRows = data.filter((row) => isRowSelectable(row));
    const currentPageSelectableIds = selectableRows.map((row) => getRowId(row));

    const selectedOnCurrentPageCount = currentPageSelectableIds.filter((id) => selectedRowIds.includes(id)).length;
    const allCurrentPageSelected = currentPageSelectableIds.length > 0 && selectedOnCurrentPageCount === currentPageSelectableIds.length;
    const partiallySelected = selectedOnCurrentPageCount > 0 && selectedOnCurrentPageCount < currentPageSelectableIds.length;

    const handleToggleAllCurrentPage = (checked: boolean) => {
        if(!onSelectedRowIdsChange) return;

        if(checked){
            const merged = Array.from(new Set([...selectedRowIds, ...currentPageSelectableIds]));
            onSelectedRowIdsChange(merged);
            return;
        }

        const nextIds = selectedRowIds.filter((id) => !currentPageSelectableIds.includes(id));
        onSelectedRowIdsChange(nextIds);
    };

    const handleToggleRow = (row: any, checked: boolean) => {
        if(!onSelectedRowIdsChange) return;

        const rowId = getRowId(row);

        if(checked){
            onSelectedRowIdsChange(Array.from(new Set([...selectedRowIds, rowId])));
            return;
        }

        onSelectedRowIdsChange(selectedRowIds.filter((id) => id !== rowId));
    };

    const handleSortColumn = (col: ColumnFormat) => {
        if (!onSortChange) return;
        if (col.sortable === false) return;

        const nextSortBy = col.sortKey ?? col.name;
        const nextDirection: "asc" | "desc" = sortBy === nextSortBy && sortDirection === "asc" ? "desc" : "asc";
        onSortChange(nextSortBy, nextDirection);
    };

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
                    {message ?? ""}
                </Alert>
            )} 
            
            <TableContainer component={Paper} className="table-container">
                {topToolbar}

                <Table sx={{ tableLayout: 'auto', width: '100%' }}>
                    <TableHead className="header-table">
                        <TableRow>
                            {rowSelectionEnabled && (
                                <TableCell padding="checkbox" sx={{ width: 48 }}>
                                    <Checkbox
                                        indeterminate={partiallySelected}
                                        checked={allCurrentPageSelected}
                                        onChange={(event) => handleToggleAllCurrentPage(event.target.checked)}
                                        inputProps={{ "aria-label": "select all rows" }}
                                    />
                                </TableCell>
                            )}

                            {columns.map((col, idx) => (
                                (() => {
                                    const effectiveSortKey = col.sortKey ?? col.name;
                                    const isSortable = Boolean(onSortChange) && col.sortable !== false && col.type !== "menu";
                                    const isActiveSort = sortBy === effectiveSortKey;

                                    return (
                                <TableCell 
                                    key={idx}
                                    sx={{
                                        whiteSpace: "nowrap",
                                        overflow: "hidden",
                                        textOverflow: "ellipsis",
                                        width: col.name === "actions" ? 120 : "auto"
                                    }}
                                    align={col.align ?? "left"}
                                >
                                    {isSortable ? (
                                        <TableSortLabel
                                            active={isActiveSort}
                                            direction={isActiveSort ? sortDirection : "asc"}
                                            onClick={() => handleSortColumn(col)}
                                        >
                                            { col.renderHeader ? col.renderHeader() : col.label }
                                        </TableSortLabel>
                                    ) : (
                                        col.renderHeader ? col.renderHeader() : col.label
                                    )}
                                </TableCell>
                                    );
                                })()
                            ))}
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {loading ? (
                            <TableRow>
                                <TableCell colSpan={columns.length + (rowSelectionEnabled ? 1 : 0)} align="center">
                                    <CircularProgress />
                                </TableCell>
                            </TableRow>
                        ) : (
                            data.map((row, i) => (
                                <TableRow key={i}>
                                    {rowSelectionEnabled && (
                                        <TableCell padding="checkbox">
                                            <Checkbox
                                                checked={selectedRowIds.includes(getRowId(row))}
                                                disabled={!isRowSelectable(row)}
                                                onChange={(event) => handleToggleRow(row, event.target.checked)}
                                                inputProps={{ "aria-label": `select row ${i + 1}` }}
                                            />
                                        </TableCell>
                                    )}

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
                        )}
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