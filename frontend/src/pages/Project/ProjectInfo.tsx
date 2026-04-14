import { useEffect, useMemo, useState } from "react";
import { useParams } from "react-router-dom";
import {
  Alert,
  Box,
  Button,
  Chip,
  CircularProgress,
  FormControl,
  InputLabel,
  LinearProgress,
  MenuItem,
  Paper,
  Select,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
} from "@mui/material";
import axios from "../../config/axiosInstance";
import { ApiConfig } from "../../config/ApiConfig";
import { useProjects } from "../../hook/useProjects";
import { useMetadata } from "../../hook/useMetadata";
import { ProjectData } from "../../config/ProjectFieldsConfig";
import { useAuth } from "../../contexts/AuthContext";

interface ProjectInfoForm {
  internal_code: string;
  project_name: string;
  symphony: string;
  job_number: string;
  planned_field_start: string;
  planned_field_end: string;
  project_types: string[];
  teams: string[];
  permissions: string;
}

const toInputDate = (value?: string | null) => {
  if (!value) return "";
  return value.slice(0, 10);
};

const formatNumber = (value: number) => value.toLocaleString("vi-VN");

const getMainSamples = (province: any) => Number(province?.sample_size_main ?? 0);
const getBoosterSamples = (province: any) => Number(province?.sample_size_booters ?? province?.sample_size_boosters ?? 0);

