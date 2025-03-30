import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import { Alert, Typography } from '@mui/material';
const ErrorDisplay = ({ error }: { error: Error }) => {
    return (
        <Alert
            severity="error"
            variant="filled"
            icon={<ErrorOutlineIcon />}
            sx={{
                display: 'flex',
                alignItems: 'center',
                py: 2,
                boxShadow: 1,
            }}
        >
            <Typography fontWeight={500}>Error loading content: {error?.message ?? 'Unknown error'}</Typography>
        </Alert>
    );
};

export default ErrorDisplay;
