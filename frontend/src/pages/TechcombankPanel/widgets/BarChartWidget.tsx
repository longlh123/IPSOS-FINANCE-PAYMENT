import React from "react";
import BarChartComponent from "../../../components/Charts/BarChartComponent";
import { Card, CardContent, CardHeader } from "@mui/material";
import { useTranslation } from "react-i18next";

interface Data {
    name: string,
    value: number,
}

interface WidgetPieChartProps {
    title: string,
    legend_title?: string,
    data: Data[],
    sorted: boolean,
    sortedList?: string[], 
    translate?: boolean; //to determine if translation is needed.
}

const BarChartWidget: React.FC<WidgetPieChartProps> = ({title, legend_title, data, sorted, sortedList, translate = false}) => {
    const { t } = useTranslation();

    const translateData = translate ? data.map(item => ({
        ...item,
        name: t(`dashboard.barchart.categories.${item.name}`)
    }))
    : data;

    return (
        <Card className='widget-container'>
            <CardHeader 
                title={title} 
                sx={{
                    color: 'var(--font-secondary)'
                }}
            />
            <CardContent>
                <BarChartComponent legend_title={legend_title} data={translateData} sorted={sorted} sortedList={sortedList} />
            </CardContent>
        </Card>
    );
}

export default BarChartWidget;