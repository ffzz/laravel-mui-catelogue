import SearchOffIcon from '@mui/icons-material/SearchOff';
import { Box, Typography } from '@mui/material';

const NoContentDisplay = () => {
    return (
        <Box
            display="flex"
            flexDirection="column"
            justifyContent="center"
            alignItems="center"
            sx={{
                py: 10,
                bgcolor: 'background.paper',
                borderRadius: 2,
                boxShadow: 1,
            }}
        >
            <SearchOffIcon sx={{ fontSize: 60, color: 'text.secondary', mb: 2 }} />
            <Typography variant="h6" sx={{ mb: 1 }}>
                No content found
            </Typography>
            <Typography variant="body1" color="text.secondary">
                Try changing your filter criteria or refreshing the cache
            </Typography>
        </Box>
    );
};

export default NoContentDisplay;
