import React from 'react';
import { Container } from '@mui/material';
import MarketTrendWidget from './widgets/MarketTrendWidget';

const MarketTrend: React.FC = () => {
  return (
    <Container maxWidth="md" sx={{ my: 4 }}>
      <MarketTrendWidget />
    </Container>
  );
};

export default MarketTrend;
