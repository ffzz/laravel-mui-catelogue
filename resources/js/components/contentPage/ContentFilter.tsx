import { ContentTypeColor } from '@/types/content';
import CachedIcon from '@mui/icons-material/Cached';
import FilterAltIcon from '@mui/icons-material/FilterAlt';
import MemoryIcon from '@mui/icons-material/Memory';
import {
    Box,
    Button,
    Chip,
    CircularProgress,
    FormControl,
    InputLabel,
    MenuItem,
    Select,
    SelectChangeEvent,
    Stack,
    Typography,
    useTheme,
} from '@mui/material';
const ContentFilter: React.FC<{
    noCache: boolean;
    setNoCache: (noCache: boolean) => void;
    handleRefreshCache: () => void;
    setPage: (page: number) => void;
    refreshing: boolean;
    contentTypeOptions: { value: string; label: string }[];
    handleContentTypeChange: (event: SelectChangeEvent<string>) => void;
    contentType: string;
    contentTypeColor: ContentTypeColor;
}> = ({ noCache, setNoCache, handleRefreshCache, contentTypeOptions, handleContentTypeChange, contentType, contentTypeColor, refreshing }) => {
    const theme = useTheme();

    return (
        <Box mb={4}>
            <Typography variant="h4" component="h2" gutterBottom>
                Content Catalogue
                <Chip
                    label={contentType || 'All Types'}
                    size="small"
                    color={contentTypeColor || 'primary'}
                    sx={{ fontWeight: 500, position: 'relative', top: '-10px', right: '-10px' }} // Positioned at the top right corner
                />
            </Typography>

            <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2} alignItems={{ xs: 'stretch', sm: 'center' }} sx={{ mb: 4 }}>
                <FormControl
                    sx={{
                        minWidth: { xs: '100%', sm: 250 },
                        bgcolor: 'background.paper',
                        borderRadius: 1,
                        '& .MuiOutlinedInput-root': {
                            '& fieldset': {
                                borderColor: theme.palette.divider,
                            },
                            '&:hover fieldset': {
                                borderColor: theme.palette.primary.main,
                            },
                        },
                    }}
                    size="small"
                >
                    <InputLabel id="content-type-label" sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                        <FilterAltIcon fontSize="small" />
                        <span>Content Type</span>
                    </InputLabel>
                    <Select labelId="content-type-label" value={contentType} label="Content Type" onChange={handleContentTypeChange}>
                        {contentTypeOptions?.map((type) => (
                            <MenuItem key={type.value} value={type.value}>
                                {type.label}
                            </MenuItem>
                        ))}
                    </Select>
                </FormControl>

                <Button
                    variant="outlined"
                    color={noCache ? 'secondary' : 'primary'}
                    onClick={() => setNoCache(!noCache)}
                    startIcon={<MemoryIcon />}
                    size="small"
                    sx={{
                        textTransform: 'none',
                        minWidth: { xs: '100%', sm: 130 },
                        bgcolor: 'background.paper',
                    }}
                >
                    {noCache ? 'Use Cache' : 'Bypass Cache'}
                </Button>

                <Button
                    variant="contained"
                    color="secondary"
                    onClick={handleRefreshCache}
                    disabled={refreshing}
                    startIcon={refreshing ? <CircularProgress size={16} color="inherit" /> : <CachedIcon />}
                    size="small"
                    sx={{
                        textTransform: 'none',
                        minWidth: { xs: '100%', sm: 150 },
                        boxShadow: 1,
                    }}
                >
                    {refreshing ? 'Refreshing...' : 'Refresh Cache'}
                </Button>
            </Stack>
        </Box>
    );
};

export default ContentFilter;
