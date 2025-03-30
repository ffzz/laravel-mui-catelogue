import ImagePlaceholder from '@/components/ImagePlaceholder';
import { Box } from '@mui/material';

interface ModalImageProps {
    imageUrl: string | null;
    altText: string;
}

/**
 * Component that displays either the content image or a placeholder
 * when no image is available
 */
const ModalImage = ({ imageUrl, altText }: ModalImageProps) => {
    return (
        <Box sx={{ width: '100%' }}>
            {imageUrl ? (
                <Box
                    component="img"
                    src={imageUrl}
                    alt={altText}
                    sx={{
                        width: '100%',
                        height: 'auto',
                        maxHeight: '300px',
                        objectFit: 'cover',
                        borderRadius: 1,
                    }}
                />
            ) : (
                <ImagePlaceholder />
            )}
        </Box>
    );
};

export default ModalImage;
