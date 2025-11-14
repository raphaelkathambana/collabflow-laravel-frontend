// Hybrid pattern: Import for Vite plugin, then override with window.React
import React from 'react';

// Override with window.React (the shared instance from vendor-react.js)
const SharedReact = window.React || React;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window in ValidationPanel');
    throw new Error('React not available - vendor-react.js must load first');
}

/**
 * ValidationPanel Component
 *
 * Displays workflow validation results with score and issues list.
 * Shows at the bottom of the flowchart container.
 *
 * @param {Object} props
 * @param {Object} props.validation - Validation result object
 * @param {number} props.validation.score - Score 0-100
 * @param {Array} props.validation.issues - Array of issue objects
 * @param {boolean} props.validation.isValid - Whether workflow passes validation
 */
const ValidationPanel = ({ validation }) => {
    // Icon Components (inline SVG)
    const CheckCircleIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    );

    const InfoIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    );

    const AlertTriangleIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    );

    const AlertCircleIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    );

    // Severity configuration with theme-aware styling
    const getSeverityStyles = (severity) => {
        const baseStyles = {
            borderWidth: '1px',
            borderStyle: 'solid',
        };

        switch (severity) {
            case 'low':
                return {
                    icon: InfoIcon,
                    iconColor: '#2563eb', // blue-600
                    ...baseStyles,
                    backgroundColor: 'rgba(37, 99, 235, 0.1)', // blue with transparency
                    borderColor: 'rgba(37, 99, 235, 0.3)',
                };
            case 'high':
                return {
                    icon: AlertCircleIcon,
                    iconColor: '#dc2626', // red-600
                    ...baseStyles,
                    backgroundColor: 'rgba(220, 38, 38, 0.1)', // red with transparency
                    borderColor: 'rgba(220, 38, 38, 0.3)',
                };
            case 'medium':
            default:
                return {
                    icon: AlertTriangleIcon,
                    iconColor: '#ea580c', // orange-600
                    ...baseStyles,
                    backgroundColor: 'rgba(234, 88, 12, 0.1)', // orange with transparency
                    borderColor: 'rgba(234, 88, 12, 0.3)',
                };
        }
    };

    // Helper to get score color
    const getScoreColor = (score) => {
        if (score >= 90) return 'text-green-600';
        if (score >= 70) return 'text-blue-600';
        if (score >= 50) return 'text-orange-600';
        return 'text-red-600';
    };

    // Helper to get score label
    const getScoreLabel = (score) => {
        if (score >= 90) return 'Excellent';
        if (score >= 70) return 'Good';
        if (score >= 50) return 'Needs Work';
        return 'Poor';
    };

    // If no issues, show success message
    if (!validation.issues || validation.issues.length === 0) {
        return (
            <div className="p-4" style={{
                borderTopWidth: '1px',
                borderTopStyle: 'solid',
                borderTopColor: 'var(--color-background-300)',
                backgroundColor: 'var(--color-background-100)'
            }}>
                <div className="flex items-center gap-3 text-green-600">
                    <CheckCircleIcon />
                    <div>
                        <p className="font-semibold">Workflow looks great!</p>
                        <p className="text-sm" style={{ color: 'var(--color-text-600)' }}>No issues detected. Ready to proceed.</p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div style={{
            borderTopWidth: '1px',
            borderTopStyle: 'solid',
            borderTopColor: 'var(--color-background-300)',
            backgroundColor: 'var(--color-background-100)'
        }}>
            {/* Header with Score */}
            <div className="p-4" style={{
                borderBottomWidth: '1px',
                borderBottomStyle: 'solid',
                borderBottomColor: 'var(--color-background-300)'
            }}>
                <div className="flex items-center justify-between">
                    <h4 className="font-semibold" style={{ color: 'var(--color-text-900)' }}>Workflow Validation</h4>
                    <div className="flex items-center gap-2">
                        <span className="text-sm" style={{ color: 'var(--color-text-600)' }}>Score:</span>
                        <span className={`text-lg font-bold ${getScoreColor(validation.score)}`}>
                            {validation.score}/100
                        </span>
                        <span className={`text-sm ${getScoreColor(validation.score)}`}>
                            {getScoreLabel(validation.score)}
                        </span>
                    </div>
                </div>
            </div>

            {/* Issues List */}
            <div className="p-4 space-y-2 max-h-48 overflow-y-auto">
                {validation.issues.map((issue, index) => {
                    const styles = getSeverityStyles(issue.severity);
                    const IconComponent = styles.icon;

                    return (
                        <div
                            key={index}
                            className="p-3 rounded-lg"
                            style={{
                                backgroundColor: styles.backgroundColor,
                                borderWidth: styles.borderWidth,
                                borderStyle: styles.borderStyle,
                                borderColor: styles.borderColor,
                            }}
                        >
                            <div className="flex items-start gap-3">
                                <div className="mt-0.5 flex-shrink-0" style={{ color: styles.iconColor }}>
                                    <IconComponent />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium" style={{ color: 'var(--color-text-900)' }}>{issue.message}</p>
                                    {issue.affectedTasks && issue.affectedTasks.length > 0 && (
                                        <p className="text-xs mt-1" style={{ color: 'var(--color-text-600)' }}>
                                            Affects {issue.affectedTasks.length} task(s)
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export default ValidationPanel;
