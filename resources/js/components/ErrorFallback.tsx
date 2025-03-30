// Create a component to render error details
const ErrorFallback = ({ error, resetErrorBoundary }: { error: Error; resetErrorBoundary: () => void }) => {
    return (
        <div
            style={{
                padding: '20px',
                border: '1px solid #f44336',
                borderRadius: '8px',
                margin: '20px',
                backgroundColor: '#ffebee',
            }}
        >
            <h2>An error occurred:</h2>
            <pre style={{ whiteSpace: 'pre-wrap' }}>{error.message}</pre>
            <button
                onClick={resetErrorBoundary}
                style={{
                    padding: '8px 16px',
                    backgroundColor: '#f44336',
                    color: 'white',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: 'pointer',
                    marginTop: '10px',
                }}
            >
                Try again
            </button>
        </div>
    );
};

export default ErrorFallback;
