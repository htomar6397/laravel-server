import { useCallback } from 'react';

interface ErrorReport {
    id: string;
    message: string;
    stack?: string;
    componentStack?: string;
    timestamp: string;
    userAgent: string;
    url: string;
    additionalData?: Record<string, unknown>;
}

interface ErrorInfo {
    componentStack?: string;
}

export const useErrorReporting = () => {
    const reportError = useCallback(async (error: Error, errorInfo?: ErrorInfo, additionalData?: Record<string, unknown>) => {
        const errorReport: ErrorReport = {
            id: `ERR_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            message: error.message,
            stack: error.stack,
            componentStack: errorInfo?.componentStack,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
            additionalData,
        };

        // Log to console in development
        if (process.env.NODE_ENV === 'development') {
            console.error('Error Report:', errorReport);
        }

        // In production, send to error reporting service
        try {
            // Example: Send to your error reporting service
            // await fetch('/api/errors', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(errorReport),
            // });

            // For now, store in localStorage for debugging
            const existingErrors = JSON.parse(localStorage.getItem('errorReports') || '[]');
            existingErrors.push(errorReport);
            localStorage.setItem('errorReports', JSON.stringify(existingErrors.slice(-10))); // Keep last 10 errors

        } catch (reportError) {
            console.error('Failed to report error:', reportError);
        }

        return errorReport.id;
    }, []);

    const getErrorReports = useCallback(() => {
        try {
            return JSON.parse(localStorage.getItem('errorReports') || '[]');
        } catch {
            return [];
        }
    }, []);

    const clearErrorReports = useCallback(() => {
        localStorage.removeItem('errorReports');
    }, []);

    return {
        reportError,
        getErrorReports,
        clearErrorReports,
    };
};