import "../../assets/css/table.css";
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import * as XLSX from 'xlsx';
import { saveAs } from 'file-saver';
import { Alert, Box, Button, Card, CardActions, CardContent, CardHeader, CircularProgress, Divider, FormControlLabel, FormLabel, InputAdornment, OutlinedInput, Paper, Radio, RadioGroup, SelectChangeEvent, Snackbar, Table, TableBody, TableCell, TableContainer, TableHead, TablePagination, TableRow } from "@mui/material";
import { Select, MenuItem, InputLabel, FormControl } from '@mui/material';
import Grid from '@mui/material/Grid';
import { ApiConfig } from '../../config/ApiConfig';
import dayjs, { Dayjs } from 'dayjs';
import { DatePicker, DatePickerProps } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { DesktopDatePicker } from '@mui/x-date-pickers/DesktopDatePicker';
import numeral from 'numeral';
import { TableCellConfig } from '../../config/TableVinnetConfig';
import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import  RadioButtonUncheckedOutlinedIcon  from '@mui/icons-material/RadioButtonUncheckedOutlined';
import SdCardAlertOutlinedIcon from '@mui/icons-material/SdCardAlertOutlined';
import ErrorOutlineOutlinedIcon from '@mui/icons-material/ErrorOutlineOutlined';
import FileDownloadOutlinedIcon from '@mui/icons-material/FileDownloadOutlined';
import LoadingButton from "@mui/lab/LoadingButton";
import { useMetadata } from "../../hook/useMetadata";
import { useProjects } from "../../hook/useProjects";

interface TransactionData {
    internal_code: string,
    project_name: string,
    shell_chainid: string,
    employee_id: string,
    first_name: string,
    last_name: string,
    province_name: string,
    interview_start: string,
    interview_end: string,
    phone_number: string,
    transaction_id: string,
    amount: number,
    discount: number,
    payment_amt: number,
    created_at: string,
    updated_at: string,
    channel: string,
    service_code: string,
    invoice_date: string
};

interface ProjectData {
    id: number,
    internal_code: string,
    project_name: string,
};

