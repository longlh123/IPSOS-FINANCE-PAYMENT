import { useState, useEffect } from "react";
import axios from "axios";
import { Select, MenuItem, Box, Button, Card, CardContent, Typography } from "@mui/material";
import { useSearchParams } from "react-router-dom";

export default function MiniCATI() {
  const [current, setCurrent] = useState<any>(null);

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

  // 🔥 Load options từ API
  useEffect(() => {
    axios.get("https://dev.ippay.vn/api/filters").then((res) => {
      setOptions(res.data);
    });
  }, []);

  // 🔥 Khi đổi filter → reset sample
  useEffect(() => {
    setCurrent(null);
  }, [filters]);

  const getNext = async () => {
    try {
      const res = await axios.post("https://dev.ippay.vn/api/next", {
        user: employeeId,
        ...filters
      });
      setCurrent(res.data);
    } catch (err: any) {
      console.error(err.response?.data);
    }
  };

  const updateStatus = async (status: string) => {
    await axios.post("https://dev.ippay.vn/api/update-status", {
      id: current.id,
      status
    });
    getNext();
  };

  return (
    <Box p={2}>
      
      {/* ================= FILTER ================= */}
      <Box display="flex" gap={2} mb={2}>
        
        <Select
          value={filters.filter_1}
          displayEmpty
          onChange={(e) =>
            setFilters({ ...filters, filter_1: e.target.value })
          }
        >
          <MenuItem value="">All</MenuItem>
          {options.filter_1.map((item: any) => (
            <MenuItem key={item} value={item}>{item}</MenuItem>
          ))}
        </Select>

        <Select
          value={filters.filter_2}
          displayEmpty
          onChange={(e) =>
            setFilters({ ...filters, filter_2: e.target.value })
          }
        >
          <MenuItem value="">All</MenuItem>
          {options.filter_2.map((item: any) => (
            <MenuItem key={item} value={item}>{item}</MenuItem>
          ))}
        </Select>

        <Select
          value={filters.filter_3}
          displayEmpty
          onChange={(e) =>
            setFilters({ ...filters, filter_3: e.target.value })
          }
        >
          <MenuItem value="">All</MenuItem>
          {options.filter_3.map((item: any) => (
            <MenuItem key={item} value={item}>{item}</MenuItem>
          ))}
        </Select>

        <Select
          value={filters.filter_4}
          displayEmpty
          onChange={(e) =>
            setFilters({ ...filters, filter_4: e.target.value })
          }
        >
          <MenuItem value="">All</MenuItem>
          {options.filter_4.map((item: any) => (
            <MenuItem key={item} value={item}>{item}</MenuItem>
          ))}
        </Select>

        <Button variant="contained" onClick={getNext}>
          Next
        </Button>
      </Box>

      {/* ================= CALL SCREEN ================= */}
      {current?.id ? (
        
        <Box>
            <Card sx={{ maxWidth: 500, margin: "auto", mt: 3, borderRadius: 3, boxShadow: 3 }}>
                <CardContent>

                    {/* 🔥 Title */}
                    <Typography variant="h6" fontWeight="bold" mb={2}>
                    Respondent Information
                    </Typography>

                    {/* 🔥 Info */}
                    <Box mb={2}>
                        <Typography>
                            <strong>Phone:</strong> {current.phone}
                        </Typography>

                        <Typography>
                            <strong>Name:</strong> {current.name || "-"}
                        </Typography>

                        <Typography>
                            <strong>Link:</strong>{" "}
                            <a href={current.link} target="_blank" rel="noopener noreferrer">
                            {current.link}
                            </a>
                        </Typography>
                    </Box>

                    {/* 🔥 Action buttons */}
                    <Box display="grid" gridTemplateColumns="1fr 1fr" gap={2}>
                        <Button onClick={() => updateStatus("Done")} variant="contained" color="success">
                            Done
                        </Button>

                        <Button onClick={() => updateStatus("Busy")} variant="contained" color="warning">
                            Busy
                        </Button>

                        <Button onClick={() => updateStatus("No Answer")} variant="contained" color="info">
                            No Answer
                        </Button>

                        <Button onClick={() => updateStatus("Refuse")} variant="contained" color="error">
                            Refuse
                        </Button>
                    </Box>

                </CardContent>
            </Card>
        </Box>
      ) : (
        <p>No sample available</p>
      )}
    </Box>
  );
}