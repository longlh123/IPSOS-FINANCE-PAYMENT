import { ChangeEvent, useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { Box, Button, FormControl, IconButton, MenuItem, Select, SelectChangeEvent, TextField } from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import DeleteIcon from "@mui/icons-material/Delete";
import SearchTextBox from "../../components/SearchTextBox";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { useProjects } from "../../hook/useProjects";
import AlertDialog from "../../components/AlertDialog/AlertDialog";
import axios from "../../config/axiosInstance";
import { ApiConfig } from "../../config/ApiConfig";
import Tooltip from "@mui/material/Tooltip";

interface TravelExpenseRow {
  id: number;
  employee_ids: string;
  full_name: string;
  completed_samples: number | null;
  working_days: number | null;
  unit_price: number | null;
  vehicle_ticket: number | null;
  unit_price_2: number | null;
  pickup_guests: number | null;
  unit_price_3: number | null;
  total_salary: number | null;
  tax_deduction: number | null;
  remaining_amount: number | null;
  region: string;
}

const UNIT_PRICE_1_OPTIONS = [100000, 150000, 200000, 250000, 300000];
const UNIT_PRICE_2_OPTIONS = [150000, 200000, 250000, 300000];

const PICKUP_OPTIONS = [
  { value: 1, label: "Có" },
  { value: 0, label: "Không" },
];

const formatMoney = (value: number | null) => {
  if (value == null) return "";
  return value.toLocaleString("vi-VN");
};

const formatDecimal = (value: number | null) => {
  if (value == null) return "";
  return value.toFixed(2);
};

const TravelExpense: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const projectId = Number(id) || 0;
  const navigate = useNavigate();

  const { getProject } = useProjects();
  const [projectSelected, setProjectSelected] = useState<ProjectData | null>(null);

  const [rows, setRows] = useState<TravelExpenseRow[]>([]);
  const [loadingRows, setLoadingRows] = useState(false);
  const [errorRows, setErrorRows] = useState(false);
  const [messageRows, setMessageRows] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState("");
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
  const [selectedDeleteRow, setSelectedDeleteRow] = useState<TravelExpenseRow | null>(null);

  useEffect(() => {
    async function fetchProject() {
      try {
        const p = await getProject(projectId);
        setProjectSelected(p);
      } catch (error) {
        console.log(error);
      }
    }

    fetchProject();
  }, [projectId]);

  useEffect(() => {
    async function fetchTravelExpenses() {
      if (!projectId) {
        setRows([]);
        return;
      }

      try {
        setLoadingRows(true);
        setErrorRows(false);
        setMessageRows(null);

        const url = ApiConfig.project.viewTravelExpenses.replace("{projectId}", projectId.toString());
        const response = await axios.get(url);

        setRows(response.data.data ?? []);
      } catch (error: any) {
        setErrorRows(true);
        setMessageRows(error?.response?.data?.message || error?.message || "Failed to load travel expenses");
      } finally {
        setLoadingRows(false);
      }
    }

    fetchTravelExpenses();
  }, [projectId]);

  const handleSearchChange = (value: string) => {
    setSearchTerm(value.toLocaleLowerCase());
    setPage(0);
  };

  const handleSelectChange = (id: number, field: keyof TravelExpenseRow, event: SelectChangeEvent<number>) => {
    const nextValue = Number(event.target.value);
    setRows((prev) =>
      prev.map((row) => {
        if (row.id !== id) return row;
        return { ...row, [field]: nextValue };
      })
    );
  };

  const handleAmountInputChange = (
    id: number,
    field: keyof TravelExpenseRow,
    event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const rawValue = event.target.value;
    if (field === "vehicle_ticket") {
      if (rawValue === "") {
        setRows((prev) =>
          prev.map((row) => {
            if (row.id !== id) return row;
            return { ...row, [field]: null };
          })
        );
        return;
      }

      if (!/^\d+$/.test(rawValue)) {
        return;
      }
    }

    const nextValue = rawValue === "" ? null : Number(rawValue);

    if (rawValue !== "" && Number.isNaN(nextValue)) {
      return;
    }

    setRows((prev) =>
      prev.map((row) => {
        if (row.id !== id) return row;
        return { ...row, [field]: nextValue };
      })
    );
  };

  const handleDeleteTravelExpense = async (row: TravelExpenseRow) => {
    if (!projectId) return;

    try {
      const url = ApiConfig.project.removeTravelExpense
        .replace("{projectId}", projectId.toString())
        .replace("{travelExpenseId}", row.id.toString());

      const response = await axios.delete(url);

      setRows((prev) => prev.filter((item) => item.id !== row.id));
      setErrorRows(false);
      setMessageRows(response?.data?.message ?? "Travel expense removed successfully.");
    } catch (error: any) {
      setErrorRows(true);
      setMessageRows(error?.response?.data?.message || error?.message || "Failed to remove travel expense");
    }
  };

  const handleOpenDeleteDialog = (row: TravelExpenseRow) => {
    setSelectedDeleteRow(row);
    setOpenDeleteDialog(true);
  };

  const handleCloseDeleteDialog = () => {
    setOpenDeleteDialog(false);
    setSelectedDeleteRow(null);
  };

  const handleConfirmDelete = async () => {
    if (!selectedDeleteRow) return;

    await handleDeleteTravelExpense(selectedDeleteRow);
    handleCloseDeleteDialog();
  };

  const filteredRows = useMemo(() => {
    if (!searchTerm) return rows;
    return rows.filter(
      (row) =>
        row.employee_ids.toLocaleLowerCase().includes(searchTerm) ||
        row.full_name.toLocaleLowerCase().includes(searchTerm) ||
        row.region.toLocaleLowerCase().includes(searchTerm)
    );
  }, [rows, searchTerm]);

  const pagedRows = useMemo(() => {
    const start = page * rowsPerPage;
    const end = start + rowsPerPage;
    return filteredRows.slice(start, end);
  }, [filteredRows, page, rowsPerPage]);

  const columns: ColumnFormat[] = [
    {
      label: "Mã số PVV",
      name: "employee_ids",
      type: "string",
      align: "left",
      width: 130,
    },
    {
      label: "Họ và tên",
      name: "full_name",
      type: "string",
      align: "left",
      width: 180,
    },
    {
      label: "Số samples hoàn thành",
      name: "completed_samples",
      type: "number",
      align: "right",
      width: 120,
      renderCell: (row: TravelExpenseRow) => (
        <span style={{ color: "#e53935", fontWeight: 500 }}>{formatDecimal(row.completed_samples)}</span>
      ),
    },
    {
      label: "Ngày làm",
      name: "working_days",
      type: "number",
      align: "right",
      width: 100,
      renderCell: (row: TravelExpenseRow) => formatDecimal(row.working_days),
    },
    {
      label: "Đơn giá",
      name: "unit_price",
      type: "select",
      align: "right",
      width: 120,
      renderCell: (row: TravelExpenseRow) => (
        <FormControl size="small" sx={{ minWidth: 100 }}>
          <Select<number>
            value={row.unit_price ?? ""}
            displayEmpty
            onChange={(event) => handleSelectChange(row.id, "unit_price", event as SelectChangeEvent<number>)}
          >
            <MenuItem value="" disabled />
            {UNIT_PRICE_1_OPTIONS.map((opt) => (
              <MenuItem key={opt} value={opt}>
                {formatMoney(opt)}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      ),
    },
    {
      label: "Vé xe/Hỗ trợ xăng",
      name: "vehicle_ticket",
      type: "number",
      align: "right",
      width: 140,
      renderCell: (row: TravelExpenseRow) => (
        <TextField
          size="small"
          type="number"
          value={row.vehicle_ticket ?? ""}
          onChange={(event) => handleAmountInputChange(row.id, "vehicle_ticket", event)}
          placeholder="Nhập số lượng"
          inputProps={{ min: 0, step: 1 }}
          sx={{ minWidth: 110 }}
        />
      ),
    },
    {
      label: "Đơn giá",
      name: "unit_price_2",
      type: "select",
      align: "right",
      width: 120,
      renderCell: (row: TravelExpenseRow) => (
        <FormControl size="small" sx={{ minWidth: 100 }}>
          <Select<number>
            value={row.unit_price_2 ?? ""}
            displayEmpty
            onChange={(event) => handleSelectChange(row.id, "unit_price_2", event as SelectChangeEvent<number>)}
          >
            <MenuItem value="" disabled />
            {UNIT_PRICE_2_OPTIONS.map((opt) => (
              <MenuItem key={opt} value={opt}>
                {formatMoney(opt)}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      ),
    },
    {
      label: "Đón khách",
      name: "pickup_guests",
      type: "select",
      align: "left",
      width: 120,
      renderCell: (row: TravelExpenseRow) => (
        <FormControl size="small" sx={{ minWidth: 100 }}>
          <Select<number>
            value={row.pickup_guests ?? ""}
            displayEmpty
            onChange={(event) => handleSelectChange(row.id, "pickup_guests", event as SelectChangeEvent<number>)}
          >
            <MenuItem value="" disabled />
            {PICKUP_OPTIONS.map((opt) => (
              <MenuItem key={opt.value} value={opt.value}>
                {opt.label}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      ),
    },
    {
      label: "Đơn giá",
      name: "unit_price_3",
      type: "number",
      align: "right",
      width: 120,
      renderCell: (row: TravelExpenseRow) => (
        <TextField
          size="small"
          type="number"
          value={row.unit_price_3 ?? ""}
          onChange={(event) => handleAmountInputChange(row.id, "unit_price_3", event)}
          placeholder="Nhập số tiền"
          inputProps={{ min: 0 }}
          sx={{ minWidth: 110 }}
        />
      ),
    },
    {
      label: "Tổng lương",
      name: "total_salary",
      type: "number",
      align: "right",
      width: 120,
      renderCell: (row: TravelExpenseRow) => formatMoney(row.total_salary),
    },
    {
      label: "Tiền thuế khấu trừ",
      name: "tax_deduction",
      type: "number",
      align: "right",
      width: 160,
      renderCell: (row: TravelExpenseRow) => formatMoney(row.tax_deduction),
    },
    {
      label: "Số tiền còn lại được nhận",
      name: "remaining_amount",
      type: "number",
      align: "right",
      width: 130,
      renderCell: (row: TravelExpenseRow) => formatMoney(row.remaining_amount),
    },
    {
      label: "KHU VỰC",
      name: "region",
      type: "string",
      align: "left",
      width: 130,
    },
    {
      label: "Actions",
      name: "actions",
      type: "menu",
      align: "center",
      width: 100,
      renderAction: (row: TravelExpenseRow) => (
        <Tooltip title="Delete">
          <span>
            <IconButton
              color="error"
              size="small"
              disabled={loadingRows}
              onClick={() => handleOpenDeleteDialog(row)}
            >
              <DeleteIcon />
            </IconButton>
          </span>
        </Tooltip>
      ),
    },
  ];

  const handleChangePage = (event: any, newPage: number) => {
    setPage(newPage);
  };

  const handleChangeRowsPerPage = (event: any) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  return (
    <Box className="box-table">
      <div className="filter">
        <div className="filter-left">
          <div className="project-info">
            <div>
              <strong>Project Name:</strong> {projectSelected?.project_name}
            </div>
            <div>
              <strong>Symphony:</strong> {projectSelected?.symphony}
            </div>
          </div>
        </div>
        <div className="filter-right">
          <Button
            className="btn"
            variant="outlined"
            startIcon={<CloseIcon />}
            onClick={() => navigate("/project-management/projects")}
          >
            Tắt
          </Button>
        </div>
      </div>

      <div className="filter">
        <div className="filter-left"></div>
        <div className="filter-right">
          <SearchTextBox placeholder="Tìm khu vực..." onSearchChange={handleSearchChange} />
        </div>
      </div>

      <ReusableTable
        title="Travel Expense"
        columns={columns}
        data={pagedRows}
        loading={loadingRows}
        error={errorRows}
        message={messageRows}
        page={page}
        rowsPerPage={rowsPerPage}
        total={filteredRows.length}
        onPageChange={handleChangePage}
        onRowsPerPageChange={handleChangeRowsPerPage}
      />

      <AlertDialog
        open={openDeleteDialog}
        onClose={handleCloseDeleteDialog}
        onConfirm={handleConfirmDelete}
        showConfirmButton={true}
        title="Delete Travel Expense"
        message={`Are you sure that you want to remove ${selectedDeleteRow?.employee_ids ?? "this record"} from travel expense list?`}
      />
    </Box>
  );
};

export default TravelExpense;
