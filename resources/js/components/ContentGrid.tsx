import { ContentItem, ContentTypeColor } from '@/types/content';
import { Box, Button, Card, CardActions, CardContent, CardMedia, Chip, Fade, Typography } from '@mui/material';

const ContentGrid = ({ contents, contentTypeColor }: { contents: ContentItem[]; contentTypeColor: ContentTypeColor }) => {
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
                <Fade in={true} key={content.id} timeout={300}>
                    <Card
                        sx={{
                            display: 'flex',
                            flexDirection: 'column',
                            height: '100%',
                            transition: 'transform 0.3s, box-shadow 0.3s',
                            '&:hover': {
                                transform: 'translateY(-4px)',
                                boxShadow: 6,
                            },
                        }}
                    >
                        {content.image && <CardMedia component="img" height="140" image={content.image} alt={content.fullname} />}
                        <CardContent sx={{ flexGrow: 1 }}>
                            <Box display="flex" justifyContent="space-between" alignItems="center" mb={1}>
                                <Chip label={content.contentType} size="small" color={contentTypeColor} sx={{ fontWeight: 500 }} />
                            </Box>
                            <Typography gutterBottom variant="h6" component="h2">
                                {content.fullname}
                            </Typography>
                            <Typography variant="body2" color="text.secondary">
                                {content.summary || 'No description available.'}
                            </Typography>
                        </CardContent>
                        <CardActions>
                            <Button size="small" href={content.url || '#'} target="_blank" rel="noopener noreferrer">
                                View Content
                            </Button>
                        </CardActions>
                    </Card>
                </Fade>
            ))}
        </Box>
    );
};

export default ContentGrid;
