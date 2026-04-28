import { useState, useEffect } from "react";
import axios from "axios";
import { Select, MenuItem, Box, Button, Card, CardContent, Typography, TextField, Menu, TableContainer, Table, TableHead, TableRow, TableCell, TableBody, Paper, FormControl, InputLabel, Grid } from "@mui/material";
import { useSearchParams } from "react-router-dom";
import { ApiConfig } from "../../config/ApiConfig";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { MiniCATICellConfig } from "../../config/MiniCATIFieldsConfig";

export default function MiniCATI() {
  const [ current, setCurrent ] = useState<any>(null);
  
  const [ searchParams ] = useSearchParams();
  const employeeId = searchParams.get('employee_id');

  const [filters, setFilters] = useState({
    filter_1: "",
    filter_2: "",
    filter_3: "",
    filter_4: ""
  });

  const [options, setOptions] = useState<any>({
    filter_1: [],
    filter_2: [],
    filter_3: [],
    filter_4: []
  });

  const statusOptions = [
      { value: "Done", label: "Thành công" },
      { value: "Suspended", label: "Hẹn gọi lại" },

      { value: "Reject_Industry", label: "Thuộc ngành cấm" },
      { value: "Reject_NoMemory", label: "Không nhớ giao dịch" },
      { value: "Reject_NoTransaction", label: "Không có giao dịch" },
      { value: "Reject_Refuse", label: "Từ chối tham gia" },
      { value: "Reject_WrongPhone", label: "Số điện thoại sai" },
      // { value: "Reject_NoAnswer", label: "Không nghe máy" },
  ];

  const columns: ColumnFormat[] = [
    ...MiniCATICellConfig,
    {
      label: "Action",
      name: "Action",
      type: "string",
      align: "center",
      renderCell: (row: any) => {
        <Button
          size="small"
          variant="outlined"
          onClick={() => setCurrent(row)}
        >
          Gọi lại
        </Button>
      },
    }
  ];

    const [status, setStatus] = useState("");
    const [comment, setComment] = useState("");

    const [suspendedList, setSuspendedList] = useState<any[]>([]);

    const getSuspendedList = async () => {
      const res = await axios.get(ApiConfig.minicati.getSuspendedList, {
          params: { employee_id: employeeId }
      });

      setSuspendedList(res.data);
    };

    useEffect(() => {
        getSuspendedList();
    }, []);

  // 🔥 Load options từ API
  useEffect(() => {
    axios.get(ApiConfig.minicati.filters).then((res) => {
      setOptions(res.data);
    });
  }, []);

  // 🔥 Khi đổi filter → reset sample
  useEffect(() => {
    setCurrent(null);
  }, [filters]);

  const getNext = async () => {
    try {
      const res = await axios.post(ApiConfig.minicati.next, {
        user: employeeId,
        ...filters
      });
      setCurrent(res.data);
    } catch (err: any) {
      console.error(err.response?.data);
    }
  };

    const handleSubmit = async () => {
        if (!status || !current) return;

        let finalStatus = status;
        let finalComment = "";

        const selected = statusOptions.find((s) => s.value === status);

        if (status === "Suspended") {
            if (!comment.trim()) return alert("Vui lòng nhập ghi chú");
            finalComment = comment;
        } else if (status.startsWith("Reject")) {
            finalComment = selected?.label || "";
        }

        await axios.post(ApiConfig.minicati.updateStatus, {
            id: current.id,
            status: finalStatus,
            comment: finalComment
        });

        // reset
        setStatus("");
        setComment("");
        setCurrent(null);

        await getSuspendedList();
    };

  
  return (
    <Box p={3}>
      
      {/* ================= FILTER ================= */}
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={3}>
          <div style={{ marginBottom: "1rem" }}>
            <Typography variant="body2" gutterBottom>
                Filter 1
            </Typography>
            <Select
              value={filters.filter_1}
              displayEmpty
              fullWidth
              onChange={(e) =>
                setFilters({ ...filters, filter_1: e.target.value })
              }
            >
              <MenuItem value="">All</MenuItem>
              {options.filter_1.map((item: any) => (
                <MenuItem key={item} value={item}>{item}</MenuItem>
              ))}
            </Select>
          </div>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <div style={{ marginBottom: "1rem" }}>
            <Typography variant="body2" gutterBottom>
                Filter 2
            </Typography>
            <Select
              value={filters.filter_2}
              displayEmpty
              fullWidth
              onChange={(e) =>
                setFilters({ ...filters, filter_2: e.target.value })
              }
            >
              <MenuItem value="">All</MenuItem>
              {options.filter_2.map((item: any) => (
                <MenuItem key={item} value={item}>{item}</MenuItem>
              ))}
            </Select>
          </div>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <div style={{ marginBottom: "1rem" }}>
            <Typography variant="body2" gutterBottom>
                Filter 3
            </Typography>
            <Select
              value={filters.filter_3}
              displayEmpty
              fullWidth
              onChange={(e) =>
                setFilters({ ...filters, filter_3: e.target.value })
              }
            >
              <MenuItem value="">All</MenuItem>
              {options.filter_3.map((item: any) => (
                <MenuItem key={item} value={item}>{item}</MenuItem>
              ))}
            </Select>
          </div>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <div style={{ marginBottom: "1rem" }}>
            <Typography variant="body2" gutterBottom>
                Filter 4
            </Typography>
            <Select
              value={filters.filter_4}
              displayEmpty
              fullWidth
              onChange={(e) =>
                setFilters({ ...filters, filter_4: e.target.value })
              }
            >
              <MenuItem value="">All</MenuItem>
              {options.filter_4.map((item: any) => (
                <MenuItem key={item} value={item}>{item}</MenuItem>
              ))}
            </Select>
          </div>
        </Grid>
      </Grid>
      <Box display="flex" gap={2} mb={2}>
        <Button 
            disabled={current}
            variant="contained" 
            onClick={getNext}
        >
            Next
        </Button>
      </Box>

      {/* ================= CALL SCREEN ================= */}
      {current?.id ? (
        
        <Box>
            <Card sx={{ maxWidth: 900, margin: "auto", mt: 3, borderRadius: 3, boxShadow: 3 }}>
                <CardContent>

                    {/* 🔥 Title */}
                    <Typography variant="h6" fontWeight="bold" mb={2}>
                    Respondent Information
                    </Typography>

                    {/* 🔥 Info */}
                    <Box mb={2}>
                        <Typography>
                            <strong>ID:</strong> {current.respondent_id}
                        </Typography>

                        <Typography>
                            <strong>Phone:</strong> {current.phone}
                        </Typography>

                        <Typography color="error">
                            Vui lòng thực hiện phỏng vấn trực tiếp bên dưới (không mở link ngoài)
                        </Typography>
                    </Box>

                    <Box
                      sx={{
                        border: "1px solid #ddd",
                        borderRadius: 2,
                        overflow: "hidden",
                        mb: 3
                      }}
                    >
                      <iframe
                        key={current.id}
                        src={current.link}
                        width="100%"
                        height="600px"
                        style={{
                          border: "none"
                        }}
                      />
                    </Box>

                    {/* 🔥 Action buttons */}
                    <Box display="flex" flexDirection="column" gap={2} mt={2}>
                    
                        {/* 🔽 Status Combobox */}
                        <TextField
                            select
                            label="Select Status"
                            value={status}
                            onChange={(e) => {
                                setStatus(e.target.value);
                                setComment("");
                            }}
                            fullWidth
                        >
                            {statusOptions.map((opt) => (
                                <MenuItem key={opt.value} value={opt.value}>
                                    {opt.label}
                                </MenuItem>
                            ))}
                        </TextField>

                        {/* 📝 Comment */}
                        {status === "Suspended" && (
                            <TextField
                                label="Comment (optional)"
                                value={comment}
                                onChange={(e) => setComment(e.target.value)}
                                fullWidth
                                multiline
                                rows={3}
                            />
                        )}
                        
                        {/* 🚀 Submit */}
                        <Button
                            variant="contained"
                            color="primary"
                            disabled={!status}
                            onClick={handleSubmit}
                        >
                            Submit
                        </Button>

                    </Box>

                </CardContent>
            </Card>
        </Box>
      ) : (
        <p>No sample available</p>
      )}
      
      {/* <ReusableTable
        title="Danh sách hẹn gọi lại"
        columns={columns}
        data={suspendedList}
        loading={loading}
        error={errorAccounts}
        message={messageAccount}
        page = {page}
        rowsPerPage = {rowsPerPage}
        total = {total}
        onPageChange={handleChangePage}
        onRowsPerPageChange={handleChangeRowsPerPage}
      /> */}

        <Box sx={{ maxWidth: 720, margin: "auto", mt: 3 }}>
  
            <Typography variant="h6" fontWeight="bold" mb={2}>
                🔁 Danh sách hẹn gọi lại
            </Typography>
            
            <TableContainer component={Paper} sx={{ borderRadius: 3 }}>
                <Table size="small">
                
                <TableHead>
                    <TableRow>
                    <TableCell><strong>ID</strong></TableCell>
                    <TableCell><strong>Phone</strong></TableCell>
                    <TableCell><strong>Comment</strong></TableCell>
                    <TableCell align="center"><strong>Action</strong></TableCell>
                    </TableRow>
                </TableHead>

                <TableBody>
                    {suspendedList.length > 0 ? (
                    suspendedList.map((item) => (
                        <TableRow key={item.id}>
                        
                        <TableCell>{item.respondent_id}</TableCell>
                        <TableCell>{item.phone}</TableCell>

                        <TableCell>
                            {/* <a href={item.link} target="_blank" rel="noopener noreferrer"> */}
                            {item.comment}
                            {/* </a> */}
                        </TableCell>

                        <TableCell align="center">
                            <Button
                              size="small"
                              variant="outlined"
                              onClick={() => setCurrent(item)}
                            >
                            Gọi lại
                            </Button>
                        </TableCell>

                        </TableRow>
                    ))
                    ) : (
                    <TableRow>
                        <TableCell colSpan={4} align="center">
                        Không có dữ liệu
                        </TableCell>
                    </TableRow>
                    )}
                </TableBody>

                </Table>
            </TableContainer>
        </Box>
    </Box>
  );
}