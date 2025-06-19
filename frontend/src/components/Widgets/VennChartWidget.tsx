import React from "react";
import { Card, CardContent, CardHeader } from "@mui/material";
import VennChartComponent from "../Charts/VennChartComponent";

interface VennChartData {
    sets: string[]; 
    size: number
}

interface VennChartWidgetProps {
    title: string,
    legend_title?: string,
    data: VennChartData[],
    sorted: boolean,
    sortedList?: string[], 
}

const VennChartWidget: React.FC<VennChartWidgetProps> = ({title, legend_title, data, sorted, sortedList}) => {
    return (
        <Card className='widget-container'>
            <CardHeader 
                title={title} 
                sx={{
                    color: 'var(--font-secondary)'
                }}
            />
            <CardContent>
                <VennChartComponent legend_title={legend_title} data={data} sorted={sorted} sortedList={sortedList} />
            </CardContent>
        </Card>
    );
}

export default VennChartWidget;