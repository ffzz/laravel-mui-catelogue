import { ContentItem, ContentTypeColor } from '@/types/content';

import { CardActionButtons, CardImage, CardSummary, CardTitle } from '@/components/card';
import { formattedDate, removeSpecialSymbols } from '@/lib/utils';
import { Card, CardActions, CardContent, Chip, Fade } from '@mui/material';

const ItemCard = ({ content, contentTypeColor }: { content: ContentItem; contentTypeColor: ContentTypeColor }) => {
    const displayName = removeSpecialSymbols(content?.fullname);

    const updatedDate = content.formatted_date?.modified || formattedDate(content?.timeModified);

    return (
        <Fade in={true} timeout={300} key={content.id}>
            <Card
                sx={{
                    display: 'flex',
                    flexDirection: 'column',
                    height: '100%',
                    maxHeight: 420,
                    transition: 'transform 0.3s, box-shadow 0.3s',
                    position: 'relative',
                    '&:hover': {
                        transform: 'translateY(-4px)',
                        boxShadow: 6,
                    },
                }}
            >
                <Chip
                    label={content.contentType}
                    size="small"
                    color={contentTypeColor}
                    sx={{ fontWeight: 500, position: 'absolute', top: '0.5rem', right: '0.5rem', zIndex: 2 }}
                />
                <CardImage contentImage={content.image || ''} displayName={displayName} />
                <CardContent sx={{ flexGrow: 1, overflow: 'hidden' }}>
                    <CardTitle displayName={displayName} updatedDate={updatedDate} contentStatus={content.contentStatus} />
                    <CardSummary contentSummary={content.summary} />
                </CardContent>
                <CardActions sx={{ padding: 2, paddingTop: 0 }}>
                    <CardActionButtons contentUrl={content.url} content={content} />
                </CardActions>
            </Card>
        </Fade>
    );
};

export default ItemCard;
