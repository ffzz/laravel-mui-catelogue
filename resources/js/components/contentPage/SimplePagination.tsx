import { Box, Button, Chip, FormControl, MenuItem, Select, SelectChangeEvent, Typography, useTheme } from '@mui/material';
import React from 'react';

interface SimplePaginationProps {
    page: number;
    setPage: React.Dispatch<React.SetStateAction<number>>;
    perPage: number;
    setPerPage: React.Dispatch<React.SetStateAction<number>>;
    metadata: {
        currentPage: number;
        totalPages: number;
    };
}

const SimplePagination: React.FC<SimplePaginationProps> = ({ page, setPage, perPage, setPerPage, metadata }) => {
    const theme = useTheme();

    const handlePerPageChange = (event: SelectChangeEvent<number>) => {
        const newPerPage = Number(event.target.value);
        // Calculate the position of the first item currently being viewed
        const currentFirstItem = (page - 1) * perPage + 1;
        // Calculate which page should be displayed with the new items per page setting
        const newPage = Math.max(1, Math.ceil(currentFirstItem / newPerPage));

        setPerPage(newPerPage);
        setPage(newPage);
    };

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
                flexWrap: 'wrap',
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
                Page <Chip label={metadata.currentPage} color="primary" size="small" sx={{ mx: 1, fontWeight: 600 }} />
                {/* of{' '}
                <Chip label={metadata.totalPages} color="default" size="small" sx={{ ml: 1, fontWeight: 600 }} /> */}
            </Typography>
            <Button
                variant="outlined"
                // disabled={page >= metadata.totalPages}
                onClick={() => setPage((prev: number) => prev + 1)}
                size="small"
                sx={{ minWidth: 100 }}
            >
                Next
            </Button>

            <Box sx={{ display: 'flex', alignItems: 'center', ml: { xs: 0, md: 2 } }}>
                <Typography variant="body2" sx={{ mr: 1 }}>
                    Items per page:
                </Typography>
                <FormControl size="small" sx={{ minWidth: 80 }}>
                    <Select value={perPage} onChange={handlePerPageChange} displayEmpty>
                        <MenuItem value={10}>10</MenuItem>
                        <MenuItem value={20}>20</MenuItem>
                        <MenuItem value={50}>50</MenuItem>
                    </Select>
                </FormControl>
            </Box>
        </Box>
    );
};

export default SimplePagination;
