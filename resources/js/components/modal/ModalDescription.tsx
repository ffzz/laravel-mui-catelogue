import { Box, Typography } from '@mui/material';

interface ModalDescriptionProps {
    summary: string | null;
}

/**
 * Component that displays the content description with HTML rendering support
 */
const ModalDescription = ({ summary }: ModalDescriptionProps) => {
    // Create safe HTML markup from summary
    const createMarkup = (html: string) => {
        return { __html: html };
    };

    return (
        <Box>
            <Typography variant="h6" gutterBottom>
                Description
            </Typography>
            {summary ? (
                <Box sx={{ typography: 'body1', color: 'text.secondary' }} dangerouslySetInnerHTML={createMarkup(summary)} />
            ) : (
                <Typography variant="body2" color="text.secondary">
                    No description available.
                </Typography>
            )}
        </Box>
    );
};

export default ModalDescription;
