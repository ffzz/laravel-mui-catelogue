import { Container, Typography } from '@mui/material';

const Intro = () => {
    return (
        <Container>
            <Typography variant="h5" color="text.primary" gutterBottom>
                Discovery courses
            </Typography>
            <Typography variant="body1" color="text.secondary">
                Explore a wide range of learning content, available anytime and anywhere to help you enhance your skills. Choose any content type
                below to start your learning journey.
            </Typography>
        </Container>
    );
};

export default Intro;
