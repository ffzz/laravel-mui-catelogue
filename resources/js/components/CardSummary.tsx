import { createSafeHtml } from '@/lib/utils';
import { Box, Typography } from '@mui/material';
const CardSummary = ({ contentSummary }: { contentSummary: string }) => {
    return (
        <Box
            sx={{
                maxHeight: '4.5em',
                overflow: 'hidden',
                my: 1,
            }}
        >
            {contentSummary ? (
                <Box
                    component="div"
                    sx={{
                        color: 'text.secondary',
                        fontSize: '0.875rem',
                        '& a': { color: 'primary.main' },
                        '& p': { margin: 0 },
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        display: '-webkit-box',
                        WebkitLineClamp: 3,
                        WebkitBoxOrient: 'vertical',
                    }}
                    dangerouslySetInnerHTML={createSafeHtml(contentSummary)}
                />
            ) : (
                <Typography variant="body2" color="text.secondary">
                    No description available.
                </Typography>
            )}
        </Box>
    );
};

export default CardSummary;
