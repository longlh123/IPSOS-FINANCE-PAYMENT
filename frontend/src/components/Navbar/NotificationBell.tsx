import { Badge, IconButton, Menu, MenuItem, Typography } from "@mui/material";
import { useState } from "react";
import NotificationIcon from '@mui/icons-material/Notifications';
import { useNotifications } from "../../hook/useNotifications";

const NotificationBell: React.FC = () => {
    const [ anchorEl, setAnchorEl ] = useState<null | HTMLElement>(null);
    
    const { countUnRead, notifications, fetchNotifications } = useNotifications();

    const open = Boolean(anchorEl);

    const handleOpen = async (event: React.MouseEvent<HTMLElement>) => {
        setAnchorEl(event.currentTarget);

        await fetchNotifications();
    }

    const handleClose = () => {
        setAnchorEl(null);
    }

    return (
        <>
            <IconButton
                onClick={handleOpen}
            >
                <Badge
                    badgeContent={countUnRead}
                    color="error"
                >
                    <NotificationIcon />
                </Badge>
            </IconButton>

            <Menu
                anchorEl={anchorEl}
                open={open}
                onClose={handleClose}
            >
                {notifications.map((n: any) => (
                    <MenuItem
                        key={n.id}
                    >
                        <Typography variant="body2">
                            {n.message}
                        </Typography>
                    </MenuItem>
                ))}
            </Menu>
        </>
    )
}

export default NotificationBell;