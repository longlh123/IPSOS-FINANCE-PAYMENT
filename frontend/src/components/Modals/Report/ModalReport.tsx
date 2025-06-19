import { useState, useEffect, useCallback, memo } from "react";
import {
  Modal,
  Box,
  Typography,
  Divider,
  Grid,
  Button,
  TableContainer,
  TableHead,
  Table,
  TableRow,
  TableCell,
  TableBody,
} from "@mui/material";
import { TableCellConfig } from "../../../config/TableProjectConfig";
import * as XLSX from "xlsx";

interface ModalReportProps {
  openModal: boolean;
  onClose: () => void;
  reportValue?: any;
}

const ModalReport: React.FC<ModalReportProps> = ({
  openModal,
  onClose,
  reportValue,
}) => {
  const [formData, setFormData] = useState<any>(reportValue || {});

  useEffect(() => {
    setFormData(reportValue);
  }, [reportValue]);

  const handleImport = useCallback((event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;
  }, []);

  const handleExport = useCallback(() => {
    // Prepare the data
    const data = [
      TableCellConfig.map((item) => item.label), // Header row
      TableCellConfig.map((item) => reportValue[item.name]), // Data row
    ];

    // Create a new workbook and worksheet
    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.aoa_to_sheet(data);

    // Apply styles to the header row
    const headerRange = XLSX.utils.decode_range(worksheet["!ref"] as string);
    for (let C = headerRange.s.c; C <= headerRange.e.c; ++C) {
      const cellAddress = XLSX.utils.encode_cell({ c: C, r: 0 });
      if (!worksheet[cellAddress]) continue;
      worksheet[cellAddress].s = {
        fill: {
          fgColor: { rgb: "0000FF" }, // Blue background
        },
        font: {
          color: { rgb: "FFFFFF" }, // White text
          bold: true,
        },
        alignment: {
          horizontal: "center",
          vertical: "center",
        },
      };
    }

    // Add the worksheet to the workbook
    XLSX.utils.book_append_sheet(workbook, worksheet, "Report");

    // Generate Excel file and trigger download
    XLSX.writeFile(workbook, "report.xlsx");
  }, [reportValue]);

  return (
    <Modal
      open={openModal}
      onClose={onClose}
      aria-labelledby="modal-modal-title"
      aria-describedby="modal-modal-description"
    >
      <Box className="modal-box">
        <Typography
          id="modal-modal-title"
          variant="h6"
          component="h2"
          textAlign="center"
        >
          Report
        </Typography>
        <Divider />

        <Grid container className="content-modal">
          <TableContainer className="table-container">
            <Table>
              <TableHead>
                <TableRow>
                  {TableCellConfig.map((item, index) => {
                    return <TableCell key={index}>{item.name}</TableCell>;
                  })}
                </TableRow>
              </TableHead>
              <TableBody>
                <TableRow>
                  {TableCellConfig.map((item, index) => {
                    return (
                      <TableCell key={index}>
                        {/* {formData[item.value]} */}
                      </TableCell>
                    );
                  })}
                </TableRow>
              </TableBody>
            </Table>
          </TableContainer>
        </Grid>
        <Box className="btn-modal-footer" textAlign="end">
          <Button className="btn-modal-cancel" onClick={onClose}>
            Cancel
          </Button>
          
          <input
            accept=".xlsx, .xls, .csv"
            style={{ display: "none" }}
            id="import-file"
            type="file"
            onChange={handleImport}
          />
          <label htmlFor="import-file">
            <Button
              className="btn-modal-submit"
              variant="contained"
              component="span"
            >
              Import File
            </Button>
          </label>

          <Button
            className="btn-modal-submit"
            variant="contained"
            onClick={handleExport}
          >
            Export File
          </Button>
        </Box>
      </Box>
    </Modal>
  );
};

export default memo(ModalReport);