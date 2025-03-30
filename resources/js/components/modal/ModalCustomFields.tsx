import { Box, Stack, Typography } from '@mui/material';

interface CustomField {
    name: string;
    data: string;
}

interface ModalCustomFieldsProps {
    customFields: CustomField[] | null;
}

/**
 * Component that displays custom fields of content in a responsive grid
 */
const ModalCustomFields = ({ customFields }: ModalCustomFieldsProps) => {
    // Ensure value is a string, convert objects to JSON if needed
    const ensureString = (value: unknown): string => {
        if (value === null || value === undefined) return '';
        if (typeof value === 'string') return value;
        if (typeof value === 'number' || typeof value === 'boolean') return String(value);
        return JSON.stringify(value);
    };

    if (!Array.isArray(customFields) || customFields.length === 0) {
        return null;
    }

    return (
        <Box>
            <Typography variant="h6" gutterBottom>
                Additional Information
            </Typography>
            <Stack direction="row" flexWrap="wrap" gap={2}>
                {customFields.map((field: CustomField, index) => (
                    <Box key={index} sx={{ minWidth: { xs: '100%', sm: '45%', md: '30%' } }}>
                        <Typography variant="body2">
                            <strong>{field.name}:</strong> {ensureString(field.data)}
                        </Typography>
                    </Box>
                ))}
            </Stack>
        </Box>
    );
};

export default ModalCustomFields;
