import { Box, CircularProgress, Typography } from '@mui/material';

const LoadingContent = () => {
    return (
        <Box
            display="flex"
            justifyContent="center"
            alignItems="center"
            sx={{
                py: 10,
                bgcolor: 'background.paper',
                borderRadius: 2,
            }}
        >
            <CircularProgress color="primary" />
            <Typography variant="body1" sx={{ ml: 2 }}>
                Loading content...
            </Typography>
        </Box>
    );
};

export default LoadingContent;
