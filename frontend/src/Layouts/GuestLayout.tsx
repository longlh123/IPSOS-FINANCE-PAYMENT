import React from 'react';
import CssBaseline from '@mui/material/CssBaseline';
import Container from '@mui/material/Container';

interface LayoutProps {
  children: React.ReactNode; // Placeholder for content within the layout
}

const GuestLayout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <React.Fragment>
      <CssBaseline />
      <Container fixed>
        {children}
      </Container>
    </React.Fragment>
  );
};

export default GuestLayout;