const TransactionsManager = () => {
    const token = localStorage.getItem('authToken');

    const { getTransactions } = useProjects();
    const { metadata, loading: metadataLoading, error: metadataError } = useMetadata();
    const [ transactions, setTransactions ] = useState<TransactionData[]>([]);
    
    const [statusMessage, setStatusMessage] = useState("");
    const [isError, setIsError] = useState<boolean>(false);
    const [loading, setLoading] = useState(false);
    const [loadingExportToExcel, setLoadingExportToExcel] = useState(false);
    const [ exportToExcelError, setExportToExcelError ] = useState<string | null>(null);
    
    const [filterType, setFilterType] = useState<string>('by_month');

    
    const [ projectIdSelected, setProjectIdSelected] = useState<string>('');
    const [ uniqueProjects , setUniqueProjects ] = useState<ProjectData[]>([]);

    const [ numberOfTransactions, setNumberOfTransactions ] = useState<number>(0);
    const [ totalAmount, setTotalAmount ] = useState<number>(0);
    const [ paymentAmount, setPaymentAmount ] = useState<number>(0);

    const [ page, setPage ] = useState(0);
    const [ rowsPerPage, setRowsPerPage ] = useState(15);
    
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedDate, setSelectedDate] = useState<Dayjs | null>(null);

    const handleChangePage = (event: unknown, newPage: number) => {
        setPage(newPage);
    }

    const handleChangeRowsPerPage = (event: React.ChangeEvent<HTMLInputElement>) => {
        setRowsPerPage(parseInt(event.target.value, 15));
        setPage(0);
    }
    
    const handleFilterTypeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setFilterType(event.target.value);
        // Reset other filters when switching
        setProjectIdSelected('');
        setSelectedDate(null);

        setNumberOfTransactions(0);
        setTotalAmount(0);
        setPaymentAmount(0);

        setPage(0);
    };

    // useEffect(() => {
    //     setLoading(true);

    //     const fetchData = async () => {
    //         try{
    //             const response = await axios.get(ApiConfig.project.viewTransactions, {
    //                 method: 'GET',
    //                 headers: {
    //                     'Content-Type': 'application/json',
    //                     'Authorization': `Bearer ${token}`
    //                 }
    //             });

    //             const transactions: VinnetTransactionData[] = response.data.data;

    //             setVinnetTransactions(transactions);

    //             setUniqueProjects(
    //                 Array.from(new Set(transactions.map((transaction: VinnetTransactionData) => transaction.project_id)))
    //                     .map(project_id => {
    //                         const transaction = transactions.find(transaction => transaction?.project_id === project_id);
    //                         return transaction ? { project_id: transaction.project_id, internal_code: transaction.internal_code, project_name: transaction.project_name } : {project_id: '', internal_code: '', project_name: '' };
    //                     })
    //                     .filter(project => project)
    //             );
                
    //         } catch (error) {
    //             if (axios.isAxiosError(error)) {
    //                 setStatusMessage(error.response?.data.message ?? error.message);
    //                 setIsError(true);
    //             }
    //         } finally {
    //             setLoading(false);
    //         }
    //     }

    //     fetchData();
    // }, []);
    
    const handleProjectSelectedChange = async (event: SelectChangeEvent<string>, child: React.ReactNode) => {
        const project_id = event.target.value as string;

        const data = await getTransactions(parseInt(project_id));

        setTransactions(data);

        // const filterTransactions = vinnetTransactions.filter(transaction => (transaction.project_id === project_id)); 

        // setFilterVinnetTransactions(filterTransactions);

        // const number_of_transactions = filterTransactions.filter(transaction => transaction.status === 'Token verified').length;
        // const total_amt = filterTransactions.reduce((sum, transaction) => sum + transaction.total_amt, 0);
        // const total_payment_amt = filterTransactions.reduce((sum, transaction) => sum + transaction.payment_amt, 0);

        // setNumberOfTransactions(number_of_transactions);
        // setTotalAmount(total_amt);
        // setPaymentAmount(total_payment_amt);

        setPage(0);
    }

    const handleDateChange: DatePickerProps<Dayjs, false>['onChange'] = (newDate) => {
        setSelectedDate(newDate);

        console.log(newDate?.month);

        const filterTransactions = transactions.filter(transaction => {
            const transactionDate = dayjs(transaction.created_at);

            return (
                transactionDate.month() === newDate?.month() && transactionDate.year() === newDate?.year()
            )
        });

        setTransactions(filterTransactions);

        const number_of_transactions = filterTransactions.filter(transaction => transaction.invoice_date === 'Token verified').length;
        const total_amt = filterTransactions.reduce((sum, transaction) => sum + transaction.amount, 0);
        const total_payment_amt = filterTransactions.reduce((sum, transaction) => sum + transaction.payment_amt, 0);

        setNumberOfTransactions(number_of_transactions);
        setTotalAmount(total_amt);
        setPaymentAmount(total_payment_amt);

        setPage(0);
    };

    const exportToExcel = () => {
        try
        {
            setLoadingExportToExcel(true);

            if(transactions.length === 0)
            {
                throw new Error("No data to export.");
            }

            const columnsToExport = {
                vinnet_payservice_requuid: 'Req UuId',
                status: 'Transaction status',
                internal_code: 'Internal Code',
                project_name: 'Project Name',
                employee_id: 'Interview ID',
                team: 'Team',
                province: 'Province',
                total_amt: 'Total Amount',
                commission: 'Commission',
                discount: 'Discount',
                payment_amt: 'Payment Amount',
                created_at: 'Created At',
                vinnet_invoice_date: 'Invoiced At'
            }

            const exportData = transactions.map(transaction => {
                const filteredTransaction: {[key: string]: any} = {};

                Object.entries(columnsToExport).forEach((key) => {
                    if(key[0] === 'created_at' || key[0] === 'vinnet_invoice_date')
                    {

                        filteredTransaction[key[1]] = transaction[key[0] as keyof TransactionData] === null ? null : dayjs(transaction[key[0] as keyof TransactionData]).format("YYYY-MM-DD HH:mm:ss");
                    }
                    else
                    {
                        filteredTransaction[key[1]] = transaction[key[0] as keyof TransactionData];
                    }
                })

                return filteredTransaction;
            })

            const fileType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8';
            const fileExtension = '.xlsx';

            const ws = XLSX.utils.json_to_sheet(exportData.filter(transaction => transaction['Transaction status'] === 'Token verified'));
            const wb = { Sheets: { 'data': ws }, SheetNames: ['data'] };
            const excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
            const data = new Blob([excelBuffer], { type: fileType });
            saveAs(data, 'vinnet_transactions' + fileExtension);

            setExportToExcelError(null);
        }
        catch(error)
        {
            console.log("Export failed:", error);
            setExportToExcelError(error instanceof Error ? error.message : "Export failed");
        }
        finally
        {
            setLoadingExportToExcel(false);
        }
    }
    
    const handleCloseError = () => {
        setExportToExcelError(null);
    };

    return (
        <Card sx={{
            borderRadius: "10px"
        }}>
            <CardHeader
                title="Transactions"
            />
            <Divider />
            {
                isError ? (
                    <Box display="flex" justifyContent="center" alignItems="center" height="100%" sx={{padding: '20px 20px'}}>
                        <SdCardAlertOutlinedIcon />
                        <div>{statusMessage}</div>
                    </Box>
                ) : (
                loading ? (
                    <Box display="flex" justifyContent="center" alignItems="center" height="100%" sx={{padding: '20px 20px'}}>
                        <CircularProgress />
                    </Box>
                ) : (
                    <div>
                        <CardContent>
                            <div className="filter-container">
                                <div className="filter=item">
                                    <FormControl component="fieldset">
                                        {/* <FormLabel id="involce_filter">Filter</FormLabel> */}
                                        <RadioGroup
                                            row
                                            aria-labelledby="involce_filter"
                                            name="involce_filter"
                                            value={filterType}
                                            onChange={handleFilterTypeChange}
                                        >
                                            <FormControlLabel value="by_month" control={<Radio />} label="By Month" />
                                            <FormControlLabel value="by_project" control={<Radio />} label="By Project" />
                                        </RadioGroup>
                                    </FormControl>
                                </div>
                                {filterType === 'by_project' && (
                                    <div className="filter-item">
                                        <FormControl fullWidth>
                                            <InputLabel id="project-filter-label">Filter by Project</InputLabel>
                                            <Select
                                                labelId="project-filter-label"
                                                id="project-filter"
                                                value={projectIdSelected}
                                                label="Filter by Project"
                                                onChange={handleProjectSelectedChange}
                                            >
                                                <MenuItem value="">
                                                    <em>Please select a project</em>
                                                </MenuItem>
                                                { 
                                                    metadata.projects.map((project: ProjectData) => (
                                                        <MenuItem key={project.id} value={project.id} >
                                                            {project.internal_code + " - " + project.project_name}
                                                        </MenuItem>
                                                    ))
                                                }
                                            </Select>
                                        </FormControl>
                                    </div>
                                )}

                                {filterType === 'by_month' && (
                                    <div className="filter-item">
                                        <LocalizationProvider dateAdapter={AdapterDayjs}>
                                        <DesktopDatePicker
                                            views={['year', 'month', 'day']}
                                            label="Filter by Month"
                                            value={selectedDate}
                                            onChange={handleDateChange}
                                        />
                                        </LocalizationProvider>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                        <CardContent>
                            <Grid container spacing={2}>
                                <Grid item xs={12} md={9}>
                                    <Paper sx={{width: '100%', mb: 2}} className="tabel-wrapper">
                                        <TableContainer component={Paper} className="table-container">
                                            <Table>
                                                <TableHead>
                                                    <TableRow className="header-table">
                                                        {
                                                            TableCellConfig.map((column, key) => {
                                                                return (
                                                                    <TableCell
                                                                        key={column.name}
                                                                        component='th'
                                                                        id={column.name}
                                                                        scope='col'
                                                                        padding="none"
                                                                        style={{ width: column.width }}
                                                                        align={column.type === 'number' ? 'right' : 'left'}
                                                                        className="table-row"
                                                                    >
                                                                        {column.label}
                                                                    </TableCell>
                                                                )
                                                            })
                                                        }
                                                        <TableCell
                                                            key='action'
                                                            component='th'
                                                            id='action'
                                                            scope='col'
                                                            padding="none"
                                                            style={{ width: 200 }}
                                                            align="center"
                                                            className="table-cell"
                                                            ></TableCell>
                                                    </TableRow>
                                                </TableHead>
                                                <TableBody>
                                                    {
                                                        transactions.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((transaction: TransactionData, index) => {
                                                            return (
                                                                <TableRow
                                                                    key={transaction.transaction_id} 
                                                                    className="table-row"
                                                                    hover={true}   
                                                                >
                                                                    {
                                                                        TableCellConfig.map((column: {[key: string]: any}, index) => {
                                                                            const valueFormat = column.type === 'number' ? numeral(transaction[column.name as keyof TransactionData]).format(column.name === 'discount' ? ('0.00') : ('0,000'))
                                                                            : (column.type === 'date' ? (dayjs(transaction[column.name as keyof TransactionData]).format("YYYY-MM-DD HH:MM")) : (transaction[column.name as keyof TransactionData]));
                                                                            return (
                                                                            <TableCell
                                                                                key={column.name}
                                                                                align={column.type === 'number' ? 'right' : 'left'}
                                                                                className="table-cell"
                                                                                sx={{verticalAlign: 'middle'}}
                                                                            >
                                                                                { column.type === 'image' ? (
                                                                                    transaction.invoice_date === 'Token verified' ? (
                                                                                        valueFormat === null  ? (<RadioButtonUncheckedOutlinedIcon sx={{ color: 'var(--text-primary-color)', fontSize: '18px' }} />) 
                                                                                                            : (<CheckCircleOutlineOutlinedIcon sx={{ color: 'var(--status-completed-color)', fontSize: '18px' }} />)
                                                                                    ) : (
                                                                                        <ErrorOutlineOutlinedIcon sx={{ color: 'var(--status-cancelled-color)', fontSize: '18px' }} />
                                                                                    )
                                                                                    
                                                                                ) : valueFormat }
                                                                            </TableCell>
                                                                            )
                                                                        })
                                                                    }
                                                                </TableRow>
                                                            )
                                                        })
                                                    }
                                                </TableBody>
                                            </Table>
                                        </TableContainer>
                                        <TablePagination
                                            rowsPerPageOptions={[15, 20, 25]}
                                            component='div'
                                            count={transactions.length}
                                            rowsPerPage={rowsPerPage}
                                            page={page}
                                            onPageChange={handleChangePage}
                                            onRowsPerPageChange={handleChangeRowsPerPage}
                                        />
                                    </Paper>
                                </Grid>
                                <Grid item xs={12} md={3}>
                                    <Card>
                                        <CardActions>
                                            {(exportToExcelError !== null) && (
                                                <Alert onClose={handleCloseError} severity="error" sx={{ width: '100%' }}>
                                                    {exportToExcelError}
                                                </Alert>
                                            )}
                                        </CardActions>
                                        <CardHeader title="Subtotal" />
                                        <Divider/>
                                        <CardContent>
                                            <div className="subtotal-container">
                                                <div className="row">
                                                    <div className="col">Number of transactions:</div>
                                                    <div className="col">{numeral(numberOfTransactions).format('0,000')}</div>
                                                </div>
                                                <div className="row">
                                                    <div className="col">Total Amount:</div>
                                                    <div className="col">{numeral(totalAmount).format('0,000')}</div>
                                                </div>
                                                <div className="row">
                                                    <div className="col">Payment Amount:</div>
                                                    <div className="col">{numeral(paymentAmount).format('0,000')}</div>
                                                </div>
                                            </div>            
                                        </CardContent>
                                        <Divider/>
                                        <CardContent>
                                            <div className="subtotal-container">
                                                <div className="row">
                                                    <div className="col">Total Amount:</div>
                                                    <div className="col">{numeral(totalAmount).format('0,000')}</div>
                                                </div>
                                                <div className="row">
                                                    <div className="col">Payment Amount:</div>
                                                    <div className="col">{numeral(paymentAmount).format('0,000')}</div>
                                                </div>
                                            </div>            
                                        </CardContent>
                                        <Divider/>
                                        <CardActions>
                                            <LoadingButton
                                                size="small"
                                                onClick={exportToExcel}
                                                endIcon={<FileDownloadOutlinedIcon />}
                                                loading={loadingExportToExcel}
                                                loadingPosition="end"
                                                variant="contained"
                                                className='btn bg-primary'
                                                >
                                                <span>Export to Excel</span>
                                            </LoadingButton>
                                        </CardActions>
                                    </Card>
                                </Grid>
                            </Grid>

                        </CardContent>
                    </div>
                ))
            }
        </Card>

    )
}

export default TransactionsManager;