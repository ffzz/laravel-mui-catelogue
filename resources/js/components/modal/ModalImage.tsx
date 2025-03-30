import { Box, Typography } from '@mui/material';

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
                <Box
                    sx={{
                        width: '100%',
                        height: '200px',
                        backgroundColor: 'grey.200',
                        backgroundImage: 'linear-gradient(to right bottom, #f5f5f5, #e0e0e0)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        borderRadius: 1,
                    }}
                >
                    <Typography variant="body2" color="text.secondary">
                        No image available
                    </Typography>
                </Box>
            )}
        </Box>
    );
};

export default ModalImage;
