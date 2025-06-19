import type { SxProps } from '@mui/material/styles';
import React from "react";
import { Card, CardContent, CardHeader } from "@mui/material";
import StackedBarChartPercentageComponent, { StackedBarChartData } from '../../../components/Charts/StackedBarChartPercentageComponent';

interface StackedBarChartPercentageWidgetProps {
    title: string,
    data: StackedBarChartData[]
}

const StackedBarChartPercentageWidget: React.FC<StackedBarChartPercentageWidgetProps> = ({title, data}) => {
    return (
        <Card sx={{
            width: '100%',
            boxShadow: 'rgb(145 158 171 / 30%) 0px 0px 2px 0px,rgb(145 158 171 / 12%) 0px 12px 24px -4px!important',
            borderRadius: '10px'
        }}>
            <CardHeader 
                title={title} 
                sx={{color: 'var(--font-fifth)'}}
            />
            <CardContent sx={{ 
                display: 'flex', 
                justifyContent: 'center', 
                alignItems: 'center', 
                height: '100%',
                padding: 0,
                margin: 0
            }}>
                <StackedBarChartPercentageComponent legend_title={title} data={data} />
            </CardContent>
        </Card>
    );
}

export default StackedBarChartPercentageWidget;