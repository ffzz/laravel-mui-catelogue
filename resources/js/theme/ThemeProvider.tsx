import { CssBaseline, ThemeProvider as MuiThemeProvider } from '@mui/material';
import { ReactNode } from 'react';
import theme from './theme';

interface ThemeProviderProps {
    children: ReactNode;
}

const ThemeProvider = ({ children }: ThemeProviderProps) => {
    return (
        <MuiThemeProvider theme={theme}>
            <CssBaseline />
            {children}
        </MuiThemeProvider>
    );
};

export default ThemeProvider;
