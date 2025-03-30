import StatusColourDot from '@/components/card/StatusColourDot';
import { Typography } from '@mui/material';

const CardTitle = ({ displayName, updatedDate, contentStatus }: { displayName: string; updatedDate: string; contentStatus: string }) => {
    return (
        <>
            <Typography
                gutterBottom
                variant="h6"
                component="h2"
                sx={{
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    display: '-webkit-box',
                    WebkitLineClamp: 2,
                    WebkitBoxOrient: 'vertical',
                    lineHeight: 1,
                    flex: 1,
                }}
            >
                <StatusColourDot contentStatus={contentStatus} />
                {displayName}
            </Typography>
            <Typography variant="caption" color="text.secondary" display="block">
                Updated at: {updatedDate}
            </Typography>
        </>
    );
};

export default CardTitle;
