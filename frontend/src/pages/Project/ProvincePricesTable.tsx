import axios from "axios";
import { useCallback, useEffect, useState } from "react";
import { Autocomplete, Box, IconButton, TextField } from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import AddIcon from "@mui/icons-material/Add";
import { Button } from "@mui/material";
import ReusableTable from "../../components/Table/ReusableTable";
import { ColumnFormat } from "../../config/ColumnConfig";
import { ApiConfig } from "../../config/ApiConfig";

interface PriceRow {
    province_id: number;
    province_name: string;
    price_type: string;
    sample_size: number
    price: number;
    price_1: number;
    price_2: number;
    price_3: number;
    price_4: number;
    price_5: number;
}

interface Province {
    id: number;
    name: string;
}

const PRICE_TYPES = [
    "main", "main_1", "main_2", "main_3", "main_4", "main_5",
    "booster", "booster_1", "booster_2", "booster_3", "booster_4", "booster_5",
    "non", "non_1", "non_2", "non_3", "non_4", "non_5",
];

type Props = {
    projectId: number;
    canEdit: boolean;
};

const ProvincePricesTable = ({ projectId, canEdit }: Props) => {
    const [rows, setRows] = useState<PriceRow[]>([]);
    const [provinces, setProvinces] = useState<Province[]>([]);
    const [loading, setLoading] = useState(false);

    const [draftProvince, setDraftProvince] = useState<Province | null>(null);
    const [draftType, setDraftType] = useState<string | null>(null);
    const [draftPrice, setDraftPrice] = useState("");
    const [draftSampleSize, setDraftSampleSize] = useState("");
    const [disableDraft, setDisableDraft] = useState(true);
    const [adding, setAdding] = useState(false);

    const token = localStorage.getItem("authToken");
    const headers = { Authorization: `Bearer ${token}` };

    const fetchPrices = useCallback(async () => {
        setLoading(true);
        try {
            const url = ApiConfig.project.getProjectPrices.replace("{projectId}", projectId.toString());
            const res = await axios.get(url, { headers });
            setRows(res.data.data ?? []);
        } catch (e) {
            console.log(e);
        } finally {
            setLoading(false);
        }
    }, [projectId]);

    useEffect(() => {
        fetchPrices();
        axios.get(ApiConfig.administrative.getProvinces, { headers })
            .then(res => setProvinces(res.data.data ?? []))
            .catch(console.log);
    }, [fetchPrices]);

    const handleAdd = async () => {
        if (!draftProvince || !draftType || !draftPrice) return;
        setAdding(true);
        try {
            const url = ApiConfig.project.upsertProjectPrice.replace("{projectId}", projectId.toString());
            const res = await axios.put(url, {
                province_id: draftProvince.id,
                price_type: draftType,
                price: Number(draftPrice),
            }, { headers });
            const saved: PriceRow = res.data.data;
            setRows(prev => {
                const idx = prev.findIndex(r => r.province_id === saved.province_id && r.price_type === saved.price_type);
                if (idx >= 0) {
                    const next = [...prev];
                    next[idx] = saved;
                    return next;
                }
                return [...prev, saved];
            });
            setDraftProvince(null);
            setDraftType(null);
            setDraftPrice("");
        } catch (e) {
            console.log(e);
        } finally {
            setAdding(false);
        }
    };

    const handleDelete = async (row: PriceRow) => {
        try {
            const url = ApiConfig.project.deleteProjectPrice.replace("{projectId}", projectId.toString());
            await axios.delete(url, { headers, data: { province_id: row.province_id, price_type: row.price_type } });
            setRows(prev => prev.filter(r => !(r.province_id === row.province_id && r.price_type === row.price_type)));
        } catch (e) {
            console.log(e);
        }
    };

    const isDraftValid = !!draftProvince && !!draftType && !!draftPrice && Number(draftPrice) > 0;

    const columns: ColumnFormat[] = [
        { label: "Tỉnh thành", name: "province_name", type: "string", flex: 2 },
        { label: "Sample Size", name: "sample_size", type: "number", flex: 1
        },
        { label: "Loại Đơn giá", name: "price_type", type: "string", flex: 1 },
        {
            label: "Đơn giá quà (VNĐ)",
            name: "price",
            type: "number",
            flex: 1,
            renderCell: (row: PriceRow) => row.price.toLocaleString("vi-VN"),
        },
        {
            label: "Đơn giá quà 1 (VNĐ)",
            name: "price_1",
            type: "number",
            flex: 1,
            renderCell: (row: PriceRow) => row.price_1.toLocaleString("vi-VN"),
        },
        {
            label: "Đơn giá quà 2 (VNĐ)",
            name: "price_2",
            type: "number",
            flex: 1,
            renderCell: (row: PriceRow) => row.price_2.toLocaleString("vi-VN"),
        },
        {
            label: "Đơn giá quà 3 (VNĐ)",
            name: "price_3",
            type: "number",
            flex: 1,
            renderCell: (row: PriceRow) => row.price_3.toLocaleString("vi-VN"),
        },
        {
            label: "Đơn giá quà 4 (VNĐ)",
            name: "price_4",
            type: "number",
            flex: 1,
            renderCell: (row: PriceRow) => row.price_4.toLocaleString("vi-VN"),
        },
        {
            label: "Đơn giá quà 5 (VNĐ)",
            name: "price_5",
            type: "number",
            flex: 1,
            renderCell: (row: PriceRow) => row.price_5.toLocaleString("vi-VN"),
        },
        ...(canEdit ? [{
            label: "",
            name: "actions",
            type: "menu" as const,
            align: "center" as const,
            width: 60,
            renderAction: (row: PriceRow) => (
                <IconButton size="small" color="error" onClick={() => handleDelete(row)}>
                    <DeleteIcon fontSize="small" />
                </IconButton>
            ),
        }] : []),
    ];

    return (
        <ReusableTable
            title="Đơn giá theo tỉnh thành"
            columns={columns}
            data={rows}
            actionStatus={{ type: loading ? "fetch" : "idle", loading, error: false, message: "" }}
            topToolbar={canEdit ? (
                <Box sx={{ display: "flex", gap: 1.5, alignItems: "center", flexWrap: "wrap" }}>
                    <Autocomplete
                        size="small"
                        options={provinces}
                        value={draftProvince}
                        getOptionLabel={(o) => o.name}
                        isOptionEqualToValue={(o, v) => o.id === v.id}
                        onChange={(_, v) => {
                            let samplesize = 0;
                            for(const row of rows){
                                if(row.province_name == v?.name){
                                    samplesize = row.sample_size;
                                }
                            }
                            setDraftProvince(v);
                            setDraftSampleSize(!v ? "" : samplesize.toString());
                            setDisableDraft(!v)
                        }}
                        sx={{ minWidth: 220 }}
                        renderInput={(params) => <TextField {...params} label="Tỉnh thành" />}
                    />
                    <Autocomplete
                        size="small"
                        options={PRICE_TYPES}
                        value={draftType}
                        disabled={disableDraft}
                        onChange={(_, v) => setDraftType(v)}
                        sx={{ minWidth: 160 }}
                        renderInput={(params) => <TextField {...params} label="Loại Đơn giá" />}
                    />
                    <TextField
                        size="small"
                        label="Sample Size"
                        type="number"
                        disabled={Number(draftSampleSize) > 0}
                        value={draftSampleSize}
                        onChange={(e) => setDraftSampleSize(e.target.value)}
                        inputProps={{ min: 0 }}
                        sx={{ minWidth: 150 }}

                    />
                    <TextField
                        size="small"
                        label="Đơn giá quà"
                        type="number"
                        disabled={disableDraft}
                        value={draftPrice}
                        onChange={(e) => setDraftPrice(e.target.value)}
                        inputProps={{ min: 0 }}
                        sx={{ minWidth: 150 }}
                    />
                    <Button
                        className="btn"
                        startIcon={<AddIcon />}
                        disabled={!isDraftValid || adding}
                        onClick={handleAdd}
                    >
                        {adding ? "Saving..." : "Add"}
                    </Button>
                </Box>
            ) : undefined}
        />
    );
};

export default ProvincePricesTable;
