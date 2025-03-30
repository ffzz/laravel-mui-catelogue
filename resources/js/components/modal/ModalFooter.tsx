import LaunchIcon from '@mui/icons-material/Launch';
import { Button, DialogActions } from '@mui/material';

interface ModalFooterProps {
    contentUrl: string | null;
    onClose: () => void;
}

/**
 * Component that renders the footer actions for the content modal
 */
const ModalFooter = ({ contentUrl, onClose }: ModalFooterProps) => {
    return (
        <DialogActions sx={{ p: 2, pt: 1 }}>
            <Button onClick={onClose} variant="outlined">
                Close
            </Button>
            {contentUrl && (
                <Button variant="contained" color="primary" href={contentUrl} target="_blank" rel="noopener noreferrer" endIcon={<LaunchIcon />}>
                    Join
                </Button>
            )}
        </DialogActions>
    );
};

export default ModalFooter;
