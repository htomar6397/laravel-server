import { AlertTriangle, RefreshCw } from 'lucide-react';
import React from 'react';

interface SimpleErrorBoundaryProps {
    children: React.ReactNode;
    fallback?: React.ReactNode;
    onRetry?: () => void;
}

/**
 * A simple error boundary component for wrapping individual components
 * Use this for smaller sections that might fail independently
 */
export const SimpleErrorBoundary: React.FC<SimpleErrorBoundaryProps> = ({
    children,
    fallback,
    onRetry
}) => {
    const [hasError, setHasError] = React.useState(false);
    const [error, setError] = React.useState<Error | null>(null);

    React.useEffect(() => {
        if (hasError) {
            // Reset error state when children change
            setHasError(false);
            setError(null);
        }
    }, [children, hasError]);

    const handleRetry = () => {
        setHasError(false);
        setError(null);
        onRetry?.();
    };

    if (hasError) {
        if (fallback) {
            return <>{fallback}</>;
        }

        return (
            <div className="rounded-lg border border-red-200 bg-red-50 p-4">
                <div className="flex items-center gap-3">
                    <AlertTriangle className="h-5 w-5 text-red-600 shrink-0" />
                    <div className="flex-1">
                        <h3 className="text-sm font-medium text-red-900">
                            Something went wrong
                        </h3>
                        <p className="text-sm text-red-700 mt-1">
                            {error?.message || 'An unexpected error occurred'}
                        </p>
                        <button
                            onClick={handleRetry}
                            className="mt-2 inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                        >
                            <RefreshCw className="h-3 w-3" />
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    try {
        return <>{children}</>;
    } catch (err) {
        setHasError(true);
        setError(err as Error);
        return null;
    }
};