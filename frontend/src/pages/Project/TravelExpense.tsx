import { ChangeEvent, useCallback, useEffect, useRef, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { Box, Button, FormControl, IconButton, InputAdornment, Menu, MenuItem, Select, SelectChangeEvent, TextField } from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import DeleteIcon from "@mui/icons-material/Delete";
import FileDownloadIcon from "@mui/icons-material/FileDownload";
import FileUploadIcon from "@mui/icons-material/FileUpload";
import SearchTextBox from "../../components/SearchTextBox";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { useProjects } from "../../hook/useProjects";
import AlertDialog from "../../components/AlertDialog/AlertDialog";
import axios from "../../config/axiosInstance";
import { ApiConfig } from "../../config/ApiConfig";
import Tooltip from "@mui/material/Tooltip";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

interface TravelExpenseRow {
  id: number;
  employee_ids: string;
  full_name: string;
  departure_date: string | null;
  return_date: string | null;
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

const UNIT_PRICE_1_OPTIONS = [70000, 100000, 150000, 250000, 300000 ];
const UNIT_PRICE_2_OPTIONS = [150000, 200000, 250000, 300000];
const CUSTOM_UNIT_PRICE_2_OPTION = -1;

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

const parseImportedDate = (value: unknown): string | null => {
  if (value == null || value === "") return null;

  if (value instanceof Date && !Number.isNaN(value.getTime())) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, "0");
    const day = String(value.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  if (typeof value === "number") {
    const parsed = XLSX.SSF.parse_date_code(value);
    if (!parsed) return null;
    const month = String(parsed.m).padStart(2, "0");
    const day = String(parsed.d).padStart(2, "0");
    return `${parsed.y}-${month}-${day}`;
  }

  const raw = String(value).trim();
  if (!raw) return null;
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;

  const date = new Date(raw);
  if (Number.isNaN(date.getTime())) return null;

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const parseImportedNumber = (value: unknown): number | null => {
  if (value == null || value === "") return null;
  if (typeof value === "number") return Number.isNaN(value) ? null : value;

  const raw = String(value).replace(/,/g, "").trim();
  if (!raw) return null;

  const parsed = Number(raw);
  return Number.isNaN(parsed) ? null : parsed;
};

const parseImportedPickupGuests = (value: unknown): number | null => {
  if (value == null || value === "") return null;

  if (typeof value === "number") {
    if (value === 1) return 1;
    if (value === 0) return 0;
  }

  const raw = String(value).trim().toLowerCase();
  if (["1", "co", "có", "yes", "y", "true"].includes(raw)) return 1;
  if (["0", "khong", "không", "no", "n", "false"].includes(raw)) return 0;

  return null;
};

const isInvalidDateRange = (departureDate: string | null, returnDate: string | null) => {
  if (!departureDate || !returnDate) return false;
  return returnDate < departureDate;
};

const formatProjectTypes = (project: ProjectData | null) => {
  const projectWithLegacyTypes = project as (ProjectData & { project_project_types?: Array<string | { name?: string }> }) | null;
  const rawTypes = projectWithLegacyTypes?.project_project_types ?? project?.project_types ?? [];

  if (!Array.isArray(rawTypes) || rawTypes.length === 0) {
    return "-";
  }

  const normalizedTypes = rawTypes
    .map((typeItem) => {
      if (typeof typeItem === "string") {
        return typeItem;
      }

      return typeItem?.name ?? "";
    })
    .filter(Boolean);

  return normalizedTypes.length > 0 ? normalizedTypes.join(", ") : "-";
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
  const [target, setTarget] = useState<string>("");
  const [targetRecruit, setTargetRecruit] = useState<string>("");
  const [targetRecall, setTargetRecall] = useState<string>("");
  const [importingRows, setImportingRows] = useState(false);
  const [exportAnchorEl, setExportAnchorEl] = useState<null | HTMLElement>(null);
  const [customUnitPrice2Rows, setCustomUnitPrice2Rows] = useState<Set<number>>(new Set());
  const projectTypesDisplay = useMemo(() => formatProjectTypes(projectSelected), [projectSelected]);
  const importFileInputRef = useRef<HTMLInputElement | null>(null);
  const normalizedProjectTypes = useMemo(() => {
    const projectWithLegacyTypes = projectSelected as (ProjectData & { project_project_types?: Array<string | { name?: string }> }) | null;
    const rawTypes = projectWithLegacyTypes?.project_project_types ?? projectSelected?.project_types ?? [];

    return rawTypes
      .map((typeItem) => (typeof typeItem === "string" ? typeItem : typeItem?.name ?? ""))
      .map((name) => name.trim().toUpperCase())
      .filter(Boolean);
  }, [projectSelected]);

  const isHutProject = normalizedProjectTypes.includes("HUT");

  const activeTarget = isHutProject ? targetRecruit : target;
  const targetValue = Number(activeTarget);

  const travelDayBase = useMemo(() => {
    if (normalizedProjectTypes.includes("HUT") || normalizedProjectTypes.includes("CLT")) {
      return 3;
    }

    if (normalizedProjectTypes.includes("F2F")) {
      return 1;
    }

    return 0;
  }, [normalizedProjectTypes]);

  const calculateWorkingDays = useCallback((completedSamples: number | null) => {
    if (completedSamples == null) {
      return null;
    }

    if (isHutProject) {
      const recruitTargetValue = Number(targetRecruit);
      const recallTargetValue = Number(targetRecall);

      if (!targetRecruit || Number.isNaN(recruitTargetValue) || recruitTargetValue <= 0) {
        return null;
      }

      if (!targetRecall || Number.isNaN(recallTargetValue) || recallTargetValue <= 0) {
        return null;
      }

      return 4 + completedSamples / recruitTargetValue + completedSamples / recallTargetValue;
    }

    if (!activeTarget || Number.isNaN(targetValue) || targetValue <= 0) {
      return null;
    }

    return travelDayBase + completedSamples / targetValue;
  }, [activeTarget, targetValue, travelDayBase, isHutProject, targetRecruit, targetRecall]);

  const calculateTotalSalary = useCallback((row: TravelExpenseRow) => {
    const workingDaysAmount = Number(calculateWorkingDays(row.completed_samples) ?? 0) * Number(row.unit_price ?? 0);
    const vehicleAmount = Number(row.vehicle_ticket ?? 0) * Number(row.unit_price_2 ?? 0);
    const pickupAmount = Number(row.pickup_guests ?? 0) * Number(row.unit_price_3 ?? 0);

    return workingDaysAmount + vehicleAmount + pickupAmount;
  }, [calculateWorkingDays]);

  const buildTravelExpensePayload = useCallback((row: TravelExpenseRow) => ({
    departure_date: row.departure_date,
    return_date: row.return_date,
    working_days: calculateWorkingDays(row.completed_samples),
    unit_price: row.unit_price,
    vehicle_ticket: row.vehicle_ticket,
    unit_price_2: row.unit_price_2,
    pickup_guests: row.pickup_guests,
    unit_price_3: row.pickup_guests === 1 ? row.unit_price_3 : null,
    total_salary: Math.round(calculateTotalSalary(row)),
  }), [calculateTotalSalary, calculateWorkingDays]);

  const fetchTravelExpenses = useCallback(async () => {
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

      const normalizedRows = (response.data.data ?? []).map((row: TravelExpenseRow) => {
        const pickupGuests = row.pickup_guests ?? 0;
        return {
          ...row,
          pickup_guests: pickupGuests,
          unit_price_3: pickupGuests === 1 ? row.unit_price_3 : null,
        };
      });

      setRows(normalizedRows);
      setCustomUnitPrice2Rows(
        new Set(
          normalizedRows
            .filter((row: TravelExpenseRow) => row.unit_price_2 != null && !UNIT_PRICE_2_OPTIONS.includes(row.unit_price_2))
            .map((row: TravelExpenseRow) => row.id)
        )
      );
    } catch (error: any) {
      setErrorRows(true);
      setMessageRows(error?.response?.data?.message || error?.message || "Failed to load travel expenses");
    } finally {
      setLoadingRows(false);
    }
  }, [projectId]);

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
    if (!projectSelected) return;

    setTarget(projectSelected.travel_expense_target != null ? String(projectSelected.travel_expense_target) : "");
    setTargetRecruit(
      projectSelected.travel_expense_target_recruit != null
        ? String(projectSelected.travel_expense_target_recruit)
        : ""
    );
    setTargetRecall(
      projectSelected.travel_expense_target_recall != null
        ? String(projectSelected.travel_expense_target_recall)
        : ""
    );
  }, [projectSelected]);

  useEffect(() => {
    fetchTravelExpenses();
  }, [fetchTravelExpenses]);

  const handleSearchChange = (value: string) => {
    setSearchTerm(value.toLocaleLowerCase());
    setPage(0);
  };

  const handleTargetBlur = async () => {
    if (!projectId) return;

    const trimmed = target.trim();
    const parsedTarget = trimmed === "" ? null : Number(trimmed);

    if (trimmed !== "") {
      if (parsedTarget == null || !Number.isInteger(parsedTarget) || parsedTarget <= 0) {
        setErrorRows(true);
        setMessageRows("Target phải là số nguyên dương.");
        return;
      }
    }

    try {
      const url = ApiConfig.project.updateTravelExpenseTarget.replace("{projectId}", projectId.toString());
      const response = await axios.put(url, { travel_expense_target: parsedTarget });

      setProjectSelected((prev) =>
        prev
          ? {
              ...prev,
              travel_expense_target: response?.data?.data?.travel_expense_target ?? parsedTarget,
              travel_expense_target_recruit: response?.data?.data?.travel_expense_target_recruit ?? prev.travel_expense_target_recruit,
              travel_expense_target_recall: response?.data?.data?.travel_expense_target_recall ?? prev.travel_expense_target_recall,
            }
          : prev
      );

      setErrorRows(false);
    } catch (error: any) {
      setErrorRows(true);
      setMessageRows(error?.response?.data?.message || error?.message || "Failed to save target");
    }
  };

  const handleHutTargetBlur = async (field: "recruit" | "recall") => {
    if (!projectId) return;

    const rawValue = field === "recruit" ? targetRecruit : targetRecall;
    const trimmed = rawValue.trim();
    const parsedValue = trimmed === "" ? null : Number(trimmed);

    if (trimmed !== "") {
      if (parsedValue == null || !Number.isInteger(parsedValue) || parsedValue <= 0) {
        setErrorRows(true);
        setMessageRows(`Target ${field === "recruit" ? "Recruit" : "Recall"} phải là số nguyên dương.`);
        return;
      }
    }

    try {
      const url = ApiConfig.project.updateTravelExpenseTarget.replace("{projectId}", projectId.toString());
      const payload =
        field === "recruit"
          ? { travel_expense_target_recruit: parsedValue }
          : { travel_expense_target_recall: parsedValue };
      const response = await axios.put(url, payload);

      setProjectSelected((prev) =>
        prev
          ? {
              ...prev,
              travel_expense_target: response?.data?.data?.travel_expense_target ?? prev.travel_expense_target,
              travel_expense_target_recruit: response?.data?.data?.travel_expense_target_recruit ?? prev.travel_expense_target_recruit,
              travel_expense_target_recall: response?.data?.data?.travel_expense_target_recall ?? prev.travel_expense_target_recall,
            }
          : prev
      );

      setErrorRows(false);
      setMessageRows(null);
    } catch (error: any) {
      setErrorRows(true);
      setMessageRows(error?.response?.data?.message || error?.message || "Failed to save target");
    }
  };

  const persistTravelExpenseRow = async (row: TravelExpenseRow) => {
    if (!projectId) return;

    try {
      const url = ApiConfig.project.updateTravelExpense
        .replace("{projectId}", projectId.toString())
        .replace("{travelExpenseId}", row.id.toString());

      await axios.put(url, buildTravelExpensePayload(row));
      setErrorRows(false);
    } catch (error: any) {
      setErrorRows(true);
      setMessageRows(error?.response?.data?.message || error?.message || "Failed to save travel expense");
    }
  };

  const handleImportFile = async (event: ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];

    if (!file || !projectId) {
      if (event.target) event.target.value = "";
      return;
    }

    try {
      setImportingRows(true);
      setErrorRows(false);
      setMessageRows(null);

      const arrayBuffer = await file.arrayBuffer();
      const workbook = XLSX.read(arrayBuffer, { type: "array", cellDates: true });
      const firstSheetName = workbook.SheetNames[0];

      if (!firstSheetName) {
        setErrorRows(true);
        setMessageRows("File import không có sheet dữ liệu.");
        return;
      }

      const worksheet = workbook.Sheets[firstSheetName];
      const importedRows = XLSX.utils.sheet_to_json<Record<string, unknown>>(worksheet, {
        defval: null,
        raw: true,
      });

      if (importedRows.length === 0) {
        setErrorRows(true);
        setMessageRows("File import không có dòng dữ liệu.");
        return;
      }

      const draftRows = [...rows];
      const updateCandidates: TravelExpenseRow[] = [];
      let skippedRows = 0;

      importedRows.forEach((item) => {
        const employeeCode = String(item["Mã số PVV"] ?? item["employee_ids"] ?? "").trim();

        if (!employeeCode) {
          skippedRows += 1;
          return;
        }

        const rowIndex = draftRows.findIndex((row) => String(row.employee_ids).trim() === employeeCode);
        if (rowIndex < 0) {
          skippedRows += 1;
          return;
        }

        const baseRow = draftRows[rowIndex];
        const importedPickupGuests = parseImportedPickupGuests(item["Đón khách"] ?? item["pickup_guests"]);

        const nextRow: TravelExpenseRow = {
          ...baseRow,
          departure_date: parseImportedDate(item["Ngày đi"] ?? item["departure_date"]) ?? baseRow.departure_date,
          return_date: parseImportedDate(item["Ngày về"] ?? item["return_date"]) ?? baseRow.return_date,
          unit_price: parseImportedNumber(item["Đơn giá travel"] ?? item["unit_price"]) ?? baseRow.unit_price,
          vehicle_ticket: parseImportedNumber(item["Vé xe/Hỗ trợ xăng"] ?? item["vehicle_ticket"]) ?? baseRow.vehicle_ticket,
          unit_price_2: parseImportedNumber(item["Đơn giá vé xe/Hỗ trợ xăng"] ?? item["unit_price_2"]) ?? baseRow.unit_price_2,
          pickup_guests: importedPickupGuests ?? baseRow.pickup_guests,
          unit_price_3: parseImportedNumber(item["Đơn giá đón khách"] ?? item["unit_price_3"]) ?? baseRow.unit_price_3,
          tax_deduction: parseImportedNumber(item["Tiền thuế khấu trừ"] ?? item["tax_deduction"]) ?? baseRow.tax_deduction,
          remaining_amount: parseImportedNumber(item["Số tiền còn lại được nhận"] ?? item["remaining_amount"]) ?? baseRow.remaining_amount,
        };

        if (nextRow.pickup_guests !== 1) {
          nextRow.unit_price_3 = null;
        }

        if (isInvalidDateRange(nextRow.departure_date, nextRow.return_date)) {
          skippedRows += 1;
          return;
        }

        draftRows[rowIndex] = nextRow;
        updateCandidates.push(nextRow);
      });

      if (updateCandidates.length === 0) {
        setErrorRows(true);
        setMessageRows("Không tìm thấy dòng hợp lệ để import.");
        return;
      }

      const results = await Promise.allSettled(
        updateCandidates.map((row) => {
          const url = ApiConfig.project.updateTravelExpense
            .replace("{projectId}", projectId.toString())
            .replace("{travelExpenseId}", row.id.toString());
          return axios.put(url, buildTravelExpensePayload(row));
        })
      );

      const successCount = results.filter((result) => result.status === "fulfilled").length;
      const failedCount = results.length - successCount;

      await fetchTravelExpenses();

      if (failedCount > 0) {
        setErrorRows(true);
        setMessageRows(`Import hoàn tất: ${successCount} dòng thành công, ${failedCount} dòng lỗi, ${skippedRows} dòng bỏ qua.`);
      } else {
        setErrorRows(false);
        setMessageRows(`Import thành công ${successCount} dòng${skippedRows > 0 ? `, bỏ qua ${skippedRows} dòng` : ""}.`);
      }
    } catch (error: any) {
      setErrorRows(true);
      setMessageRows(error?.response?.data?.message || error?.message || "Import thất bại");
    } finally {
      setImportingRows(false);
      if (event.target) event.target.value = "";
    }
  };

  const handleSelectChange = (id: number, field: keyof TravelExpenseRow, event: SelectChangeEvent<number>) => {
    const nextValue = Number(event.target.value);
    const currentRow = rows.find((row) => row.id === id);

    if (!currentRow) return;

    const updatedRow =
      field === "pickup_guests"
        ? {
            ...currentRow,
            pickup_guests: nextValue,
            unit_price_3: nextValue === 1 ? currentRow.unit_price_3 : null,
          }
        : { ...currentRow, [field]: nextValue };

    setRows((prev) =>
      prev.map((row) => {
        if (row.id !== id) return row;

        if (field === "pickup_guests") {
          return {
            ...row,
            pickup_guests: nextValue,
            unit_price_3: nextValue === 1 ? row.unit_price_3 : null,
          };
        }

        return { ...row, [field]: nextValue };
      })
    );

    persistTravelExpenseRow(updatedRow);
  };

  const handleUnitPrice2SelectChange = (id: number, event: SelectChangeEvent<number>) => {
    const nextValue = Number(event.target.value);
    const currentRow = rows.find((row) => row.id === id);

    if (!currentRow) return;

    if (nextValue === CUSTOM_UNIT_PRICE_2_OPTION) {
      setCustomUnitPrice2Rows((prev) => {
        const next = new Set(prev);
        next.add(id);
        return next;
      });
      return;
    }

    const updatedRow = { ...currentRow, unit_price_2: nextValue };

    setCustomUnitPrice2Rows((prev) => {
      const next = new Set(prev);
      next.delete(id);
      return next;
    });

    setRows((prev) =>
      prev.map((row) => {
        if (row.id !== id) return row;
        return { ...row, unit_price_2: nextValue };
      })
    );

    persistTravelExpenseRow(updatedRow);
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

  const handleDateChange = (
    id: number,
    field: "departure_date" | "return_date",
    event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const nextValue = event.target.value || null;
    const currentRow = rows.find((row) => row.id === id);

    if (!currentRow) return;

    const updatedRow = { ...currentRow, [field]: nextValue };

    if (isInvalidDateRange(updatedRow.departure_date, updatedRow.return_date)) {
      setErrorRows(true);
      setMessageRows("Ngày về không được sớm hơn ngày đi.");
      return;
    }

    setRows((prev) =>
      prev.map((row) => {
        if (row.id !== id) return row;
        return { ...row, [field]: nextValue };
      })
    );

    setErrorRows(false);
    setMessageRows(null);

    persistTravelExpenseRow(updatedRow);
  };

  const handleAmountInputBlur = (id: number) => {
    const row = rows.find((item) => item.id === id);

    if (!row) return;

    persistTravelExpenseRow(row);
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

  const exportRows = useMemo(
    () =>
      filteredRows.map((row) => ({
        "Mã số PVV": row.employee_ids,
        "Họ và tên": row.full_name,
        "Ngày đi": row.departure_date ?? "",
        "Ngày về": row.return_date ?? "",
        "Số samples hoàn thành": Number(row.completed_samples ?? 0),
        "Ngày travel": Number(calculateWorkingDays(row.completed_samples) ?? 0),
        "Đơn giá travel": Number(row.unit_price ?? 0),
        "Vé xe/Hỗ trợ xăng": Number(row.vehicle_ticket ?? 0),
        "Đơn giá vé xe/Hỗ trợ xăng": Number(row.unit_price_2 ?? 0),
        "Đón khách": row.pickup_guests === 1 ? "Có" : "Không",
        "Đơn giá đón khách": Number(row.unit_price_3 ?? 0),
        "Tổng lương": Number(calculateTotalSalary(row) ?? 0),
        "Tiền thuế khấu trừ": Number(row.tax_deduction ?? 0),
        "Số tiền còn lại được nhận": Number(row.remaining_amount ?? 0),
        "Khu vực": row.region,
      })),
    [filteredRows, target]
  );

  const getExportFileName = (extension: "xlsx" | "csv") => {
    const now = new Date();
    const timestamp = `${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, "0")}${String(now.getDate()).padStart(2, "0")}_${String(now.getHours()).padStart(2, "0")}${String(now.getMinutes()).padStart(2, "0")}`;
    return `travel_expense_${projectId}_${timestamp}.${extension}`;
  };

  const handleOpenExportMenu = (event: React.MouseEvent<HTMLButtonElement>) => {
    setExportAnchorEl(event.currentTarget);
  };

  const handleCloseExportMenu = () => {
    setExportAnchorEl(null);
  };

  const handleExportXlsx = () => {
    if (exportRows.length === 0) {
      handleCloseExportMenu();
      return;
    }

    const worksheet = XLSX.utils.json_to_sheet(exportRows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Travel Expense");
    const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
    const blob = new Blob([excelBuffer], { type: "application/octet-stream" });
    saveAs(blob, getExportFileName("xlsx"));
    handleCloseExportMenu();
  };

  const handleExportCsv = () => {
    if (exportRows.length === 0) {
      handleCloseExportMenu();
      return;
    }

    const worksheet = XLSX.utils.json_to_sheet(exportRows);
    const csv = XLSX.utils.sheet_to_csv(worksheet);
    const blob = new Blob([`\uFEFF${csv}`], { type: "text/csv;charset=utf-8;" });
    saveAs(blob, getExportFileName("csv"));
    handleCloseExportMenu();
  };

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
      label: "Ngày đi",
      name: "departure_date",
      type: "string",
      align: "center",
      width: 160,
      renderCell: (row: TravelExpenseRow) => (
        <TextField
          size="small"
          type="date"
          value={row.departure_date ?? ""}
          onChange={(event) => handleDateChange(row.id, "departure_date", event)}
          InputLabelProps={{ shrink: true }}
          inputProps={{ max: row.return_date ?? undefined }}
          sx={{ minWidth: 140 }}
        />
      ),
    },
    {
      label: "Ngày về",
      name: "return_date",
      type: "string",
      align: "center",
      width: 160,
      renderCell: (row: TravelExpenseRow) => (
        <TextField
          size="small"
          type="date"
          value={row.return_date ?? ""}
          onChange={(event) => handleDateChange(row.id, "return_date", event)}
          InputLabelProps={{ shrink: true }}
          inputProps={{ min: row.departure_date ?? undefined }}
          sx={{ minWidth: 140 }}
        />
      ),
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
      label: "Ngày travel",
      name: "working_days",
      type: "number",
      align: "right",
      width: 100,
      renderCell: (row: TravelExpenseRow) => formatDecimal(calculateWorkingDays(row.completed_samples)),
    },
    {
      label: "Đơn giá travel",
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
          onBlur={() => handleAmountInputBlur(row.id)}
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
      width: 180,
      renderCell: (row: TravelExpenseRow) => {
        const isCustom =
          customUnitPrice2Rows.has(row.id) ||
          (row.unit_price_2 != null && !UNIT_PRICE_2_OPTIONS.includes(row.unit_price_2));

        if (isCustom) {
          return (
            <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
              <TextField
                size="small"
                type="number"
                value={row.unit_price_2 ?? ""}
                onChange={(event) => handleAmountInputChange(row.id, "unit_price_2", event)}
                onBlur={() => handleAmountInputBlur(row.id)}
                placeholder="Nhập đơn giá"
                inputProps={{ min: 0, step: 1000 }}
                InputProps={{
                  endAdornment: <InputAdornment position="end">₫</InputAdornment>,
                }}
                sx={{ minWidth: 120 }}
                autoFocus
              />
              <Tooltip title="Chọn từ danh sách">
                <IconButton
                  size="small"
                  onClick={() => {
                    setCustomUnitPrice2Rows((prev) => {
                      const next = new Set(prev);
                      next.delete(row.id);
                      return next;
                    });
                    setRows((prev) =>
                      prev.map((r) => (r.id === row.id ? { ...r, unit_price_2: null } : r))
                    );
                    persistTravelExpenseRow({ ...row, unit_price_2: null });
                  }}
                  sx={{
                    color: "#757575",
                    "&:hover": { color: "#1976d2" },
                  }}
                >
                  <CloseIcon fontSize="small" />
                </IconButton>
              </Tooltip>
            </Box>
          );
        }

        return (
          <FormControl size="small" sx={{ minWidth: 150 }}>
            <Select<number>
              value={row.unit_price_2 ?? ""}
              displayEmpty
              onChange={(event) => handleUnitPrice2SelectChange(row.id, event as SelectChangeEvent<number>)}
            >
              <MenuItem value="">
                <span style={{ color: "#8a8a8a" }}>Chọn đơn giá</span>
              </MenuItem>
              {UNIT_PRICE_2_OPTIONS.map((opt) => (
                <MenuItem key={opt} value={opt}>
                  {formatMoney(opt)}
                </MenuItem>
              ))}
              <MenuItem value={CUSTOM_UNIT_PRICE_2_OPTION}>Khác (nhập tay)</MenuItem>
            </Select>
          </FormControl>
        );
      },
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
            value={row.pickup_guests ?? 0}
            onChange={(event) => handleSelectChange(row.id, "pickup_guests", event as SelectChangeEvent<number>)}
          >
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
          value={row.pickup_guests === 1 ? row.unit_price_3 ?? "" : ""}
          disabled={row.pickup_guests !== 1}
          onChange={(event) => handleAmountInputChange(row.id, "unit_price_3", event)}
          onBlur={() => handleAmountInputBlur(row.id)}
          placeholder={row.pickup_guests === 1 ? "Nhập số tiền" : "Chọn Có để nhập"}
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
      renderCell: (row: TravelExpenseRow) => formatMoney(calculateTotalSalary(row)),
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
            <div>
              <strong>Project Type:</strong> {projectTypesDisplay}
            </div>
            <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
              {isHutProject ? (
                <>
                  <strong>Target Recruit:</strong>
                  <TextField
                    size="small"
                    type="number"
                    value={targetRecruit}
                    onChange={(event) => setTargetRecruit(event.target.value)}
                    onBlur={() => handleHutTargetBlur("recruit")}
                    placeholder="Nhập target recruit"
                    inputProps={{ min: 1 }}
                    sx={{ width: 160 }}
                  />
                  <strong>Target Recall:</strong>
                  <TextField
                    size="small"
                    type="number"
                    value={targetRecall}
                    onChange={(event) => setTargetRecall(event.target.value)}
                    onBlur={() => handleHutTargetBlur("recall")}
                    placeholder="Nhập target recall"
                    inputProps={{ min: 1 }}
                    sx={{ width: 160 }}
                  />
                </>
              ) : (
                <>
                  <strong>Target:</strong>
                  <TextField
                    size="small"
                    type="number"
                    value={target}
                    onChange={(event) => setTarget(event.target.value)}
                    onBlur={handleTargetBlur}
                    placeholder="Nhập target"
                    inputProps={{ min: 1 }}
                    sx={{ width: 140 }}
                  />
                </>
              )}
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
          <Button
            className="btn"
            variant="outlined"
            startIcon={<FileUploadIcon />}
            onClick={() => importFileInputRef.current?.click()}
            disabled={loadingRows || importingRows}
            sx={{ mr: 1 }}
          >
            Import
          </Button>
          <input
            ref={importFileInputRef}
            type="file"
            accept=".xlsx,.xls,.csv"
            style={{ display: "none" }}
            onChange={handleImportFile}
          />
          <Button
            className="btn"
            variant="contained"
            startIcon={<FileDownloadIcon />}
            onClick={handleOpenExportMenu}
            disabled={loadingRows || importingRows || filteredRows.length === 0}
            sx={{ mr: 1 }}
          >
            Export
          </Button>
          <Menu
            anchorEl={exportAnchorEl}
            open={Boolean(exportAnchorEl)}
            onClose={handleCloseExportMenu}
          >
            <MenuItem onClick={handleExportXlsx}>Export XLSX</MenuItem>
            <MenuItem onClick={handleExportCsv}>Export CSV</MenuItem>
          </Menu>
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
