import * as React from 'react';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardActions from '@mui/material/CardActions';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Divider from '@mui/material/Divider';
import { alpha, useTheme } from '@mui/material/styles';
import type { SxProps } from '@mui/material/styles';
import { ArrowClockwise as ArrowClockwiseIcon } from '@phosphor-icons/react/dist/ssr/ArrowClockwise';
import { ArrowRight as ArrowRightIcon } from '@phosphor-icons/react/dist/ssr/ArrowRight';

import StackedBarChartComponent, { ChartData } from '../../../components/Charts/StackedBarChartComponent';


export interface RecruitmentProps {
  title: string;
  data: ChartData[]; 
  sx?: SxProps;
}

const RecruitmentWidget:React.FC<RecruitmentProps> = ({title, data}) => {
    
    return (
      <Card className='widget-container'>
        <CardHeader
          sx={{color: 'var(--font-fifth)'}}
          action={
            <Button color="inherit" size="small" startIcon={<ArrowClockwiseIcon fontSize="var(--icon-fontSize-md)" />}>
              Sync
            </Button>
          }
          title={title}
        />
        <CardContent>
            <StackedBarChartComponent data={data}  />
        </CardContent>
        <Divider />
        <CardActions sx={{ justifyContent: 'flex-end' }}>
          <Button color="inherit" endIcon={<ArrowRightIcon fontSize="var(--icon-fontSize-md)" />} size="small">
            Overview
          </Button>
        </CardActions>
      </Card>
    );
}

export default RecruitmentWidget;