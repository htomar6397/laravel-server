import { AlertTriangle, RefreshCw, Home, Bug } from 'lucide-react';
import type { ErrorInfo, ReactNode } from 'react';
import React, { Component } from 'react';

interface Props {
    children: ReactNode;
    fallback?: ReactNode;
    onError?: (error: Error, errorInfo: ErrorInfo) => void;
    showDetails?: boolean;
    className?: string;
}

interface State {
    hasError: boolean;
    error: Error | null;
    errorInfo: ErrorInfo | null;
    errorId: string | null;
}

class ErrorBoundary extends Component<Props, State> {
    constructor(props: Props) {
        super(props);

        this.state = {
            hasError: false,
            error: null,
            errorInfo: null,
            errorId: null,
        };
    }

    static getDerivedStateFromError(error: Error): Partial<State> {
        // Update state so the next render will show the fallback UI
        return {
            hasError: true,
            error,
            errorId: ErrorBoundary.generateErrorId(),
        };
    }

    componentDidCatch(error: Error, errorInfo: ErrorInfo) {
        // Log the error
        console.error('ErrorBoundary caught an error:', error, errorInfo);

        // Generate unique error ID for tracking
        const errorId = ErrorBoundary.generateErrorId();

        this.setState({
            error,
            errorInfo,
            errorId,
        });

        // Call custom error handler if provided
        if (this.props.onError) {
            this.props.onError(error, errorInfo);
        }

        // In production, you might want to send this to an error reporting service
        this.reportError(error, errorInfo, errorId);
    }

    private static generateErrorId(): string {
        return `ERR_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    private reportError(error: Error, errorInfo: ErrorInfo, errorId: string) {
        // In a real application, you would send this to your error reporting service
        // For now, we'll just log it with additional context
        const errorReport = {
            id: errorId,
            message: error.message,
            stack: error.stack,
            componentStack: errorInfo.componentStack,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
        };

        console.error('Error Report:', errorReport);

        // Example: Send to error reporting service
        // errorReportingService.captureException(error, {
        //     extra: errorReport,
        //     tags: { errorId }
        // });
    }

    private handleRetry = () => {
        this.setState({
            hasError: false,
            error: null,
            errorInfo: null,
            errorId: null,
        });
    };

    private handleGoHome = () => {
        window.location.href = '/admin/dashboard';
    };

    render() {
        if (this.state.hasError) {
            // Custom fallback UI
            if (this.props.fallback) {
                return this.props.fallback;
            }

            // Default professional error UI
            return (
                <div className={`min-h-screen bg-gray-50 flex items-center justify-center px-4 ${this.props.className || ''}`}>
                    <div className="max-w-2xl w-full">
                        <div className="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden">
                            {/* Header */}
                            <div className="bg-red-50 px-6 py-4 border-b border-red-200">
                                <div className="flex items-center gap-3">
                                    <div className="flex-shrink-0">
                                        <AlertTriangle className="h-8 w-8 text-red-600" />
                                    </div>
                                    <div>
                                        <h1 className="text-xl font-semibold text-red-900">
                                            Something went wrong
                                        </h1>
                                        <p className="text-sm text-red-700 mt-1">
                                            An unexpected error occurred while rendering this page
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Content */}
                            <div className="px-6 py-6">
                                <div className="space-y-6">
                                    {/* Error ID */}
                                    {this.state.errorId && (
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <div className="flex items-center gap-2 text-sm text-gray-600">
                                                <Bug className="h-4 w-4" />
                                                <span className="font-medium">Error ID:</span>
                                                <code className="bg-gray-200 px-2 py-1 rounded text-xs font-mono">
                                                    {this.state.errorId}
                                                </code>
                                            </div>
                                        </div>
                                    )}

                                    {/* Error Message */}
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-900 mb-2">
                                            Error Details
                                        </h3>
                                        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                                            <p className="text-sm text-red-800 font-medium">
                                                {this.state.error?.message || 'Unknown error'}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Technical Details (only in development or when showDetails is true) */}
                                    {(this.props.showDetails || process.env.NODE_ENV === 'development') && this.state.errorInfo && (
                                        <details className="bg-gray-50 border border-gray-200 rounded-lg">
                                            <summary className="cursor-pointer px-4 py-3 text-sm font-medium text-gray-900 hover:bg-gray-100">
                                                Technical Details
                                            </summary>
                                            <div className="px-4 py-3 border-t border-gray-200">
                                                <pre className="text-xs text-gray-700 whitespace-pre-wrap overflow-auto max-h-40">
                                                    {this.state.error?.stack}
                                                    {'\n\nComponent Stack:\n'}
                                                    {this.state.errorInfo.componentStack}
                                                </pre>
                                            </div>
                                        </details>
                                    )}

                                    {/* Actions */}
                                    <div className="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                                        <button
                                            onClick={this.handleRetry}
                                            className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                        >
                                            <RefreshCw className="h-4 w-4 mr-2" />
                                            Try Again
                                        </button>

                                        <button
                                            onClick={this.handleGoHome}
                                            className="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                        >
                                            <Home className="h-4 w-4 mr-2" />
                                            Go to Dashboard
                                        </button>
                                    </div>

                                    {/* Help Text */}
                                    <div className="text-center">
                                        <p className="text-sm text-gray-500">
                                            If this problem persists, please contact your system administrator
                                            {this.state.errorId && (
                                                <span className="block mt-1">
                                                    and provide the error ID: <code className="bg-gray-100 px-1 py-0.5 rounded text-xs">{this.state.errorId}</code>
                                                </span>
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;