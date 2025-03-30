import { ModalCustomFields, ModalDescription, ModalDetails, ModalFooter, ModalHeader, ModalImage } from '@/components/modal';
import { removeSpecialSymbols } from '@/lib/utils';
import { ContentItem } from '@/types/content';
import { Dialog, DialogContent, Divider, Stack, useMediaQuery, useTheme } from '@mui/material';

interface ContentModalProps {
    content: ContentItem | null;
    open: boolean;
    onClose: () => void;
}

/**
 * Modal component that displays detailed information about a content item
 */
const ContentModal = ({ content, open, onClose }: ContentModalProps) => {
    const theme = useTheme();
    const fullScreen = useMediaQuery(theme.breakpoints.down('md'));

    if (!content) return null;

    const displayName = removeSpecialSymbols(content.fullname);

    return (
        <Dialog open={open} onClose={onClose} fullScreen={fullScreen} maxWidth="md" fullWidth scroll="paper" aria-labelledby="content-detail-dialog">
            <ModalHeader content={content} onClose={onClose} />
            <Divider />
            <DialogContent>
                <Stack spacing={3}>
                    <Stack direction={{ xs: 'column', md: 'row' }} spacing={3}>
                        {/* Left column - Image */}
                        <Stack sx={{ width: { xs: '100%', md: '50%' } }}>
                            <ModalImage imageUrl={content.image} altText={displayName} />
                        </Stack>

                        {/* Right column - Details */}
                        <Stack sx={{ width: { xs: '100%', md: '50%' } }}>
                            <ModalDetails content={content} />
                        </Stack>
                    </Stack>

                    {/* Description section */}
                    <ModalDescription summary={content.summary} />

                    {/* Additional information section */}
                    <ModalCustomFields customFields={Array.isArray(content.customfields) ? content.customfields : []} />
                </Stack>
            </DialogContent>
            <ModalFooter contentUrl={content.url} onClose={onClose} />
        </Dialog>
    );
};

export default ContentModal;
