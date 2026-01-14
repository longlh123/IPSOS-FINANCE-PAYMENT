import { Alert, Box, Button, Paper, Snackbar } from "@mui/material";
import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { useVisibility } from "../../hook/useVisibility";
import SearchTextBox from "../../components/SearchTextBox";
import { useTransactions } from "../../hook/useTransactions";
import { TransactionCellConfig } from "../../config/TransactionFieldsConfig";
import dayjs, { Dayjs } from 'dayjs';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { DesktopDatePicker } from '@mui/x-date-pickers/DesktopDatePicker';
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import { ColumnFormat } from "../../config/ColumnConfig";

const TransactionsManager = () => {
    const { id } = useParams<{id: string}>();
    const projectId = Number(id) || 0;

    const { transactions, meta, total, page, setPage, rowsPerPage, setRowsPerPage, searchTerm, setSearchTerm, loading, error  } = useTransactions(projectId);

    const { canView } = useVisibility();
    
    const [ formFieldsConfig, setFormFieldsConfig] = useState(TransactionCellConfig);
    const [ snackbarOpen, setSnackbarOpen ] = useState<boolean>(false);
    
    const columns: ColumnFormat[] = [
        ...formFieldsConfig
    ];

    const [selectedDate, setSelectedDate] = useState<Dayjs | null>(dayjs());

    const handleSearchChange = (value: string) => {
        setSearchTerm(value.toLocaleLowerCase());
    }

    const exportToExcel = (rows: any[], fileName='transactions.xlsx') => {

        if(!rows || rows.length === 0) return;

        const data = rows.map(row => ({
            'THÁNG': row.created_at,
            'STATUS': row.transaction_status,
            'NGƯỜI NẠP': "",
            'MÃ ĐƠN HÀNG': row.transaction_id,
            'MÃ REQUEST': row.transaction_id,
            'NGƯỜI TẠO': row.transaction_id,
            'LOẠI SẢN PHẨM': row.service_code,
            'THỜI GIAN GIAO DỊCH': row.created_at,
            'NHÀ CUNG CẤP': row.channel,
            'SỐ ĐIỆN THOẠI': row.phone_number,
            'MỆNH GIÁ (VNĐ)': row.amount,
            'NẠP THÀNH CÔNG (VNĐ)': row.amount,
            'CHIẾT KHẤU': row.discount,
            'THANH TOÁN': row.payment_amt,
            'TRẠNG THÁI': row.transaction_status,
            'ĐT/QUÀ': "",
            'TÊN DỰ ÁN': row.project_name,
            'KHU VỰC': row.province_name,
            'SỐ SYMPHONY': row.symphony,
            'THÀNH TIỀN': row.payment_per_tax,
            'TÀI KHOẢN': "",
            'TÌNH TRẠNH DỰ ÁN': "",
            'PO NUMBER': "",
            'VENDOR': row.channel,

        }));

        // 2. Tạo worksheet từ data
        const worksheet = XLSX.utils.json_to_sheet(data);

        // 3. Tạo workbook và thêm worksheet
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Transactions");

        // 4. Xuất file Excel
        const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
        const blob = new Blob([excelBuffer], { type: "application/octet-stream" });
        saveAs(blob, fileName);
    }

    useEffect(() => {
        if (error) {
            setSnackbarOpen(true);
        }
    }, [error]);

    return (
        <Box className="box-table">
            <div className="filter">
                <div className="filter-left">
                <h2 className="filter-title">Transactions</h2>
                </div>
                <div className="filter-right">
                {/* {canView("projects.functions.visible_add_new_project") && (
                    <Button className="btnAdd" onClick={() => {
                        setExportAll(false);
                        exportToExcel(transactions);
                    }}>
                    Export Excel
                    </Button>
                )} */}
                </div>
            </div>
            <div className="filter">
                {/* LEFT: Add button */}
                <div className="filter-left">
                <LocalizationProvider dateAdapter={AdapterDayjs}>
                    <DesktopDatePicker
                    views={['year', 'month']}
                    label="Filter by Month"
                    value={selectedDate}
                    onChange={(value) => {
                        setSelectedDate(value);
                        // handleDateChange(value);
                    }}
                    />
                </LocalizationProvider>
                </div>
    
                {/* RIGHT: Search + Date filter */}
                <div className="filter-right">
                <SearchTextBox
                    placeholder="Search project name, internal code,..."
                    onSearchChange={handleSearchChange}
                />
                </div>
            </div>
            <Snackbar
                open={snackbarOpen}
                autoHideDuration={10000}
                onClose={() => setSnackbarOpen(false)}
                anchorOrigin={{ vertical: "top", horizontal: "center" }}
                sx={{ zIndex: 9999 }}
            >
                <Alert severity= {error ? "error" : "success"} onClose={() => setSnackbarOpen(false)}>
                    {error}
                </Alert>
                
            </Snackbar>
            <Paper sx={{ height: 700, width: '100%', p: 2, overflowX: 'auto' }}>
                
            </Paper>
        </Box>
    )
}

export default TransactionsManager;