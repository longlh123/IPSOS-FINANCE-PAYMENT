import dayjs, { Dayjs } from "dayjs"
import SearchDatePicker from "./SearchDatePicker"
import React, { useState } from "react"
import { Button } from "@mui/material"

interface SearchDatePickerFromToProps {
    fromValue: Dayjs | null,
    toValue: Dayjs | null,
    onSearchChange: (from: Dayjs | null, to: Dayjs | null) => void
}

const SearchDatePickerFromTo: React.FC<SearchDatePickerFromToProps> = ({fromValue=dayjs().startOf("year"), toValue=dayjs().endOf("year"), onSearchChange}) => {

    const [ fromDate, setFromDate ] = useState<Dayjs | null>(fromValue);
    const [ toDate, setToDate ] = useState<Dayjs | null>(toValue);

    const handleDateChange = (name: string, value: Dayjs | null) => {
        if(!value) return;

        if(name == "fromDate"){
            setFromDate(value);
        }
        if(name == "toDate"){
            setToDate(value)
        }
    }

    const handleFilter = () => {
        onSearchChange(fromDate, toDate);
    }

    const isApplyFilter = !fromDate || !toDate || (fromDate && toDate && fromDate.isAfter(toDate));
    
    return (
        <div style={{display: "flex", gap: "0.5rem", alignItems: "center", width: "16rem"}}>
            <SearchDatePicker title="From:" name= "fromDate" value={fromDate} onSearchChange={handleDateChange} />
            <SearchDatePicker title="To:" name= "toDate" value={toDate} onSearchChange={handleDateChange} />
            <Button
                variant="contained"
                className="btn bg-primary"
                onClick={handleFilter}
                disabled={isApplyFilter}
            >
                <span>Apply</span>
            </Button>
        </div>
    )
}

export default SearchDatePickerFromTo;