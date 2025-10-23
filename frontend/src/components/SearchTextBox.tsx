import SearchIcon from "@mui/icons-material/Search";
import { InputAdornment, TextField } from "@mui/material"
import { useState } from "react";

const SearchTextBox = () => {
    const [ search, setSearch ] = useState("");

    const hanleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setSearch(event.target.value)
        console.log("Search: ", event.target.value)
    }

    return (
        <div style={{display: "flex", gap: "0.5rem", alignItems: "center" }}>
            <TextField
                value={search}
                onChange={hanleChange}
                placeholder="Search..."
                variant="outlined"
                size="small"
                fullWidth
                InputProps={{
                    startAdornment: (
                        <InputAdornment position="start">
                            <SearchIcon />
                        </InputAdornment>
                    ),
                }}
            >

            </TextField>
        </div>
    )
}

export default SearchTextBox;