const ProjectInfo: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const projectId = Number(id) || 0;

  const { user } = useAuth();
  const isAdmin = (user?.role ?? "").toLowerCase() === "admin";

  const { getProject } = useProjects();
  const { metadata } = useMetadata();

  const [project, setProject] = useState<ProjectData | null>(null);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [editMode, setEditMode] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const [form, setForm] = useState<ProjectInfoForm>({
    internal_code: "",
    project_name: "",
    symphony: "",
    job_number: "",
    planned_field_start: "",
    planned_field_end: "",
    project_types: [],
    teams: [],
    permissions: "",
  });

  const teamOptions = useMemo(() => metadata?.teams?.map((item: any) => item.name) ?? [], [metadata]);
  const projectTypeOptions = useMemo(() => metadata?.project_types?.map((item: any) => item.name) ?? [], [metadata]);

  const loadProject = async () => {
    if (!projectId) return;

    try {
      setLoading(true);
      setError(null);

      const data = await getProject(projectId);
      setProject(data);
      setForm({
        internal_code: data?.internal_code ?? "",
        project_name: data?.project_name ?? "",
        symphony: data?.symphony ?? "",
        job_number: (data as any)?.job_number ?? "",
        planned_field_start: toInputDate(data?.planned_field_start),
        planned_field_end: toInputDate(data?.planned_field_end),
        project_types: data?.project_types ?? [],
        teams: data?.teams ?? [],
        permissions: ((data as any)?.permissions ?? []).join(", "),
      });
    } catch (err: any) {
      setError(err?.response?.data?.message || err?.message || "Failed to load project info");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProject();
  }, [projectId]);

  const handleTextChange = (field: keyof ProjectInfoForm, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSave = async () => {
    if (!projectId) return;

    try {
      setSaving(true);
      setError(null);
      setSuccess(null);

      const payload = {
        internal_code: form.internal_code,
        project_name: form.project_name,
        symphony: form.symphony,
        job_number: form.job_number,
        planned_field_start: form.planned_field_start,
        planned_field_end: form.planned_field_end,
        project_types: form.project_types,
        teams: form.teams,
        permissions: form.permissions
          .split(",")
          .map((item) => item.trim())
          .filter(Boolean),
      };

      const url = ApiConfig.project.updateProject.replace("{projectId}", projectId.toString());
      await axios.put(url, payload);

      setSuccess("Project information updated successfully.");
      setEditMode(false);
      await loadProject();
    } catch (err: any) {
      setError(err?.response?.data?.message || err?.message || "Failed to update project info");
    } finally {
      setSaving(false);
    }
  };

  const provincesText = useMemo(() => {
    if (!project?.provinces || project.provinces.length === 0) return "-";
    return project.provinces
      .map((p: any) => `${p.name} (main: ${getMainSamples(p)}, boosters: ${getBoosterSamples(p)})`)
      .join("; ");
  }, [project]);

  const sampleSummary = useMemo(() => {
    const provinces = (project?.provinces as any[]) ?? [];
    const totalMain = provinces.reduce((sum, province) => sum + getMainSamples(province), 0);
    const totalBoosters = provinces.reduce((sum, province) => sum + getBoosterSamples(province), 0);
    const totalPlanned = totalMain + totalBoosters;
    const completed = Number((project as any)?.count_respondents ?? 0);
    const completionRate = totalPlanned > 0 ? Math.min((completed / totalPlanned) * 100, 100) : 0;

    return {
      totalMain,
      totalBoosters,
      totalPlanned,
      completed,
      remaining: Math.max(totalPlanned - completed, 0),
      completionRate,
    };
  }, [project]);

  const sampleDetailRows = useMemo(() => {
    const provinces = (project?.provinces as any[]) ?? [];
    const totalPlanned = sampleSummary.totalPlanned;
    const completed = sampleSummary.completed;

    return provinces
      .map((province) => {
        const main = getMainSamples(province);
        const boosters = getBoosterSamples(province);
        const total = main + boosters;
        const contributionRate = totalPlanned > 0 ? (total / totalPlanned) * 100 : 0;
        const estimatedCompleted = Math.round((contributionRate / 100) * completed);

        return {
          id: province.id,
          name: province.name ?? "-",
          main,
          boosters,
          total,
          contributionRate,
          estimatedCompleted,
          estimatedRemaining: Math.max(total - estimatedCompleted, 0),
        };
      })
      .sort((a, b) => b.total - a.total);
  }, [project, sampleSummary]);

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="240px">
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box className="box-table">
      <div className="filter">
        <div className="filter-left">
          <h2 className="filter-title">Project Information</h2>
        </div>
        <div className="filter-right" style={{ gap: 8, display: "flex" }}>
          {isAdmin && !editMode && (
            <Button className="btn" variant="contained" onClick={() => setEditMode(true)}>
              Edit
            </Button>
          )}
          {isAdmin && editMode && (
            <>
              <Button className="btn" variant="outlined" onClick={() => { setEditMode(false); loadProject(); }}>
                Cancel
              </Button>
              <Button className="btn" variant="contained" onClick={handleSave} disabled={saving}>
                {saving ? "Saving..." : "Save"}
              </Button>
            </>
          )}
        </div>
      </div>

      {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
      {success && <Alert severity="success" sx={{ mb: 2 }}>{success}</Alert>}

      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell sx={{ width: "28%", fontWeight: 700 }}>Field</TableCell>
              <TableCell sx={{ fontWeight: 700 }}>Value</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>{project?.id ?? "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Internal Code</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField size="small" fullWidth value={form.internal_code} onChange={(e) => handleTextChange("internal_code", e.target.value)} />
                ) : (
                  project?.internal_code ?? "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Project Name</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField size="small" fullWidth value={form.project_name} onChange={(e) => handleTextChange("project_name", e.target.value)} />
                ) : (
                  project?.project_name ?? "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Symphony</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField size="small" fullWidth value={form.symphony} onChange={(e) => handleTextChange("symphony", e.target.value)} />
                ) : (
                  project?.symphony ?? "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Job Number</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField size="small" fullWidth value={form.job_number} onChange={(e) => handleTextChange("job_number", e.target.value)} />
                ) : (
                  ((project as any)?.job_number ?? "-")
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Platform</TableCell>
              <TableCell>{project?.platform ?? "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Status</TableCell>
              <TableCell>{(project as any)?.status ?? "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Planned Field Start</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField size="small" type="date" value={form.planned_field_start} onChange={(e) => handleTextChange("planned_field_start", e.target.value)} />
                ) : (
                  toInputDate(project?.planned_field_start) || "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Planned Field End</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField size="small" type="date" value={form.planned_field_end} onChange={(e) => handleTextChange("planned_field_end", e.target.value)} />
                ) : (
                  toInputDate(project?.planned_field_end) || "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Actual Field Start</TableCell>
              <TableCell>{toInputDate(project?.actual_field_start) || "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Actual Field End</TableCell>
              <TableCell>{toInputDate(project?.actual_field_end) || "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Project Types</TableCell>
              <TableCell>
                {editMode ? (
                  <FormControl fullWidth size="small">
                    <InputLabel id="project-types-label">Project Types</InputLabel>
                    <Select
                      labelId="project-types-label"
                      multiple
                      label="Project Types"
                      value={form.project_types}
                      onChange={(e) => setForm((prev) => ({ ...prev, project_types: e.target.value as string[] }))}
                    >
                      {projectTypeOptions.map((item: string) => (
                        <MenuItem key={item} value={item}>{item}</MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                ) : (
                  (project?.project_types ?? []).join(", ") || "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Teams</TableCell>
              <TableCell>
                {editMode ? (
                  <FormControl fullWidth size="small">
                    <InputLabel id="teams-label">Teams</InputLabel>
                    <Select
                      labelId="teams-label"
                      multiple
                      label="Teams"
                      value={form.teams}
                      onChange={(e) => setForm((prev) => ({ ...prev, teams: e.target.value as string[] }))}
                    >
                      {teamOptions.map((item: string) => (
                        <MenuItem key={item} value={item}>{item}</MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                ) : (
                  (project?.teams ?? []).join(", ") || "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Permissions (emails)</TableCell>
              <TableCell>
                {editMode ? (
                  <TextField
                    size="small"
                    fullWidth
                    multiline
                    minRows={2}
                    value={form.permissions}
                    onChange={(e) => handleTextChange("permissions", e.target.value)}
                    placeholder="email1@example.com, email2@example.com"
                  />
                ) : (
                  (((project as any)?.permissions ?? []) as string[]).join(", ") || "-"
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Provinces</TableCell>
              <TableCell>{provincesText}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Sample Overview</TableCell>
              <TableCell>
                <Box
                  sx={{
                    display: "grid",
                    gridTemplateColumns: { xs: "1fr", md: "repeat(4, minmax(0, 1fr))" },
                    gap: 1,
                    mb: 1.5,
                  }}
                >
                  <Paper variant="outlined" sx={{ p: 1.25 }}>
                    <Box sx={{ fontSize: 12, color: "text.secondary" }}>Main Samples</Box>
                    <Box sx={{ fontSize: 18, fontWeight: 700 }}>{formatNumber(sampleSummary.totalMain)}</Box>
                  </Paper>
                  <Paper variant="outlined" sx={{ p: 1.25 }}>
                    <Box sx={{ fontSize: 12, color: "text.secondary" }}>Booster Samples</Box>
                    <Box sx={{ fontSize: 18, fontWeight: 700 }}>{formatNumber(sampleSummary.totalBoosters)}</Box>
                  </Paper>
                  <Paper variant="outlined" sx={{ p: 1.25 }}>
                    <Box sx={{ fontSize: 12, color: "text.secondary" }}>Total Planned</Box>
                    <Box sx={{ fontSize: 18, fontWeight: 700 }}>{formatNumber(sampleSummary.totalPlanned)}</Box>
                  </Paper>
                  <Paper variant="outlined" sx={{ p: 1.25 }}>
                    <Box sx={{ fontSize: 12, color: "text.secondary" }}>Progress</Box>
                    <Box sx={{ fontSize: 18, fontWeight: 700 }}>{sampleSummary.completionRate.toFixed(1)}%</Box>
                  </Paper>
                </Box>

                <Box sx={{ display: "flex", alignItems: "center", gap: 1, mb: 1.25, flexWrap: "wrap" }}>
                  <Chip size="small" color="success" label={`Completed: ${formatNumber(sampleSummary.completed)}`} />
                  <Chip size="small" color="warning" label={`Remaining: ${formatNumber(sampleSummary.remaining)}`} />
                  <Chip size="small" variant="outlined" label={`Total: ${formatNumber(sampleSummary.totalPlanned)}`} />
                </Box>

                <LinearProgress
                  variant="determinate"
                  value={sampleSummary.completionRate}
                  sx={{
                    height: 8,
                    borderRadius: 999,
                  }}
                />
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Sample Detail by Province</TableCell>
              <TableCell>
                {sampleDetailRows.length === 0 ? (
                  "-"
                ) : (
                  <TableContainer component={Paper} variant="outlined">
                    <Table size="small">
                      <TableHead>
                        <TableRow>
                          <TableCell>Tỉnh/Thành</TableCell>
                          <TableCell align="right">Main</TableCell>
                          <TableCell align="right">Boosters</TableCell>
                          <TableCell align="right">Total</TableCell>
                          <TableCell align="right">Tỷ trọng</TableCell>
                          <TableCell align="right">Completed (Ước tính)</TableCell>
                          <TableCell align="right">Remaining (Ước tính)</TableCell>
                        </TableRow>
                      </TableHead>
                      <TableBody>
                        {sampleDetailRows.map((row) => (
                          <TableRow key={row.id}>
                            <TableCell>{row.name}</TableCell>
                            <TableCell align="right">{formatNumber(row.main)}</TableCell>
                            <TableCell align="right">{formatNumber(row.boosters)}</TableCell>
                            <TableCell align="right" sx={{ fontWeight: 700 }}>
                              {formatNumber(row.total)}
                            </TableCell>
                            <TableCell align="right">{row.contributionRate.toFixed(1)}%</TableCell>
                            <TableCell align="right">{formatNumber(row.estimatedCompleted)}</TableCell>
                            <TableCell align="right">{formatNumber(row.estimatedRemaining)}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </TableContainer>
                )}
              </TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Project Objectives</TableCell>
              <TableCell>{(project as any)?.project_objectives ?? "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Remember UUID</TableCell>
              <TableCell>{(project as any)?.remember_uuid ?? "-"}</TableCell>
            </TableRow>

            <TableRow>
              <TableCell>Remember Token</TableCell>
              <TableCell>{(project as any)?.remember_token ?? "-"}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </TableContainer>
    </Box>
  );
};

export default ProjectInfo;