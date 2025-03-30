import { Box, Typography } from '@mui/material';

const ImagePlaceholder = () => {
    const getPlaceholderImage = () =>
        'https://staging.acornlms.com/local/acorn_shared/resources/assets/customfile.php/21340/course/overviewfiles/0/Teams%20Background_32.jpg';

    return (
        <Box
            sx={{
                position: 'relative',
                height: 140,
                backgroundColor: 'grey.200',
                backgroundImage: 'linear-gradient(to right bottom, #f5f5f5, #e0e0e0)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                overflow: 'hidden',
            }}
        >
            <img
                src={getPlaceholderImage()}
                alt="Placeholder"
                style={{
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                    opacity: 0.6,
                }}
            />
            <Typography
                variant="caption"
                color="text.secondary"
                sx={{
                    position: 'absolute',
                    backgroundColor: 'rgba(255, 255, 255, 0.7)',
                    padding: '2px 8px',
                    borderRadius: 1,
                }}
            >
                No image available
            </Typography>
        </Box>
    );
};

export default ImagePlaceholder;
