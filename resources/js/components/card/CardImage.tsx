import ImagePlaceholder from '@/components/ImagePlaceholder';
import { CardMedia } from '@mui/material';

const CardImage = ({ contentImage, displayName }: { contentImage: string; displayName: string }) => {
    return contentImage ? <CardMedia component="img" height="140" image={contentImage} alt={displayName} /> : <ImagePlaceholder />;
};

export default CardImage;
