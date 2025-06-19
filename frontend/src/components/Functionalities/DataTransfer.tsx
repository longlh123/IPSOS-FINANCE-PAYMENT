import Button from '@mui/material/Button';
import Stack from '@mui/material/Stack';
import { Download as DownloadIcon } from '@phosphor-icons/react/dist/ssr/Download';
import { Upload as UploadIcon } from '@phosphor-icons/react/dist/ssr/Upload';

const DataTransfer = () => {

    return (
        <Stack spacing={2}>
            <Stack direction="row" spacing={2}>
                <Stack spacing={1} sx={{ flex: '1 1 auto' }}>
                    <Stack direction="row" spacing={1} sx={{ alignItems: 'center' }}>
                    <Button color="inherit" startIcon={<UploadIcon fontSize="var(--icon-fontSize-md)" />}>
                        Import
                    </Button>
                    <Button color="inherit" startIcon={<DownloadIcon fontSize="var(--icon-fontSize-md)" />}>
                        Export
                    </Button>
                    <Button color="inherit" startIcon={<DownloadIcon fontSize="var(--icon-fontSize-md)" />}>
                        Export Template
                    </Button>
                    </Stack>
                </Stack>
            </Stack>
        </Stack>
    )
}

export default DataTransfer