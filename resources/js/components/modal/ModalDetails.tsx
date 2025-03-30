import { formattedDate } from '@/lib/utils';
import { ContentItem } from '@/types/content';
import { Box, Chip, Typography } from '@mui/material';

interface TagItem {
    id: number;
    name: string;
}

interface ModalDetailsProps {
    content: ContentItem;
}

/**
 * Component that displays the content details including tags, update date,
 * duration, cost and category
 */
const ModalDetails = ({ content }: ModalDetailsProps) => {
    const updatedDate = content.formatted_date?.modified || formattedDate(content.timeModified);

    // Ensure value is a string, convert objects to JSON if needed
    const ensureString = (value: unknown): string => {
        if (value === null || value === undefined) return '';
        if (typeof value === 'string') return value;
        if (typeof value === 'number' || typeof value === 'boolean') return String(value);
        return JSON.stringify(value);
    };

    // Get category name
    const getCategoryName = (): string => {
        if (!content.category) return '';
        if (typeof content.category === 'string') return content.category;
        if (typeof content.category === 'object' && content.category !== null) {
            return 'name' in content.category && typeof content.category.name === 'string' ? content.category.name : ensureString(content.category);
        }
        return ensureString(content.category);
    };

    return (
        <Box>
            <Typography variant="h6" gutterBottom>
                Details
            </Typography>
            <Box sx={{ mb: 2 }}>
                <Chip label={content.contentType} color="primary" sx={{ mr: 1, mb: 1 }} />
                {content.badge && <Chip label={content.badge} color="secondary" sx={{ mr: 1, mb: 1 }} />}
                {content.tags &&
                    content.tags.map((tag: string | TagItem, index) => (
                        <Chip key={index} label={typeof tag === 'string' ? tag : tag.name} variant="outlined" size="small" sx={{ mr: 1, mb: 1 }} />
                    ))}
            </Box>
            <Typography variant="body2" gutterBottom>
                <strong>Last Updated:</strong> {updatedDate}
            </Typography>
            {content.duration && (
                <Typography variant="body2" gutterBottom>
                    <strong>Duration:</strong> {content.duration}
                </Typography>
            )}
            {content.cost > 0 && (
                <Typography variant="body2" gutterBottom>
                    <strong>Cost:</strong> ${content.cost}
                </Typography>
            )}
            {content.category && (
                <Typography variant="body2" gutterBottom>
                    <strong>Category:</strong> {getCategoryName()}
                </Typography>
            )}
        </Box>
    );
};

export default ModalDetails;
