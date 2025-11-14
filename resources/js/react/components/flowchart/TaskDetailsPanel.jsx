// Hybrid pattern: Import for Vite plugin, then override with window.React
import React, { useState } from 'react';

// Override with window.React (the shared instance from vendor-react.js)
const SharedReact = window.React || React;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window in TaskDetailsPanel');
    throw new Error('React not available - vendor-react.js must load first');
}

/**
 * TaskDetailsPanel Component
 *
 * Displays detailed information about a selected task in a right-side panel.
 * Supports both view and edit modes.
 *
 * @param {Object} props
 * @param {Object} props.task - The task object to display/edit
 * @param {Function} props.onClose - Callback when panel is closed
 * @param {Function} props.onUpdate - Callback when task is updated
 * @param {Function} props.onDelete - Callback when task is deleted
 * @param {Function} props.onViewSubtasks - Optional callback to view subtasks
 */
const TaskDetailsPanel = ({ task, onClose, onUpdate, onDelete, onViewSubtasks }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [editedTask, setEditedTask] = useState(task);

    // Task type configuration
    const taskTypeConfig = {
        ai: { label: 'AI Task', color: 'text-blue-600', bgColor: 'bg-blue-50', borderColor: 'border-blue-200' },
        human: { label: 'Human Task', color: 'text-green-600', bgColor: 'bg-green-50', borderColor: 'border-green-200' },
        hitl: { label: 'HITL Checkpoint', color: 'text-orange-600', bgColor: 'bg-orange-50', borderColor: 'border-orange-200' },
    };

    const handleSave = () => {
        onUpdate(editedTask);
        setIsEditing(false);
    };

    const handleCancel = () => {
        setEditedTask(task); // Reset to original task
        setIsEditing(false);
    };

    const handleDelete = () => {
        if (confirm(`Are you sure you want to delete "${task.title || task.name}"?`)) {
            onDelete(task.id);
            onClose();
        }
    };

    const taskTitle = task.title || task.name;
    const taskType = task.type || 'human';
    const typeConfig = taskTypeConfig[taskType];

    // Icon Components (inline SVG)
    const CloseIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    );

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

    const ClockIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    );

    const ListTreeIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
    );

    const EyeIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    );

    const BotIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
        </svg>
    );

    const UserIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
    );

    const UsersIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    );

    const TypeIcon = taskType === 'ai' ? BotIcon : taskType === 'hitl' ? UsersIcon : UserIcon;

    return (
        <div className="fixed right-0 top-0 h-full w-96 shadow-lg z-50 flex flex-col" style={{
            backgroundColor: 'var(--color-background-100)',
            borderLeftWidth: '1px',
            borderLeftStyle: 'solid',
            borderLeftColor: 'var(--color-background-300)'
        }}>
            {/* Header */}
            <div className="p-4 flex items-center justify-between" style={{
                borderBottomWidth: '1px',
                borderBottomStyle: 'solid',
                borderBottomColor: 'var(--color-background-300)'
            }}>
                <h3 className="font-semibold text-lg" style={{ color: 'var(--color-text-900)' }}>Task Details</h3>
                <button
                    onClick={onClose}
                    className="p-1 rounded-md transition-colors"
                    style={{ backgroundColor: 'transparent' }}
                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                    title="Close panel"
                >
                    <CloseIcon />
                </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
                {!isEditing ? (
                    <>
                        {/* View Mode */}
                        <div className="space-y-4">
                            <div>
                                <div className="flex items-center gap-2 mb-2">
                                    <TypeIcon className={typeConfig.color} />
                                    <span className="text-sm" style={{ color: 'var(--color-text-600)' }}>{typeConfig.label}</span>
                                </div>
                                <h4 className="text-xl font-semibold" style={{ color: 'var(--color-text-900)' }}>{taskTitle}</h4>
                            </div>

                            <div>
                                <label className="text-sm font-medium" style={{ color: 'var(--color-text-600)' }}>Description</label>
                                <p className="mt-1 text-sm" style={{ color: 'var(--color-text-700)' }}>{task.description || 'No description provided'}</p>
                            </div>

                            {task.estimatedHours && (
                                <div className="flex items-center gap-2 text-sm" style={{ color: 'var(--color-text-700)' }}>
                                    <ClockIcon />
                                    <span>{task.estimatedHours} hours estimated</span>
                                </div>
                            )}

                            {/* Subtasks Section */}
                            {task.subtasks && task.subtasks.length > 0 && (
                                <div className="p-3 rounded-lg" style={{
                                    borderWidth: '1px',
                                    borderStyle: 'solid',
                                    borderColor: 'var(--color-background-300)',
                                    backgroundColor: 'var(--color-background-50)'
                                }}>
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="flex items-center gap-2">
                                            <ListTreeIcon />
                                            <span className="text-sm font-medium" style={{ color: 'var(--color-text-900)' }}>Subtasks</span>
                                        </div>
                                        <span className="px-2 py-0.5 text-xs font-medium rounded" style={{
                                            backgroundColor: 'var(--color-background-200)',
                                            color: 'var(--color-text-700)'
                                        }}>
                                            {task.subtasks.length}
                                        </span>
                                    </div>
                                    {onViewSubtasks && (
                                        <button
                                            onClick={onViewSubtasks}
                                            className="w-full mt-2 px-3 py-2 text-sm font-medium rounded-md transition-colors flex items-center justify-center gap-2"
                                            style={{
                                                color: 'var(--color-text-700)',
                                                backgroundColor: 'var(--color-background-100)',
                                                borderWidth: '1px',
                                                borderStyle: 'solid',
                                                borderColor: 'var(--color-background-300)'
                                            }}
                                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-100)'}
                                        >
                                            <EyeIcon />
                                            View Subtasks
                                        </button>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Action Buttons */}
                        <div className="space-y-2 pt-4" style={{
                            borderTopWidth: '1px',
                            borderTopStyle: 'solid',
                            borderTopColor: 'var(--color-background-300)'
                        }}>
                            <button
                                onClick={() => setIsEditing(true)}
                                className="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md transition-colors flex items-center justify-center gap-2"
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#2563eb'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#3b82f6'}
                            >
                                <EditIcon />
                                Edit Task
                            </button>
                            {onViewSubtasks && task.subtasks && task.subtasks.length > 0 && (
                                <button
                                    onClick={onViewSubtasks}
                                    className="w-full px-4 py-2 text-sm font-medium rounded-md transition-colors flex items-center justify-center gap-2"
                                    style={{
                                        color: 'var(--color-text-700)',
                                        backgroundColor: 'var(--color-background-100)',
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: 'var(--color-background-300)'
                                    }}
                                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-100)'}
                                >
                                    <ListTreeIcon />
                                    Manage Subtasks
                                </button>
                            )}
                            <button
                                onClick={handleDelete}
                                className="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md transition-colors flex items-center justify-center gap-2"
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#dc2626'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#ef4444'}
                            >
                                <TrashIcon />
                                Delete Task
                            </button>
                        </div>
                    </>
                ) : (
                    <>
                        {/* Edit Mode */}
                        <div className="space-y-4">
                            <div>
                                <label htmlFor="task-title" className="block text-sm font-medium mb-1" style={{ color: 'var(--color-text-700)' }}>
                                    Title
                                </label>
                                <input
                                    id="task-title"
                                    type="text"
                                    value={editedTask.title || editedTask.name || ''}
                                    onChange={(e) => setEditedTask({ ...editedTask, title: e.target.value })}
                                    className="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style={{
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: 'var(--color-background-300)',
                                        backgroundColor: 'var(--color-background-50)',
                                        color: 'var(--color-text-900)'
                                    }}
                                />
                            </div>

                            <div>
                                <label htmlFor="task-description" className="block text-sm font-medium mb-1" style={{ color: 'var(--color-text-700)' }}>
                                    Description
                                </label>
                                <textarea
                                    id="task-description"
                                    value={editedTask.description || ''}
                                    onChange={(e) => setEditedTask({ ...editedTask, description: e.target.value })}
                                    rows={4}
                                    className="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style={{
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: 'var(--color-background-300)',
                                        backgroundColor: 'var(--color-background-50)',
                                        color: 'var(--color-text-900)'
                                    }}
                                />
                            </div>

                            <div>
                                <label htmlFor="task-type" className="block text-sm font-medium mb-1" style={{ color: 'var(--color-text-700)' }}>
                                    Type
                                </label>
                                <select
                                    id="task-type"
                                    value={editedTask.type || 'human'}
                                    onChange={(e) => setEditedTask({ ...editedTask, type: e.target.value })}
                                    className="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style={{
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: 'var(--color-background-300)',
                                        backgroundColor: 'var(--color-background-50)',
                                        color: 'var(--color-text-900)'
                                    }}
                                >
                                    <option value="ai">AI Task</option>
                                    <option value="human">Human Task</option>
                                    <option value="hitl">HITL Checkpoint</option>
                                </select>
                            </div>

                            <div>
                                <label htmlFor="task-hours" className="block text-sm font-medium mb-1" style={{ color: 'var(--color-text-700)' }}>
                                    Estimated Hours
                                </label>
                                <input
                                    id="task-hours"
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    value={editedTask.estimatedHours || ''}
                                    onChange={(e) => setEditedTask({ ...editedTask, estimatedHours: parseFloat(e.target.value) || 0 })}
                                    className="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style={{
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: 'var(--color-background-300)',
                                        backgroundColor: 'var(--color-background-50)',
                                        color: 'var(--color-text-900)'
                                    }}
                                />
                            </div>
                        </div>

                        {/* Save/Cancel Buttons */}
                        <div className="flex gap-2 pt-4" style={{
                            borderTopWidth: '1px',
                            borderTopStyle: 'solid',
                            borderTopColor: 'var(--color-background-300)'
                        }}>
                            <button
                                onClick={handleSave}
                                className="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md transition-colors"
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#2563eb'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#3b82f6'}
                            >
                                Save Changes
                            </button>
                            <button
                                onClick={handleCancel}
                                className="px-4 py-2 text-sm font-medium rounded-md transition-colors"
                                style={{
                                    color: 'var(--color-text-700)',
                                    backgroundColor: 'var(--color-background-100)',
                                    borderWidth: '1px',
                                    borderStyle: 'solid',
                                    borderColor: 'var(--color-background-300)'
                                }}
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-100)'}
                            >
                                Cancel
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default TaskDetailsPanel;
