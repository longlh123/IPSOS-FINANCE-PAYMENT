import React from 'react';
import { Box, Card, CardContent, Stack, Typography } from '@mui/material';
import ShoppingBagOutlinedIcon from '@mui/icons-material/ShoppingBagOutlined';
import PaletteOutlinedIcon from '@mui/icons-material/PaletteOutlined';
import StorefrontOutlinedIcon from '@mui/icons-material/StorefrontOutlined';
import PeopleAltOutlinedIcon from '@mui/icons-material/PeopleAltOutlined';
import HandymanOutlinedIcon from '@mui/icons-material/HandymanOutlined';
import CleaningServicesOutlinedIcon from '@mui/icons-material/CleaningServicesOutlined';
import AccessTimeOutlinedIcon from '@mui/icons-material/AccessTimeOutlined';
import { RankedFactor } from './umeDashboardData';
import { UME_COLORS } from './umeDashboardTheme';
import DashboardCardHeader from './DashboardCardHeader';

interface RankedFactorTableProps {
    title: string;
    data: RankedFactor[];
}

const ICONS_BY_LABEL: Record<string, React.ElementType> = {
    "Product experience": ShoppingBagOutlinedIcon,
    "Store Design": PaletteOutlinedIcon,
    "Store ambience": StorefrontOutlinedIcon,
    "Staff friendliness": PeopleAltOutlinedIcon,
    "Store condition & facilities": HandymanOutlinedIcon,
    "Store cleanliness": CleaningServicesOutlinedIcon,
    "Time & Process": AccessTimeOutlinedIcon
};

const RankedFactorTable: React.FC<RankedFactorTableProps> = ({ title, data }) => {
    const maxValue = 100;

    return (
        <Card sx={{ height: '100%', overflow: 'hidden' }}>
            <DashboardCardHeader title={title} />
            <CardContent>
                <Typography variant="caption" sx={{ color: UME_COLORS.mutedInk, display: 'block', mb: 1.5 }}>
                    Data show in %
                </Typography>
                <Stack spacing={1.5}>
                    {data.map((factor) => {
                        const Icon = ICONS_BY_LABEL[factor.label] ?? StorefrontOutlinedIcon;
                        const widthPct = Math.min(100, (factor.value / maxValue) * 100);

                        return (
                            <Stack key={factor.label} direction="row" spacing={1.5} alignItems="center">
                                <Icon sx={{ color: UME_COLORS.magnitude, fontSize: 20, flexShrink: 0 }} />

                                <Typography
                                    variant="body2"
                                    sx={{ color: UME_COLORS.ink, width: 190, flexShrink: 0 }}
                                >
                                    {factor.label}
                                </Typography>

                                <Typography
                                    variant="caption"
                                    sx={{ color: UME_COLORS.mutedInk, width: 20, flexShrink: 0, textAlign: 'right' }}
                                >
                                    #{factor.rank}
                                </Typography>

                                <Box sx={{ position: 'relative', flexGrow: 1, height: 22, backgroundColor: 'rgba(0,0,0,0.04)', borderRadius: '4px', overflow: 'hidden' }}>
                                    <Box
                                        sx={{
                                            width: `${widthPct}%`,
                                            height: '100%',
                                            backgroundColor: UME_COLORS.magnitude,
                                            borderRadius: '4px'
                                        }}
                                    />
                                    <Typography
                                        variant="caption"
                                        fontWeight={700}
                                        sx={{
                                            position: 'absolute',
                                            left: `calc(${widthPct}% + 8px)`,
                                            top: '50%',
                                            transform: 'translateY(-50%)',
                                            color: UME_COLORS.ink,
                                            whiteSpace: 'nowrap'
                                        }}
                                    >
                                        {factor.value}
                                    </Typography>
                                </Box>
                            </Stack>
                        );
                    })}
                </Stack>
            </CardContent>
        </Card>
    );
};

export default RankedFactorTable;
