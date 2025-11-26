// Use window.React ONLY (shared instance from vendor-react.js)
// CRITICAL: DO NOT import React directly - this would bundle React into the chunk!
const SharedReact = window.React;

// Extract hooks from SharedReact
const { useState, useMemo, useCallback, useEffect } = SharedReact;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window.');
    console.error('   Make sure vendor-react.js loads before flowchart code.');
    throw new Error('React not available - vendor-react.js must load first');
}

// Log React version being used
console.log('⚛️ FlowchartContainer using React', SharedReact.version);
console.log('   window.React instance:', window.React);
console.log('   Using shared instance?', window.React === SharedReact);

// React Flow can be imported normally (it will use window.React internally)
import {
    ReactFlow,
    Background,
    Controls,
    MiniMap,
    Panel,
    useNodesState,
    useEdgesState,
    useReactFlow,
    addEdge,
    getNodesBounds,
    getViewportForBounds,
    ReactFlowProvider,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';

// Debug: Check if React Flow is using the same React
console.log('   React Flow hooks:', { useNodesState, useEdgesState });
console.log('   Checking React internals...');

// Import custom components
import TaskNode from './nodes/TaskNode';
import StartNode from './nodes/StartNode';
import EndNode from './nodes/EndNode';
import FlowchartToolbar from './FlowchartToolbar';
import TaskDetailsPanel from './TaskDetailsPanel';
import ContextMenu from './ContextMenu';
import ValidationPanel from './ValidationPanel';
import SubtaskModal from './SubtaskModal';
import { generateFlowchartLayout } from './utils/layoutUtils';
import { validateWorkflow } from './utils/validationUtils';

/**
 * FlowchartContainerInner - Inner component with React Flow hooks
 * Must be wrapped by ReactFlowProvider to avoid React error #185
 */
const FlowchartContainerInner = ({
    tasks = [],
    onTasksChange,
    readOnly = false,
    layoutDirection: initialLayoutDirection = 'vertical',
    hideValidationPanel = false,
    selectedTaskId = null,
    onNodeClick: onNodeClickCallback
}) => {
    // Local state for layout direction (can be toggled independently)
    const [layoutDirection, setLayoutDirection] = useState(initialLayoutDirection);

    // State for selected task (for TaskDetailsPanel)
    const [selectedTask, setSelectedTask] = useState(null);

    // State for context menu
    const [contextMenu, setContextMenu] = useState(null); // { x, y, taskId }

    // State for subtask modal
    const [subtaskModalTask, setSubtaskModalTask] = useState(null);

    // Handle subtask click from TaskNode badge (defined early so it can be used in useMemo)
    const handleSubtaskClick = useCallback((nodeData) => {
        console.log('Subtask badge clicked:', nodeData);

        // Find the full task object from tasks array
        const task = tasks.find(t => t.id === nodeData.task?.id);
        if (task) {
            setSubtaskModalTask(task);
        }
    }, [tasks]);

    // Define custom node types (stable, never changes)
    const nodeTypes = useMemo(
        () => ({
            start: StartNode,
            task: TaskNode,
            end: EndNode,
        }),
        []
    );

    // Generate initial layout from tasks
    const { initialNodes, initialEdges } = useMemo(() => {
        const layout = generateFlowchartLayout(tasks, layoutDirection);

        // Enhance task nodes with onSubtaskClick callback
        const enhancedNodes = layout.initialNodes.map(node => {
            if (node.type === 'task') {
                return {
                    ...node,
                    data: {
                        ...node.data,
                        onSubtaskClick: handleSubtaskClick,
                    },
                };
            }
            return node;
        });

        return {
            initialNodes: enhancedNodes,
            initialEdges: layout.initialEdges,
        };
    }, [tasks, layoutDirection, handleSubtaskClick]);

    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);

    // Get React Flow instance for export functionality
    const { getNodes } = useReactFlow();

    // Calculate workflow validation
    const validation = useMemo(() => {
        return validateWorkflow(tasks, edges);
    }, [tasks, edges]);

    // CRITICAL: Update internal state when tasks prop changes from outside
    useEffect(() => {
        const layout = generateFlowchartLayout(tasks, layoutDirection);

        // Enhance task nodes with onSubtaskClick callback
        const enhancedNodes = layout.initialNodes.map(node => {
            if (node.type === 'task') {
                return {
                    ...node,
                    data: {
                        ...node.data,
                        onSubtaskClick: handleSubtaskClick,
                    },
                };
            }
            return node;
        });

        setNodes(enhancedNodes);
        setEdges(layout.initialEdges);
    }, [tasks, layoutDirection, handleSubtaskClick]);

    // Handle node click
    const onNodeClick = useCallback((event, node) => {
        console.log('Node clicked:', node);

        // Close context menu if open
        setContextMenu(null);

        // Call the external callback if provided (for bidirectional sync)
        if (onNodeClickCallback && node.type === 'task') {
            onNodeClickCallback(node.id);
        }

        // Only open details panel for task nodes (not start/end)
        if (node.type === 'task') {
            // Find the task from the tasks array
            const task = tasks.find(t => t.id === node.id);
            if (task) {
                setSelectedTask(task);
            }
        }

        // Call optional callback if provided
        if (window.onFlowchartTaskClick) {
            window.onFlowchartTaskClick(node.id);
        }
    }, [tasks, onNodeClickCallback]);

    // Handle node right-click (context menu)
    const onNodeContextMenu = useCallback((event, node) => {
        event.preventDefault();

        // Only show context menu for task nodes (not start/end)
        if (node.type === 'task') {
            console.log('Node right-clicked:', node);

            setContextMenu({
                x: event.clientX,
                y: event.clientY,
                taskId: node.id,
            });
        }
    }, []);

    // Handle manual connection creation
    const onConnect = useCallback((connection) => {
        if (readOnly) return;

        console.log('Connection created:', connection);

        // Validate connection (prevent connecting to start, prevent self-loops)
        if (connection.target === 'start' || connection.source === connection.target) {
            console.warn('Invalid connection attempt');
            return;
        }

        // Add the new edge
        setEdges((eds) => addEdge(connection, eds));

        // Persist to Livewire
        if (onTasksChange) {
            const newEdge = {
                id: `${connection.source}-${connection.target}`,
                source: connection.source,
                target: connection.target,
                type: 'default',
            };

            const workflowState = {
                nodes: nodes.map(n => ({
                    id: n.id,
                    type: n.type,
                    position: n.position,
                    data: n.data,
                })),
                edges: [...edges, newEdge].map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type || 'default',
                })),
                lastUpdated: new Date().toISOString(),
            };
            onTasksChange(workflowState);
        }
    }, [readOnly, nodes, edges, setEdges, onTasksChange]);

    // Handle node drag end - save positions
    const onNodeDragStop = useCallback((event, node) => {
        if (readOnly || !onTasksChange) return;

        console.log('Node drag stopped:', node.id, node.position);

        // Build complete workflow state with current nodes and edges
        const workflowState = {
            nodes: nodes.map(n => ({
                id: n.id,
                type: n.type,
                position: n.position,
                data: n.data,
            })),
            edges: edges.map(e => ({
                id: e.id,
                source: e.source,
                target: e.target,
                type: e.type,
            })),
            lastUpdated: new Date().toISOString(),
        };

        console.log('Sending workflow state to Alpine/Livewire:', workflowState);

        // Call the callback to persist to Livewire
        onTasksChange(workflowState);
    }, [nodes, edges, readOnly, onTasksChange]);

    // Handle auto-layout button click
    const handleAutoLayout = useCallback(() => {
        console.log('Auto-layout triggered');

        // Re-generate layout with current tasks and direction
        const { initialNodes: newNodes, initialEdges: newEdges } = generateFlowchartLayout(tasks, layoutDirection);

        setNodes(newNodes);
        setEdges(newEdges);

        // Persist the new layout
        if (onTasksChange) {
            const workflowState = {
                nodes: newNodes.map(n => ({
                    id: n.id,
                    type: n.type,
                    position: n.position,
                    data: n.data,
                })),
                edges: newEdges.map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type,
                })),
                lastUpdated: new Date().toISOString(),
            };
            onTasksChange(workflowState);
        }
    }, [tasks, layoutDirection, setNodes, setEdges, onTasksChange]);

    // Handle layout direction toggle
    const handleToggleLayout = useCallback(() => {
        const newDirection = layoutDirection === 'horizontal' ? 'vertical' : 'horizontal';
        console.log('Layout direction toggled:', layoutDirection, '->', newDirection);

        setLayoutDirection(newDirection);

        // Re-generate layout with new direction
        const { initialNodes: newNodes, initialEdges: newEdges } = generateFlowchartLayout(tasks, newDirection);

        setNodes(newNodes);
        setEdges(newEdges);

        // Persist the new layout
        if (onTasksChange) {
            const workflowState = {
                nodes: newNodes.map(n => ({
                    id: n.id,
                    type: n.type,
                    position: n.position,
                    data: n.data,
                })),
                edges: newEdges.map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type,
                })),
                lastUpdated: new Date().toISOString(),
            };
            onTasksChange(workflowState);
        }
    }, [layoutDirection, tasks, setNodes, setEdges, onTasksChange]);

    // Handle export
    const handleExport = useCallback(async (format) => {
        console.log('Exporting flowchart as:', format);

        if (format === 'json') {
            // Export as JSON
            const exportData = {
                nodes: nodes.map(n => ({
                    id: n.id,
                    type: n.type,
                    position: n.position,
                    data: {
                        label: n.data.label,
                        type: n.data.type,
                        estimatedHours: n.data.estimatedHours,
                        description: n.data.description,
                        subtaskCount: n.data.subtaskCount,
                    },
                })),
                edges: edges.map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type,
                })),
                layoutDirection,
                exportedAt: new Date().toISOString(),
            };

            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `workflow-${Date.now()}.json`;
            link.click();
            URL.revokeObjectURL(url);
        } else if (format === 'png' || format === 'svg' || format === 'pdf') {
            // Export as image using html-to-image library
            try {
                const { toPng, toSvg } = await import('html-to-image');

                // Find the React Flow viewport element
                const viewport = document.querySelector('.react-flow__viewport');
                if (!viewport) {
                    console.error('Viewport not found');
                    return;
                }

                // Get the bounds of all nodes
                const nodesBounds = getNodesBounds(getNodes());
                const imageWidth = nodesBounds.width + 100; // Add padding
                const imageHeight = nodesBounds.height + 100;

                if (format === 'pdf') {
                    // Export as PDF
                    const { jsPDF } = await import('jspdf');

                    // First, generate PNG image data
                    const dataUrl = await toPng(viewport, {
                        backgroundColor: '#f9fafb',
                        width: imageWidth,
                        height: imageHeight,
                        style: {
                            width: `${imageWidth}px`,
                            height: `${imageHeight}px`,
                        },
                    });

                    // Determine PDF orientation based on flowchart dimensions
                    const orientation = imageWidth > imageHeight ? 'landscape' : 'portrait';

                    // Create PDF with appropriate dimensions
                    // Use A4 size as base, but scale to fit content
                    const pdf = new jsPDF({
                        orientation,
                        unit: 'px',
                        format: [imageWidth, imageHeight],
                    });

                    // Add the image to PDF
                    pdf.addImage(dataUrl, 'PNG', 0, 0, imageWidth, imageHeight);

                    // Download the PDF
                    pdf.save(`workflow-${Date.now()}.pdf`);
                } else {
                    // Export as PNG or SVG
                    const exportFunc = format === 'png' ? toPng : toSvg;

                    const dataUrl = await exportFunc(viewport, {
                        backgroundColor: '#f9fafb',
                        width: imageWidth,
                        height: imageHeight,
                        style: {
                            width: `${imageWidth}px`,
                            height: `${imageHeight}px`,
                        },
                    });

                    const link = document.createElement('a');
                    link.download = `workflow-${Date.now()}.${format}`;
                    link.href = dataUrl;
                    link.click();
                }
            } catch (error) {
                console.error(`Error exporting as ${format}:`, error);
                alert(`Export failed. ${format.toUpperCase()} export requires additional setup.`);
            }
        }
    }, [nodes, edges, layoutDirection, getNodes]);

    // Handle fullscreen toggle
    const handleFullscreen = useCallback(() => {
        const container = document.getElementById('flowchart-wrapper-step3');
        if (!container) {
            console.error('Flowchart wrapper not found');
            return;
        }

        if (!document.fullscreenElement) {
            // Enter fullscreen
            container.requestFullscreen().catch((err) => {
                console.error(`Error attempting to enable fullscreen: ${err.message}`);
            });
        } else {
            // Exit fullscreen
            document.exitFullscreen();
        }
    }, []);

    // Handle task update from TaskDetailsPanel
    const handleTaskUpdate = useCallback((updatedTask) => {
        console.log('Task updated:', updatedTask);

        // Update the node data
        setNodes((nds) =>
            nds.map((node) => {
                if (node.id === updatedTask.id) {
                    return {
                        ...node,
                        data: {
                            ...node.data,
                            ...updatedTask,
                        },
                    };
                }
                return node;
            })
        );

        // Update selectedTask state to reflect changes
        setSelectedTask(updatedTask);

        // Persist to Livewire
        if (onTasksChange) {
            const workflowState = {
                nodes: nodes.map(n => {
                    if (n.id === updatedTask.id) {
                        return {
                            id: n.id,
                            type: n.type,
                            position: n.position,
                            data: { ...n.data, ...updatedTask },
                        };
                    }
                    return {
                        id: n.id,
                        type: n.type,
                        position: n.position,
                        data: n.data,
                    };
                }),
                edges: edges.map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type,
                })),
                lastUpdated: new Date().toISOString(),
            };
            onTasksChange(workflowState);
        }
    }, [nodes, edges, setNodes, onTasksChange]);

    // Handle task deletion from TaskDetailsPanel
    const handleTaskDelete = useCallback((taskId) => {
        console.log('Task deleted:', taskId);

        // Check for dependent tasks (tasks that depend on this one)
        const dependentEdges = edges.filter(e => e.source === taskId && e.target !== 'end');
        const dependentTasks = dependentEdges.map(e => {
            const node = nodes.find(n => n.id === e.target);
            return node?.data?.label || node?.data?.name || 'Unknown task';
        });

        // Warn user if there are dependent tasks
        if (dependentTasks.length > 0) {
            const taskNames = dependentTasks.join(', ');
            const confirmed = confirm(
                `Warning: Deleting this task will break dependencies!\n\n` +
                `The following tasks depend on this one:\n${taskNames}\n\n` +
                `These connections will be removed. Continue with deletion?`
            );

            if (!confirmed) {
                console.log('Task deletion cancelled by user');
                return;
            }
        }

        // Remove node and connected edges
        setNodes((nds) => nds.filter((node) => node.id !== taskId));
        setEdges((eds) => eds.filter((edge) => edge.source !== taskId && edge.target !== taskId));

        // Close panel
        setSelectedTask(null);

        // Persist to Livewire
        if (onTasksChange) {
            const workflowState = {
                nodes: nodes.filter(n => n.id !== taskId).map(n => ({
                    id: n.id,
                    type: n.type,
                    position: n.position,
                    data: n.data,
                })),
                edges: edges.filter(e => e.source !== taskId && e.target !== taskId).map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type,
                })),
                lastUpdated: new Date().toISOString(),
            };
            onTasksChange(workflowState);
        }
    }, [nodes, edges, setNodes, setEdges, onTasksChange]);

    // Handle view subtasks - open SubtaskModal
    const handleViewSubtasks = useCallback(() => {
        console.log('View subtasks clicked for task:', selectedTask?.id);
        if (selectedTask) {
            setSubtaskModalTask(selectedTask);
        }
    }, [selectedTask]);

    // Handle update subtasks from SubtaskModal
    const handleUpdateSubtasks = useCallback((updatedSubtasks) => {
        console.log('Subtasks updated:', updatedSubtasks);

        if (!subtaskModalTask) return;

        // Update the task in our tasks array
        const updatedTask = { ...subtaskModalTask, subtasks: updatedSubtasks };

        // Update node data
        setNodes((nds) =>
            nds.map((node) => {
                if (node.id === subtaskModalTask.id) {
                    return {
                        ...node,
                        data: {
                            ...node.data,
                            subtasks: updatedSubtasks,
                            subtaskCount: updatedSubtasks.length,
                        },
                    };
                }
                return node;
            })
        );

        // If the task is currently selected in the details panel, update it
        if (selectedTask?.id === subtaskModalTask.id) {
            setSelectedTask(updatedTask);
        }

        // Persist to Livewire
        if (onTasksChange) {
            const workflowState = {
                nodes: nodes.map(n => {
                    if (n.id === subtaskModalTask.id) {
                        return {
                            id: n.id,
                            type: n.type,
                            position: n.position,
                            data: { ...n.data, subtasks: updatedSubtasks, subtaskCount: updatedSubtasks.length },
                        };
                    }
                    return {
                        id: n.id,
                        type: n.type,
                        position: n.position,
                        data: n.data,
                    };
                }),
                edges: edges.map(e => ({
                    id: e.id,
                    source: e.source,
                    target: e.target,
                    type: e.type,
                })),
                lastUpdated: new Date().toISOString(),
            };
            onTasksChange(workflowState);
        }
    }, [subtaskModalTask, selectedTask, nodes, edges, setNodes, onTasksChange]);

    // Context menu handlers
    const handleContextMenuEdit = useCallback(() => {
        if (!contextMenu) return;

        const task = tasks.find(t => t.id === contextMenu.taskId);
        if (task) {
            setSelectedTask(task);
        }
    }, [contextMenu, tasks]);

    const handleContextMenuDelete = useCallback(() => {
        if (!contextMenu) return;

        handleTaskDelete(contextMenu.taskId);
    }, [contextMenu, handleTaskDelete]);

    const handleContextMenuViewSubtasks = useCallback(() => {
        if (!contextMenu) return;

        const task = tasks.find(t => t.id === contextMenu.taskId);
        if (task) {
            setSubtaskModalTask(task);
        }
    }, [contextMenu, tasks]);

    return (
        <div className="w-full rounded-xl overflow-hidden flex flex-col" style={{
            backgroundColor: 'var(--color-background-50)',
            borderWidth: '2px',
            borderStyle: 'solid',
            borderColor: 'var(--color-background-300)'
        }}>
            {/* Flowchart Area */}
            <div className="h-[500px] relative">
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    onNodesChange={onNodesChange}
                    onEdgesChange={onEdgesChange}
                    onConnect={onConnect}
                    onNodeClick={onNodeClick}
                    onNodeContextMenu={onNodeContextMenu}
                    onNodeDragStop={onNodeDragStop}
                    nodeTypes={nodeTypes}
                    nodesDraggable={!readOnly}
                    nodesConnectable={!readOnly}
                    elementsSelectable={!readOnly}
                    fitView
                    minZoom={0.5}
                    maxZoom={1.5}
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
                    {!readOnly && (
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
                    )}

                    {/* Toolbar Panel - Top Left */}
                    <Panel position="top-left">
                        <FlowchartToolbar
                            onAutoLayout={handleAutoLayout}
                            onToggleLayout={handleToggleLayout}
                            onExport={handleExport}
                            onFullscreen={handleFullscreen}
                            layoutDirection={layoutDirection}
                            readOnly={readOnly}
                        />
                    </Panel>
                </ReactFlow>

                {/* Task Details Panel - Right Side (conditionally rendered) */}
                {selectedTask && (
                    <TaskDetailsPanel
                        task={selectedTask}
                        onClose={() => setSelectedTask(null)}
                        onUpdate={handleTaskUpdate}
                        onDelete={handleTaskDelete}
                        onViewSubtasks={selectedTask.subtasks && selectedTask.subtasks.length > 0 ? handleViewSubtasks : undefined}
                    />
                )}

                {/* Context Menu (conditionally rendered) */}
                {contextMenu && (
                    <ContextMenu
                        x={contextMenu.x}
                        y={contextMenu.y}
                        onEdit={handleContextMenuEdit}
                        onDelete={handleContextMenuDelete}
                        onViewSubtasks={
                            tasks.find(t => t.id === contextMenu.taskId)?.subtasks?.length > 0
                                ? handleContextMenuViewSubtasks
                                : undefined
                        }
                        onClose={() => setContextMenu(null)}
                    />
                )}
            </div>

            {/* Validation Panel - Bottom (hidden in preview mode) */}
            {!hideValidationPanel && <ValidationPanel validation={validation} />}

            {/* Subtask Modal (conditionally rendered) */}
            {subtaskModalTask && (
                <SubtaskModal
                    task={subtaskModalTask}
                    onClose={() => setSubtaskModalTask(null)}
                    onUpdateSubtasks={handleUpdateSubtasks}
                />
            )}
        </div>
    );
};

/**
 * FlowchartContainer - Wrapper component with ReactFlowProvider
 *
 * This is a "React Island" - it receives data from Livewire via Alpine.js
 * and renders the interactive flowchart. It does NOT manage data persistence.
 *
 * @param {Object} props
 * @param {Array} props.tasks - Array of task objects from Livewire
 * @param {Function} props.onTasksChange - Callback to Alpine when tasks change
 * @param {boolean} props.readOnly - Whether the flowchart is editable
 * @param {string} props.layoutDirection - 'horizontal' or 'vertical'
 * @param {boolean} props.hideValidationPanel - Whether to hide the validation panel
 * @param {string} props.selectedTaskId - The ID of the currently selected task
 * @param {Function} props.onNodeClick - Callback when a node is clicked
 */
const FlowchartContainer = (props) => {
    return (
        <ReactFlowProvider>
            <FlowchartContainerInner {...props} />
        </ReactFlowProvider>
    );
};

export default FlowchartContainer;
