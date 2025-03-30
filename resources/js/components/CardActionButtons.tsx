import LoginIcon from '@mui/icons-material/Login';
import VisibilityIcon from '@mui/icons-material/Visibility';
import { Button, Stack } from '@mui/material';

const CardActionButtons = ({ contentUrl, contentId }: { contentUrl: string; contentId: string | number }) => {
    const handleView = () => {
        console.log('view', contentId);
    };

    return (
        <Stack direction="row" spacing={1} width="100%">
            <Button onClick={handleView} size="small" variant="outlined" startIcon={<VisibilityIcon />} sx={{ flex: 1 }}>
                View
            </Button>
            <Button
                size="small"
                variant="contained"
                color="primary"
                startIcon={<LoginIcon />}
                href={contentUrl || '#'}
                target="_blank"
                rel="noopener noreferrer"
                sx={{ flex: 1 }}
            >
                Join
            </Button>
        </Stack>
    );
};

export default CardActionButtons;
