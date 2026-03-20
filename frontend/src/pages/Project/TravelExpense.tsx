import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { Box, Button, FormControl, MenuItem, Select, SelectChangeEvent } from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import SearchTextBox from "../../components/SearchTextBox";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { useProjects } from "../../hook/useProjects";

interface TravelExpenseRow {
  id: number;
  employee_ids: string;
  start_date: string;
  end_date: string;
  number_of_days: number;
  unit_price: number;
  accounting_status: "Pending" | "Approved" | "Rejected";
}

const UNIT_PRICE_OPTIONS = [200000, 300000];

const INITIAL_ROWS: TravelExpenseRow[] = [
  {
    id: 1,
    employee_ids: "PVV001",
    start_date: "2026-03-01",
    end_date: "2026-03-03",
    number_of_days: 3,
    unit_price: 200000,
    accounting_status: "Pending",
  },
  {
    id: 2,
    employee_ids: "PVV002",
    start_date: "2026-03-05",
    end_date: "2026-03-06",
    number_of_days: 2,
    unit_price: 300000,
    accounting_status: "Approved",
  },
  {
    id: 3,
    employee_ids: "PVV003",
    start_date: "2026-03-08",
    end_date: "2026-03-10",
    number_of_days: 3,
    unit_price: 200000,
    accounting_status: "Rejected",
  },
];

const formatMoney = (value: number) => {
  return value.toLocaleString("vi-VN") + " VND";
};

const TravelExpense: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const projectId = Number(id) || 0;
  const navigate = useNavigate();

  const { getProject } = useProjects();
  const [projectSelected, setProjectSelected] = useState<ProjectData | null>(null);

  const [rows, setRows] = useState<TravelExpenseRow[]>(INITIAL_ROWS);
  const [searchTerm, setSearchTerm] = useState("");
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);

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

  const handleSearchChange = (value: string) => {
    setSearchTerm(value.toLocaleLowerCase());
    setPage(0);
  };

  const handleUnitPriceChange = (id: number, event: SelectChangeEvent<number>) => {
    const nextUnitPrice = Number(event.target.value);

    setRows((prev) =>
      prev.map((row) => {
        if (row.id !== id) return row;

        return {
          ...row,
          unit_price: nextUnitPrice,
        };
      })
    );
  };

  const filteredRows = useMemo(() => {
    if (!searchTerm) return rows;

    return rows.filter((row) => row.employee_ids.toLocaleLowerCase().includes(searchTerm));
  }, [rows, searchTerm]);

  const pagedRows = useMemo(() => {
    const start = page * rowsPerPage;
    const end = start + rowsPerPage;
    return filteredRows.slice(start, end);
  }, [filteredRows, page, rowsPerPage]);

  const columns: ColumnFormat[] = [
    {
      label: "PVV Code (employee_ids)",
      name: "employee_ids",
      type: "string",
      align: "left",
      width: 220,
    },
    {
      label: "Start Date",
      name: "start_date",
      type: "date",
      align: "left",
      width: 140,
    },
    {
      label: "End Date",
      name: "end_date",
      type: "date",
      align: "left",
      width: 140,
    },
    {
      label: "Number of Days",
      name: "number_of_days",
      type: "number",
      align: "right",
      width: 130,
    },
    {
      label: "Unit Price",
      name: "unit_price",
      type: "select",
      align: "left",
      width: 180,
      renderCell: (row: TravelExpenseRow) => (
        <FormControl size="small" sx={{ minWidth: 160 }}>
          <Select<number>
            value={row.unit_price}
            onChange={(event) => handleUnitPriceChange(row.id, event)}
          >
            {UNIT_PRICE_OPTIONS.map((option) => (
              <MenuItem key={option} value={option}>
                {formatMoney(option)}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      ),
    },
    {
      label: "Total Amount",
      name: "total_amount",
      type: "number",
      align: "right",
      width: 180,
      renderCell: (row: TravelExpenseRow) => formatMoney(row.number_of_days * row.unit_price),
    },
    {
      label: "Accounting Status",
      name: "accounting_status",
      type: "string",
      align: "center",
      width: 170,
      renderCell: (row: TravelExpenseRow) => {
        return (
          <span className={"box-status-button " + row.accounting_status.toLocaleLowerCase()}>
            {row.accounting_status}
          </span>
        );
      },
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
          <SearchTextBox placeholder="Search PVV code..." onSearchChange={handleSearchChange} />
        </div>
      </div>

      <ReusableTable
        title="Travel Expense"
        columns={columns}
        data={pagedRows}
        loading={false}
        error={false}
        message={null}
        page={page}
        rowsPerPage={rowsPerPage}
        total={filteredRows.length}
        onPageChange={handleChangePage}
        onRowsPerPageChange={handleChangeRowsPerPage}
      />
    </Box>
  );
};

export default TravelExpense;
