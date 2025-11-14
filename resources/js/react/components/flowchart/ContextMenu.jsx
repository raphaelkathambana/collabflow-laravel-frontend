// Hybrid pattern: Import for Vite plugin, then override with window.React
import React from 'react';

// Override with window.React (the shared instance from vendor-react.js)
const SharedReact = window.React || React;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window in ContextMenu');
    throw new Error('React not available - vendor-react.js must load first');
}

/**
 * ContextMenu Component
 *
 * Displays a right-click context menu for task nodes.
 *
 * @param {Object} props
 * @param {number} props.x - X position for menu (pixels from left)
 * @param {number} props.y - Y position for menu (pixels from top)
 * @param {Function} props.onEdit - Callback when Edit is clicked
 * @param {Function} props.onDelete - Callback when Delete is clicked
 * @param {Function} props.onViewSubtasks - Optional callback when View Subtasks is clicked
 * @param {Function} props.onClose - Callback to close the menu
 */
const ContextMenu = ({ x, y, onEdit, onDelete, onViewSubtasks, onClose }) => {
    // Icon Components (inline SVG)
    const EditIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    );

    const TrashIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
    );

    const EyeIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    );

    return (
        <>
            {/* Backdrop to close menu when clicking outside */}
            <div
                className="fixed inset-0 z-40"
                onClick={onClose}
                onContextMenu={(e) => {
                    e.preventDefault();
                    onClose();
                }}
            />

            {/* Menu */}
            <div
                className="fixed z-50 rounded-lg shadow-lg py-1 min-w-[180px]"
                style={{
                    left: x,
                    top: y,
                    backgroundColor: 'var(--color-background-100)',
                    borderWidth: '1px',
                    borderStyle: 'solid',
                    borderColor: 'var(--color-background-300)'
                }}
                onContextMenu={(e) => e.preventDefault()}
            >
                {/* Edit Task */}
                <button
                    onClick={() => {
                        onEdit();
                        onClose();
                    }}
                    className="w-full px-4 py-2 text-left text-sm flex items-center gap-2 transition-colors"
                    style={{ color: 'var(--color-text-700)', backgroundColor: 'transparent' }}
                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                >
                    <EditIcon />
                    Edit Task
                </button>

                {/* View Subtasks (conditional) */}
                {onViewSubtasks && (
                    <button
                        onClick={() => {
                            onViewSubtasks();
                            onClose();
                        }}
                        className="w-full px-4 py-2 text-left text-sm flex items-center gap-2 transition-colors"
                        style={{ color: 'var(--color-text-700)', backgroundColor: 'transparent' }}
                        onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                        onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                    >
                        <EyeIcon />
                        View Subtasks
                    </button>
                )}

                {/* Divider */}
                <div className="my-1" style={{
                    borderTopWidth: '1px',
                    borderTopStyle: 'solid',
                    borderTopColor: 'var(--color-background-300)'
                }} />

                {/* Delete Task */}
                <button
                    onClick={() => {
                        onDelete();
                        onClose();
                    }}
                    className="w-full px-4 py-2 text-left text-sm text-red-600 flex items-center gap-2 transition-colors"
                    style={{ backgroundColor: 'transparent' }}
                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#fef2f2'}
                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                >
                    <TrashIcon />
                    Delete Task
                </button>
            </div>
        </>
    );
};

export default ContextMenu;
