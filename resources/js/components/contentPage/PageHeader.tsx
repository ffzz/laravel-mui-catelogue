import { AppBar, Typography, useTheme } from '@mui/material';

const PageHeader = ({ title }: { title?: string }) => {
    const theme = useTheme();

    return (
        <AppBar
            position="static"
            color="default"
            elevation={0}
            sx={{
                borderBottom: `1px solid ${theme.palette.divider}`,
                backgroundColor: theme.palette.background.paper,
                padding: theme.spacing(2),
                minHeight: '70px',
                display: 'flex',
                alignItems: 'center',
                marginLeft: 'auto',
                marginRight: 'auto',
            }}
        >
            {title && (
                <Typography variant="h1" sx={{ fontSize: '2rem', fontWeight: 500 }}>
                    {title}
                </Typography>
            )}
        </AppBar>
    );
};

export default PageHeader;
