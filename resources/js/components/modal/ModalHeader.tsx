import { removeSpecialSymbols } from '@/lib/utils';
import { ContentItem } from '@/types/content';
import CloseIcon from '@mui/icons-material/Close';
import { Box, Chip, DialogTitle, IconButton, Typography } from '@mui/material';

interface ModalHeaderProps {
    content: ContentItem;
    onClose: () => void;
}

/**
 * Component that renders the header section of the content modal with title and close button
 */
const ModalHeader = ({ content, onClose }: ModalHeaderProps) => {
    const displayName = removeSpecialSymbols(content.fullname);

    // Determine status color based on contentStatus
    const getStatusColor = (status: string) => {
        if (status === 'Active' || status === 'active') return 'success';
        if (status === 'Pending' || status === 'pending') return 'warning';
        return 'error';
    };

    return (
        <DialogTitle
            id="content-detail-dialog"
            sx={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                pb: 1,
            }}
        >
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <Typography variant="h5" component="div">
                    {displayName}
                </Typography>
                <Chip size="small" label={content.contentStatus} color={getStatusColor(content.contentStatus)} />
            </Box>
            <IconButton edge="end" color="inherit" onClick={onClose} aria-label="close">
                <CloseIcon />
            </IconButton>
        </DialogTitle>
    );
};

export default ModalHeader;
