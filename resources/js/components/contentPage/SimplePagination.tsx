import { Box, Button, Chip, Typography, useTheme } from '@mui/material';
import React from 'react';

interface SimplePaginationProps {
    page: number;
    setPage: React.Dispatch<React.SetStateAction<number>>;
    metadata: {
        currentPage: number;
        totalPages: number;
    };
}

const SimplePagination: React.FC<SimplePaginationProps> = ({ page, setPage, metadata }) => {
    const theme = useTheme();

    return (
        <Box
            display="flex"
            justifyContent="center"
            alignItems="center"
            sx={{
                mt: 4,
                pt: 2,
                borderTop: `1px solid ${theme.palette.divider}`,
                gap: 2,
            }}
        >
            <Button
                variant="outlined"
                disabled={page <= 1}
                onClick={() => setPage((prev: number) => Math.max(prev - 1, 1))}
                size="small"
                sx={{ minWidth: 100 }}
            >
                Previous
            </Button>
            <Typography variant="body1" component="span" sx={{ display: 'flex', alignItems: 'center' }}>
                Page <Chip label={metadata.currentPage} color="primary" size="small" sx={{ mx: 1, fontWeight: 600 }} /> of{' '}
                <Chip label={metadata.totalPages} color="default" size="small" sx={{ ml: 1, fontWeight: 600 }} />
            </Typography>
            <Button
                variant="outlined"
                disabled={page >= metadata.totalPages}
                onClick={() => setPage((prev: number) => prev + 1)}
                size="small"
                sx={{ minWidth: 100 }}
            >
                Next
            </Button>
        </Box>
    );
};

export default SimplePagination;
