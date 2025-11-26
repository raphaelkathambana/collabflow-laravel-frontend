// Use window.React ONLY (shared instance from vendor-react.js)
// CRITICAL: DO NOT import React directly - this would bundle React into the chunk!
const SharedReact = window.React;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window in FlowchartToolbar');
    throw new Error('React not available - vendor-react.js must load first');
}

/**
 * FlowchartToolbar Component
 *
 * Provides interactive controls for the flowchart:
 * - Auto Layout: Triggers Dagre re-layout algorithm
 * - Layout Direction Toggle: Switch between horizontal and vertical layouts
 * - Export: Export flowchart as PNG, SVG, or JSON
 * - Fullscreen: Toggle fullscreen mode
 *
 * @param {Object} props
 * @param {Function} props.onAutoLayout - Callback to trigger auto-layout
 * @param {Function} props.onToggleLayout - Callback to toggle layout direction
 * @param {Function} props.onExport - Callback to export flowchart (format: 'png' | 'svg' | 'json')
 * @param {Function} props.onFullscreen - Callback to toggle fullscreen
 * @param {string} props.layoutDirection - Current layout direction ('horizontal' or 'vertical')
 * @param {boolean} props.readOnly - Whether controls should be disabled
 */
const FlowchartToolbar = ({
    onAutoLayout,
    onToggleLayout,
    onExport,
    onFullscreen,
    layoutDirection = 'vertical',
    readOnly = false,
}) => {
    const [showExportMenu, setShowExportMenu] = React.useState(false);
    // Icon SVGs (inline to avoid lucide-react dependency)
    const LayoutGridIcon = () => (
        <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <rect x="3" y="3" width="7" height="7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <rect x="14" y="3" width="7" height="7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <rect x="14" y="14" width="7" height="7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <rect x="3" y="14" width="7" height="7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
    );

    const ArrowLeftRightIcon = () => (
        <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16l-4-4m0 0l4-4m-4 4h18m0 0l-4 4m4-4l-4-4"/>
        </svg>
    );

    const ArrowUpDownIcon = () => (
        <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
        </svg>
    );

    const DownloadIcon = () => (
        <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
    );

    const ChevronDownIcon = () => (
        <svg
            className="h-3 w-3"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"/>
        </svg>
    );

    const MaximizeIcon = () => (
        <svg
            className="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
        </svg>
    );

    const handleExport = (format) => {
        if (onExport) {
            onExport(format);
        }
        setShowExportMenu(false);
    };

    return (
        <div className="flex items-center gap-2 rounded-lg p-2 shadow-sm" style={{
            backgroundColor: 'var(--color-background-100)',
            borderWidth: '1px',
            borderStyle: 'solid',
            borderColor: 'var(--color-background-300)'
        }}>
            {/* Auto Layout Button */}
            <button
                onClick={onAutoLayout}
                disabled={readOnly}
                className="h-8 px-3 flex items-center gap-2 text-sm font-medium rounded-md transition-colors duration-200"
                style={{
                    color: readOnly ? 'var(--color-text-400)' : 'var(--color-text-700)',
                    cursor: readOnly ? 'not-allowed' : 'pointer'
                }}
                onMouseEnter={(e) => {
                    if (!readOnly) e.currentTarget.style.backgroundColor = 'var(--color-background-200)';
                }}
                onMouseLeave={(e) => {
                    e.currentTarget.style.backgroundColor = 'transparent';
                }}
                title="Automatically arrange nodes using Dagre layout algorithm"
            >
                <LayoutGridIcon />
                <span>Auto Layout</span>
            </button>

            {/* Layout Direction Toggle Button */}
            <button
                onClick={onToggleLayout}
                disabled={readOnly}
                className="h-8 px-3 flex items-center gap-2 text-sm font-medium rounded-md transition-colors duration-200"
                style={{
                    color: readOnly ? 'var(--color-text-400)' : 'var(--color-text-700)',
                    cursor: readOnly ? 'not-allowed' : 'pointer'
                }}
                onMouseEnter={(e) => {
                    if (!readOnly) e.currentTarget.style.backgroundColor = 'var(--color-background-200)';
                }}
                onMouseLeave={(e) => {
                    e.currentTarget.style.backgroundColor = 'transparent';
                }}
                title={`Switch to ${layoutDirection === 'horizontal' ? 'vertical' : 'horizontal'} layout`}
            >
                {layoutDirection === 'horizontal' ? <ArrowLeftRightIcon /> : <ArrowUpDownIcon />}
                <span>{layoutDirection === 'horizontal' ? 'Horizontal' : 'Vertical'}</span>
            </button>

            {/* Divider */}
            <div className="h-6 w-px" style={{ backgroundColor: 'var(--color-background-300)' }}></div>

            {/* Fullscreen Button */}
            {onFullscreen && (
                <button
                    onClick={onFullscreen}
                    className="h-8 px-3 flex items-center gap-2 text-sm font-medium rounded-md transition-colors duration-200 cursor-pointer"
                    style={{ color: 'var(--color-text-700)' }}
                    onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = 'var(--color-background-200)';
                    }}
                    onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = 'transparent';
                    }}
                    title="Toggle fullscreen mode"
                >
                    <MaximizeIcon />
                    <span>Fullscreen</span>
                </button>
            )}

            {/* Export Button with Dropdown */}
            <div className="relative">
                <button
                    onClick={() => setShowExportMenu(!showExportMenu)}
                    className="h-8 px-3 flex items-center gap-2 text-sm font-medium rounded-md transition-colors duration-200 cursor-pointer"
                    style={{ color: 'var(--color-text-700)' }}
                    onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = 'var(--color-background-200)';
                    }}
                    onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = 'transparent';
                    }}
                    title="Export flowchart"
                >
                    <DownloadIcon />
                    <span>Export</span>
                    <ChevronDownIcon />
                </button>

                {/* Dropdown Menu */}
                {showExportMenu && (
                    <>
                        {/* Backdrop to close menu when clicking outside */}
                        <div
                            className="fixed inset-0 z-10"
                            onClick={() => setShowExportMenu(false)}
                        ></div>

                        {/* Menu */}
                        <div className="absolute right-0 top-full mt-1 w-40 rounded-lg shadow-lg z-20 py-1" style={{
                            backgroundColor: 'var(--color-background-100)',
                            borderWidth: '1px',
                            borderStyle: 'solid',
                            borderColor: 'var(--color-background-300)'
                        }}>
                            <button
                                onClick={() => handleExport('png')}
                                className="w-full px-4 py-2 text-left text-sm flex items-center gap-2"
                                style={{ color: 'var(--color-text-700)' }}
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Export as PNG
                            </button>
                            <button
                                onClick={() => handleExport('svg')}
                                className="w-full px-4 py-2 text-left text-sm flex items-center gap-2"
                                style={{ color: 'var(--color-text-700)' }}
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                                Export as SVG
                            </button>
                            <button
                                onClick={() => handleExport('pdf')}
                                className="w-full px-4 py-2 text-left text-sm flex items-center gap-2"
                                style={{ color: 'var(--color-text-700)' }}
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Export as PDF
                            </button>
                            <button
                                onClick={() => handleExport('json')}
                                className="w-full px-4 py-2 text-left text-sm flex items-center gap-2"
                                style={{ color: 'var(--color-text-700)' }}
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                            >
                                <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Export as JSON
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default FlowchartToolbar;
