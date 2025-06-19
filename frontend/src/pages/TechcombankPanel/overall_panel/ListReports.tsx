import { Card, CardHeader, Divider } from "@mui/material";
import { Survey } from "../Panellist";
import ListReportsTable from "../tables/ListReportsTable";

interface ListReportProps {
    surveys: Survey[];
};

const ListReport: React.FC<ListReportProps> = ({surveys}) => {
    return (
        <>
            <ListReportsTable surveys={surveys} />
        </>
    )
}

export default ListReport;