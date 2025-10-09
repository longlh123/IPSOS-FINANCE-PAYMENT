import { Box } from "@mui/material";

interface TabPanelProps {
    children?: React.ReactNode,
    value: string,
    index: string
}

const TabPanel: React.FC<TabPanelProps> = ({children, value, index}) => {
    
    return (
        <div role="tabpanel" hidden={value !== index}>
            {value === index && <Box sx={{ p: 2 }}>{children}</Box>}
        </div>
    )
};

export default TabPanel;