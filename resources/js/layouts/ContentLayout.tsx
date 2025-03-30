import PageHeader from '@/components/contentPage/PageHeader';
import { type ContentLayoutProps } from '@/types/content';
import { Head } from '@inertiajs/react';
import { Box, Container, Paper, useTheme } from '@mui/material';

function ContentLayout({ children, fullWidth = false, title }: ContentLayoutProps) {
    const theme = useTheme();

    return (
        <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
            <Head title={title || 'Content Catalogue'} />
            <PageHeader title={title} />
            <Container maxWidth={fullWidth ? false : 'lg'} sx={{ py: 3, flexGrow: 1 }}>
                <Paper
                    elevation={1}
                    sx={{
                        borderRadius: 2,
                        overflow: 'hidden',
                        backgroundColor: theme.palette.background.paper,
                        boxShadow: theme.shadows[1],
                        transition: 'box-shadow 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms',
                        '&:hover': {
                            boxShadow: theme.shadows[2],
                        },
                    }}
                >
                    {children}
                </Paper>
            </Container>
        </Box>
    );
}

export default ContentLayout;
