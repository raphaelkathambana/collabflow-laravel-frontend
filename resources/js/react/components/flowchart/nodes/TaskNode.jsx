// Use window.React ONLY (shared instance from vendor-react.js)
// CRITICAL: DO NOT import React directly - this would bundle React into the chunk!
import { Handle, Position } from '@xyflow/react';
import { validateTask } from '../utils/validationUtils';

const SharedReact = window.React;
const { memo } = SharedReact;

const taskTypeConfig = {
    ai: {
        color: '#5c80bc', // glaucous
        bgClass: 'bg-[#5c80bc]/10',
        borderClass: 'border-[#5c80bc]',
        textClass: 'text-[#5c80bc]',
    },
    human: {
        color: '#c4d6b0', // tea-green
        bgClass: 'bg-[#c4d6b0]/10',
        borderClass: 'border-[#c4d6b0]',
        textClass: 'text-[#2d5a3d]',
    },
    hitl: {
        color: '#ff9f1c', // orange-peel
        bgClass: 'bg-[#ff9f1c]/10',
        borderClass: 'border-[#ff9f1c]',
        textClass: 'text-[#ff9f1c]',
    },
};

const TaskNode = memo(({ data, selected }) => {
    const { task, label, type = 'ai', estimatedHours, subtaskCount, name, description, onSubtaskClick, subtasks } = data;

    // Detect if task has checkpoints
    const hasCheckpoints = subtasks && Array.isArray(subtasks) && subtasks.some(st => st.is_checkpoint === true);
    const checkpointCount = subtasks ? subtasks.filter(st => st.is_checkpoint === true).length : 0;

    // Use checkpoint config if task has checkpoints, otherwise use type config
    const config = hasCheckpoints
        ? { color: '#FFD93D', bgClass: 'bg-[#FFD93D]/10', borderClass: 'border-[#FFD93D]', textClass: 'text-[#2D3748]' }
        : (taskTypeConfig[type] || taskTypeConfig.ai);

    // Validate task quality
    const taskData = { name: label || name, description, type, estimatedHours };
    const validation = validateTask(taskData);
    const hasIssues = validation.score < 70;
    const hasHighIssues = validation.issues.some(issue => issue.severity === 'high');

    // Warning icon
    const WarningIcon = () => (
        <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
        </svg>
    );

    return (
        <div
            className={`
                relative px-4 py-3 rounded-lg border-2 transition-all
                ${config.bgClass} ${config.borderClass}
                ${selected ? 'shadow-lg ring-2 ring-offset-2' : 'shadow-sm'}
                ${hasCheckpoints ? 'node-checkpoint' : type === 'hitl' ? 'node-hitl' : ''}
                hover:shadow-md cursor-pointer
                w-[200px] min-h-[80px]
            `}
            style={{ borderColor: config.color }}
        >
            {/* Checkpoint Indicator Badge - Top Right (higher priority than validation) */}
            {hasCheckpoints && (
                <div
                    className="checkpoint-indicator"
                    title={`${checkpointCount} checkpoint${checkpointCount > 1 ? 's' : ''} in this task`}
                />
            )}

            {/* Validation Badge - Top Right */}
            {!hasCheckpoints && hasIssues && (
                <div
                    className={`absolute -top-2 -right-2 rounded-full p-1 shadow-md ${
                        hasHighIssues ? 'bg-red-500 text-white' : 'bg-orange-500 text-white'
                    }`}
                    title={`Quality score: ${validation.score}/100\n${validation.issues.map(i => i.message).join('\n')}`}
                >
                    <WarningIcon />
                </div>
            )}

            {/* Handles for connections */}
            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3"
                style={{ background: config.color }}
            />

            {/* Task content */}
            <div className="flex flex-col gap-2">
                {/* Title */}
                <div className="text-sm font-semibold line-clamp-2" style={{ color: 'var(--color-text-900)' }}>
                    {label}
                </div>

                {/* Footer: Type badge + Hours + Subtasks */}
                <div className="flex items-center justify-between text-xs">
                    <span className={`${config.textClass} font-medium uppercase tracking-wide`}>
                        {type}
                    </span>

                    <div className="flex items-center gap-2" style={{ color: 'var(--color-text-600)' }}>
                        {estimatedHours > 0 && (
                            <span title="Estimated hours">
                                {estimatedHours}h
                            </span>
                        )}
                        {subtaskCount > 0 && (
                            <button
                                className="flex items-center gap-1 px-1.5 py-0.5 rounded transition-colors cursor-pointer"
                                style={{ backgroundColor: 'var(--color-background-200)' }}
                                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-300)'}
                                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                title={`${subtaskCount} subtasks - Click to manage`}
                                onClick={(e) => {
                                    e.stopPropagation(); // Prevent node selection
                                    if (onSubtaskClick) {
                                        onSubtaskClick(data);
                                    }
                                }}
                            >
                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                {subtaskCount}
                            </button>
                        )}
                        {hasIssues && (
                            <div
                                className={`flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium ${
                                    hasHighIssues
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                }`}
                                title={`Quality Score: ${validation.score}/100\n${validation.issues.map(i => `• ${i.message}`).join('\n')}`}
                            >
                                <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                </svg>
                                {validation.score}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <Handle
                type="source"
                position={Position.Bottom}
                className="w-3 h-3"
                style={{ background: config.color }}
            />
        </div>
    );
});

TaskNode.displayName = 'TaskNode';

export default TaskNode;
