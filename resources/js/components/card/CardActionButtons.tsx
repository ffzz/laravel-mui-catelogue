import { ContentItem } from '@/types/content';
import LoginIcon from '@mui/icons-material/Login';
import VisibilityIcon from '@mui/icons-material/Visibility';
import { Button, Stack } from '@mui/material';
import { useState } from 'react';
import ContentModal from '../ContentModal';

interface CardActionButtonsProps {
    contentUrl: string;
    content: ContentItem;
}

const CardActionButtons = ({ contentUrl, content }: CardActionButtonsProps) => {
    const [isModalOpen, setIsModalOpen] = useState(false);

    const handleView = () => {
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
    };

    return (
        <>
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

            <ContentModal content={content} open={isModalOpen} onClose={handleCloseModal} />
        </>
    );
};

export default CardActionButtons;
