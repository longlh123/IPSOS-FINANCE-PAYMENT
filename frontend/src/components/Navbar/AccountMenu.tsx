import * as React from 'react';
import Box from '@mui/material/Box';
import Avatar from '@mui/material/Avatar';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';
import ListItemIcon from '@mui/material/ListItemIcon';
import Divider from '@mui/material/Divider';
import IconButton from '@mui/material/IconButton';
import Typography from '@mui/material/Typography';
import Tooltip from '@mui/material/Tooltip';
import Logout from '@mui/icons-material/Logout';
import AccountCircle from '@mui/icons-material/AccountCircle';
import { useAuth } from '../../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { getStoredUser } from '../../utils/authStorage';

export default function AccountMenu() {
    const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
    const open = Boolean(anchorEl);
    const navigate = useNavigate();
    
    const { logout } = useAuth();

    const user = getStoredUser<any>();
    
    const handleClick = (event: React.MouseEvent<HTMLElement>) => {
        setAnchorEl(event.currentTarget);
    };
   
    const handleClose = () => {
        setAnchorEl(null);
    };

    const handleLogout = () => {
        logout();
    };

    const handleOpenAccountInfo = () => {
        navigate('/account-info');
    };
    
    return (
        <React.Fragment>
            <Box sx={{ display: 'flex', alignItems: 'center', textAlign: 'center' }}>
            <Typography sx={{ minWidth: 100 }}>
            {user ? `Hello, ${user.first_name}` : 'Guest'}
            </Typography>
            <Tooltip title="Account settings">
            <IconButton
                onClick={handleClick}
                size="small"
                sx={{ ml: 2 }}
                aria-controls={open ? 'account-menu' : undefined}
                aria-haspopup="true"
                aria-expanded={open ? 'true' : undefined}
            >
                <Avatar sx={{ width: 32, height: 32 }}>{user?.first_name?.charAt(0)}</Avatar>
            </IconButton>
            </Tooltip>
        </Box>

        <Menu
            anchorEl={anchorEl}
            id="account-menu"
            open={open}
            onClose={handleClose}
            onClick={handleClose}
        >
            <MenuItem onClick={handleOpenAccountInfo}>
                <ListItemIcon>
                    <AccountCircle fontSize="small" />
                </ListItemIcon>
                Thông tin tài khoản
            </MenuItem>

            <Divider />

            <MenuItem onClick={handleLogout}>
                <ListItemIcon>
                    <Logout fontSize="small" />
                </ListItemIcon>
                Logout
            </MenuItem>
        </Menu>

        {/* Conditionally render features based on role */}
        {/* {user?.role === 'Admin' && (
            <Button variant="contained"> 
                Admin Panel
            </Button>
        )} */}
        </React.Fragment>
    );
}
