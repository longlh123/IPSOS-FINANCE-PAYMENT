import dayjs, { Dayjs } from "dayjs"
import SearchDatePicker from "./SearchDatePicker"
import React, { useState } from "react"
import { Button } from "@mui/material"
import { DateView } from '@mui/x-date-pickers/models';
import LoadingButton from "@mui/lab/LoadingButton";

interface SearchDatePickerFromToProps {
    fromValue: Dayjs | null,
    toValue: Dayjs | null,
    views?: DateView[],
    buttonLabel?: string,
    loadingButton?: boolean,
    onSearchChange: (from: Dayjs | null, to: Dayjs | null) => void
}

const SearchDatePickerFromTo: React.FC<SearchDatePickerFromToProps> = ({
                                    fromValue=dayjs().startOf("year"), 
                                    toValue=dayjs().endOf("year"), 
                                    views=['year', 'month', 'day'],
                                    buttonLabel,
                                    loadingButton,
                                    onSearchChange }) => {

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
        <div style={{display: "flex", gap: "0.5rem", alignItems: "center" }}>
            <SearchDatePicker title="From:" name= "fromDate" value={fromDate} views={views} onSearchChange={handleDateChange} />
            <SearchDatePicker title="To:" name= "toDate" value={toDate} views={views} onSearchChange={handleDateChange} />
            <LoadingButton
                onClick={handleFilter}
                size="small"
                // endIcon={<SendIcon />}
                loading={loadingButton}
                loadingPosition="end"
                variant="contained"
                disabled={isApplyFilter}
                className='btn bg-vinnet-primary'
                >
                    <span>{buttonLabel ?? "APPLY"}</span>
            </LoadingButton>
            {/* <Button
                variant="contained"
                className="btn bg-primary"
                onClick={handleFilter}
                disabled={isApplyFilter}
                sx={{
                    whiteSpace: "nowrap"
                }}
            >
                <span>{buttonLabel ?? "APPLY"}</span>
            </Button> */}
        </div>
    )
}

export default SearchDatePickerFromTo;