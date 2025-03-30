import ItemCard from '@/components/card/ItemCard';
import { ContentItem } from '@/types/content';
import { Box } from '@mui/material';

const ContentGrid = ({ contents }: { contents: ContentItem[] }) => {
    return (
        <Box
            display="grid"
            gridTemplateColumns={{
                xs: 'repeat(1, 1fr)',
                sm: 'repeat(2, 1fr)',
                md: 'repeat(3, 1fr)',
                lg: 'repeat(4, 1fr)',
            }}
            gap={3}
        >
            {contents.map((content: ContentItem) => (
                <ItemCard key={content.id} content={content} />
            ))}
        </Box>
    );
};

export default ContentGrid;
