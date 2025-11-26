// Use window.React ONLY (shared instance from vendor-react.js)
// CRITICAL: DO NOT import React directly - this would bundle React into the chunk!
import {
    ReactFlow,
    Controls,
    Background,
    MiniMap,
    useNodesState,
    useEdgesState,
    ReactFlowProvider,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';

const SharedReact = window.React;
const { useState, useCallback, useMemo, useEffect } = SharedReact;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window in SubtaskModal');
    throw new Error('React not available - vendor-react.js must load first');
}

// Import node components and utils
import TaskNode from './nodes/TaskNode';
import StartNode from './nodes/StartNode';
import EndNode from './nodes/EndNode';
import { generateFlowchartLayout } from './utils/layoutUtils';

/**
 * SubtaskModal Component
 *
 * Modal dialog with nested flowchart for managing subtasks of a parent task.
 * Features split-screen: flowchart on left, subtask list on right.
 *
 * @param {Object} props
 * @param {Object} props.task - Parent task containing subtasks
 * @param {Function} props.onClose - Callback to close modal
 * @param {Function} props.onUpdateSubtasks - Callback with updated subtasks array
 */
const SubtaskModal = ({ task, onClose, onUpdateSubtasks }) => {
    const [subtasks, setSubtasks] = useState(task.subtasks || []);
    const [newSubtaskTitle, setNewSubtaskTitle] = useState('');
    const [editingSubtask, setEditingSubtask] = useState(null);
    const [layoutDirection, setLayoutDirection] = useState('vertical');

    // Generate flowchart from subtasks
    const { initialNodes, initialEdges } = useMemo(() => {
        if (subtasks.length === 0) {
            return { initialNodes: [], initialEdges: [] };
        }

        // Generate layout
        const layout = generateFlowchartLayout(subtasks, layoutDirection);

        // Restore saved positions if they exist
        const nodesWithSavedPositions = layout.initialNodes.map(node => {
            const subtask = subtasks.find(st => st.id === node.id);
            if (subtask?.position) {
                return {
                    ...node,
                    position: subtask.position,
                };
            }
            return node;
        });

        return {
            initialNodes: nodesWithSavedPositions,
            initialEdges: layout.initialEdges
        };
    }, [subtasks, layoutDirection]);

    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);

    // Update layout when direction or subtasks change
    useEffect(() => {
        const layout = generateFlowchartLayout(subtasks, layoutDirection);
        setNodes(layout.initialNodes);
        setEdges(layout.initialEdges);
    }, [subtasks, layoutDirection, setNodes, setEdges]);

    const nodeTypes = useMemo(
        () => ({
            start: StartNode,
            task: TaskNode,
            end: EndNode,
        }),
        []
    );

    // Handle add subtask
    const handleAddSubtask = useCallback(() => {
        if (!newSubtaskTitle.trim()) return;

        const newSubtask = {
            id: `subtask-${Date.now()}`,
            title: newSubtaskTitle,
            name: newSubtaskTitle,
            description: 'Subtask description',
            type: 'human',
            estimatedHours: 2,
        };

        setSubtasks([...subtasks, newSubtask]);
        setNewSubtaskTitle('');
    }, [newSubtaskTitle, subtasks]);

    // Handle delete subtask
    const handleDeleteSubtask = useCallback(
        (subtaskId) => {
            if (confirm('Are you sure you want to delete this subtask?')) {
                setSubtasks(subtasks.filter((st) => st.id !== subtaskId));
            }
        },
        [subtasks]
    );

    // Handle edit subtask
    const handleEditSubtask = useCallback((subtask) => {
        setEditingSubtask(subtask);
    }, []);

    // Handle save edited subtask
    const handleSaveEditedSubtask = useCallback(() => {
        if (!editingSubtask) return;
        setSubtasks(subtasks.map((st) => (st.id === editingSubtask.id ? editingSubtask : st)));
        setEditingSubtask(null);
    }, [editingSubtask, subtasks]);

    // Handle node drag stop - persist subtask positions
    const handleNodeDragStop = useCallback((event, node) => {
        console.log('Subtask node drag stopped:', node.id, node.position);

        // Update subtask with new position
        setSubtasks((prevSubtasks) =>
            prevSubtasks.map((st) => {
                if (st.id === node.id) {
                    return {
                        ...st,
                        position: node.position,
                    };
                }
                return st;
            })
        );
    }, []);

    // Handle save and close
    const handleSaveAndClose = useCallback(() => {
        // Save subtasks with their positions to parent
        const subtasksWithPositions = subtasks.map((st, index) => {
            // Find corresponding node position
            const node = nodes.find(n => n.id === st.id);
            return {
                ...st,
                position: node?.position || st.position,
            };
        });
        onUpdateSubtasks(subtasksWithPositions);
        onClose();
    }, [subtasks, nodes, onUpdateSubtasks, onClose]);

    // Icons
    const CloseIcon = () => (
        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    );

    const PlusIcon = () => (
        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
        </svg>
    );

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div className="rounded-xl w-[90vw] max-w-[1200px] h-[90vh] flex flex-col shadow-2xl" style={{
                backgroundColor: 'var(--color-background-100)'
            }}>
                {/* Header */}
                <div className="p-6 flex justify-between items-center" style={{
                    borderBottomWidth: '1px',
                    borderBottomStyle: 'solid',
                    borderBottomColor: 'var(--color-background-300)'
                }}>
                    <div>
                        <h2 className="text-2xl font-bold" style={{ color: 'var(--color-text-900)' }}>{task.title || task.name}</h2>
                        <p className="text-sm mt-1" style={{ color: 'var(--color-text-600)' }}>Subtask Planning & Visualization</p>
                    </div>
                    <button
                        onClick={handleSaveAndClose}
                        className="p-2 rounded-md transition-colors"
                        style={{ backgroundColor: 'transparent' }}
                        onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                        onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                        title="Close"
                    >
                        <CloseIcon />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-hidden flex">
                    {/* Left: Flowchart */}
                    <div className="flex-1 p-6">
                        <div className="flex items-center justify-between mb-3">
                            <h3 className="text-lg font-semibold" style={{ color: 'var(--color-text-900)' }}>Subtask Flow</h3>

                            {/* Layout Direction Toggle */}
                            <div className="flex gap-1 rounded-lg p-1" style={{ backgroundColor: 'var(--color-background-200)' }}>
                                <button
                                    onClick={() => setLayoutDirection('vertical')}
                                    className="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                                    style={{
                                        backgroundColor: layoutDirection === 'vertical' ? 'var(--color-background-100)' : 'transparent',
                                        color: layoutDirection === 'vertical' ? 'var(--color-text-900)' : 'var(--color-text-600)',
                                        boxShadow: layoutDirection === 'vertical' ? '0 1px 2px 0 rgb(0 0 0 / 0.05)' : 'none'
                                    }}
                                    onMouseEnter={(e) => {
                                        if (layoutDirection !== 'vertical') e.currentTarget.style.color = 'var(--color-text-900)';
                                    }}
                                    onMouseLeave={(e) => {
                                        if (layoutDirection !== 'vertical') e.currentTarget.style.color = 'var(--color-text-600)';
                                    }}
                                    title="Vertical layout"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v14m0-14l-4 4m4-4l4 4" />
                                    </svg>
                                </button>
                                <button
                                    onClick={() => setLayoutDirection('horizontal')}
                                    className="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                                    style={{
                                        backgroundColor: layoutDirection === 'horizontal' ? 'var(--color-background-100)' : 'transparent',
                                        color: layoutDirection === 'horizontal' ? 'var(--color-text-900)' : 'var(--color-text-600)',
                                        boxShadow: layoutDirection === 'horizontal' ? '0 1px 2px 0 rgb(0 0 0 / 0.05)' : 'none'
                                    }}
                                    onMouseEnter={(e) => {
                                        if (layoutDirection !== 'horizontal') e.currentTarget.style.color = 'var(--color-text-900)';
                                    }}
                                    onMouseLeave={(e) => {
                                        if (layoutDirection !== 'horizontal') e.currentTarget.style.color = 'var(--color-text-600)';
                                    }}
                                    title="Horizontal layout"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 12h14m0 0l-4-4m4 4l-4 4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div className="h-[calc(100%-3.5rem)] rounded-lg overflow-hidden" style={{
                            borderWidth: '1px',
                            borderStyle: 'solid',
                            borderColor: 'var(--color-background-300)',
                            backgroundColor: 'var(--color-background-50)'
                        }}>
                            {subtasks.length > 0 ? (
                                <ReactFlowProvider>
                                    <ReactFlow
                                        nodes={nodes}
                                        edges={edges}
                                        nodeTypes={nodeTypes}
                                        onNodesChange={onNodesChange}
                                        onEdgesChange={onEdgesChange}
                                        onNodeDragStop={handleNodeDragStop}
                                        fitView
                                        nodesDraggable={true}
                                        nodesConnectable={false}
                                        elementsSelectable={true}
                                    >
                                        <Background
                                            color="var(--color-background-400)"
                                            gap={16}
                                            style={{ backgroundColor: 'var(--color-background-50)' }}
                                        />
                                        <Controls
                                            style={{
                                                backgroundColor: 'var(--color-background-100)',
                                                borderColor: 'var(--color-background-300)',
                                            }}
                                            className="react-flow-controls-themed"
                                        />
                                        <MiniMap
                                            zoomable
                                            pannable
                                            style={{
                                                backgroundColor: 'var(--color-background-100)',
                                                borderColor: 'var(--color-background-300)',
                                            }}
                                            className="react-flow-minimap-themed"
                                            nodeColor={(node) => {
                                                if (node.type === 'start') return 'var(--color-glaucous)';
                                                if (node.type === 'end') return 'var(--color-tea-green)';
                                                return 'var(--color-background-300)';
                                            }}
                                            maskColor="rgba(0, 0, 0, 0.1)"
                                        />
                                    </ReactFlow>
                                </ReactFlowProvider>
                            ) : (
                                <div className="h-full flex items-center justify-center" style={{ color: 'var(--color-text-500)' }}>
                                    <p>No subtasks yet. Add your first subtask to get started.</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right: Subtask Management */}
                    <div className="w-96 p-6 overflow-y-auto" style={{
                        borderLeftWidth: '1px',
                        borderLeftStyle: 'solid',
                        borderLeftColor: 'var(--color-background-300)'
                    }}>
                        {/* Add Subtask Form */}
                        <div className="mb-6">
                            <h4 className="text-md font-semibold mb-3" style={{ color: 'var(--color-text-900)' }}>Add New Subtask</h4>
                            <div className="space-y-2">
                                <input
                                    type="text"
                                    value={newSubtaskTitle}
                                    onChange={(e) => setNewSubtaskTitle(e.target.value)}
                                    onKeyPress={(e) => e.key === 'Enter' && handleAddSubtask()}
                                    placeholder="Subtask title..."
                                    className="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style={{
                                        borderWidth: '1px',
                                        borderStyle: 'solid',
                                        borderColor: 'var(--color-background-300)',
                                        backgroundColor: 'var(--color-background-50)',
                                        color: 'var(--color-text-900)'
                                    }}
                                />
                                <button
                                    onClick={handleAddSubtask}
                                    className="w-full px-4 py-2 bg-blue-600 text-white rounded-md transition-colors flex items-center justify-center gap-2"
                                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#2563eb'}
                                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#3b82f6'}
                                >
                                    <PlusIcon />
                                    Add Subtask
                                </button>
                            </div>
                        </div>

                        {/* Subtask List */}
                        {subtasks.length > 0 && (
                            <div>
                                <h4 className="text-md font-semibold mb-3" style={{ color: 'var(--color-text-900)' }}>
                                    Manage Subtasks ({subtasks.length})
                                </h4>
                                <div className="space-y-2">
                                    {subtasks.map((subtask, index) => (
                                        <div
                                            key={subtask.id}
                                            className="p-3 rounded-lg transition-colors"
                                            style={{
                                                borderWidth: '1px',
                                                borderStyle: 'solid',
                                                borderColor: 'var(--color-background-300)',
                                                backgroundColor: 'var(--color-background-100)'
                                            }}
                                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-100)'}
                                        >
                                            {editingSubtask?.id === subtask.id ? (
                                                <div className="space-y-2">
                                                    <input
                                                        type="text"
                                                        value={editingSubtask.title || editingSubtask.name}
                                                        onChange={(e) =>
                                                            setEditingSubtask({
                                                                ...editingSubtask,
                                                                title: e.target.value,
                                                                name: e.target.value,
                                                            })
                                                        }
                                                        className="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                        style={{
                                                            borderWidth: '1px',
                                                            borderStyle: 'solid',
                                                            borderColor: 'var(--color-background-300)',
                                                            backgroundColor: 'var(--color-background-50)',
                                                            color: 'var(--color-text-900)'
                                                        }}
                                                    />
                                                    <div className="flex gap-2">
                                                        <button
                                                            onClick={handleSaveEditedSubtask}
                                                            className="flex-1 px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md transition-colors"
                                                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#2563eb'}
                                                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#3b82f6'}
                                                        >
                                                            Save
                                                        </button>
                                                        <button
                                                            onClick={() => setEditingSubtask(null)}
                                                            className="px-3 py-1.5 text-sm rounded-md transition-colors"
                                                            style={{
                                                                borderWidth: '1px',
                                                                borderStyle: 'solid',
                                                                borderColor: 'var(--color-background-300)',
                                                                backgroundColor: 'var(--color-background-100)',
                                                                color: 'var(--color-text-700)'
                                                            }}
                                                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-100)'}
                                                        >
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="flex items-center justify-between">
                                                    <div className="flex-1">
                                                        <span className="text-sm mr-2" style={{ color: 'var(--color-text-500)' }}>{index + 1}.</span>
                                                        <span className="font-medium" style={{ color: 'var(--color-text-900)' }}>
                                                            {subtask.title || subtask.name}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <button
                                                            onClick={() => handleEditSubtask(subtask)}
                                                            className="px-2 py-1 text-xs rounded transition-colors"
                                                            style={{
                                                                color: 'var(--color-text-700)',
                                                                backgroundColor: 'transparent'
                                                            }}
                                                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                                                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                                                        >
                                                            Edit
                                                        </button>
                                                        <button
                                                            onClick={() => handleDeleteSubtask(subtask.id)}
                                                            className="px-2 py-1 text-xs text-red-600 rounded transition-colors"
                                                            style={{ backgroundColor: 'transparent' }}
                                                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#fef2f2'}
                                                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                                                        >
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Footer */}
                <div className="p-6 flex justify-end gap-2" style={{
                    borderTopWidth: '1px',
                    borderTopStyle: 'solid',
                    borderTopColor: 'var(--color-background-300)'
                }}>
                    <button
                        onClick={onClose}
                        className="px-4 py-2 rounded-md transition-colors"
                        style={{
                            borderWidth: '1px',
                            borderStyle: 'solid',
                            borderColor: 'var(--color-background-300)',
                            color: 'var(--color-text-700)',
                            backgroundColor: 'var(--color-background-100)'
                        }}
                        onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-200)'}
                        onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--color-background-100)'}
                    >
                        Cancel
                    </button>
                    <button
                        onClick={handleSaveAndClose}
                        className="px-4 py-2 bg-blue-600 text-white rounded-md transition-colors"
                        onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#2563eb'}
                        onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#3b82f6'}
                    >
                        Save & Close
                    </button>
                </div>
            </div>
        </div>
    );
};

export default SubtaskModal;
