import { getStatusColor } from '@/lib/utils';
import { Box, Tooltip } from '@mui/material';

const StatusColourDot = ({ contentStatus }: { contentStatus: string }) => {
    const statusColor = getStatusColor(contentStatus);
    return (
        <Tooltip title={`Status: ${contentStatus}`}>
            <Box
                component="span"
                sx={{
                    width: 10,
                    height: 10,
                    borderRadius: '50%',
                    bgcolor: `${statusColor}.main`,
                    display: 'inline-block',
                    flexShrink: 0,
                    marginRight: 1,
                }}
            />
        </Tooltip>
    );
};

export default StatusColourDot;
