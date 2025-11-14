import dagre from 'dagre';

const NODE_WIDTH = 200;
const NODE_HEIGHT = 80;
const START_END_SIZE = 96;

/**
 * Generate flowchart layout using Dagre algorithm
 * @param {Array} tasks - Array of task objects
 * @param {string} direction - 'horizontal' or 'vertical'
 * @returns {Object} { initialNodes, initialEdges }
 */
export function generateFlowchartLayout(tasks, direction = 'vertical') {
    const nodes = [];
    const edges = [];

    // Start node
    nodes.push({
        id: 'start',
        type: 'start',
        position: { x: 0, y: 0 },
        data: { label: 'Start' },
    });

    // Task nodes
    tasks.forEach((task, index) => {
        nodes.push({
            id: task.id || `task-${index}`,
            type: 'task',
            position: { x: 0, y: 0 }, // Will be positioned by Dagre
            data: {
                task: task,
                label: task.name,
                type: task.type,
                estimatedHours: task.estimated_hours,
                subtaskCount: task.subtasks?.length || 0,
            },
        });
    });

    // End node
    nodes.push({
        id: 'end',
        type: 'end',
        position: { x: 0, y: 0 },
        data: { label: 'End' },
    });

    // Generate edges based on dependencies or sequential order
    generateEdges(tasks, edges);

    // Apply Dagre layout
    const layoutedNodes = applyDagreLayout(nodes, edges, direction);

    return {
        initialNodes: layoutedNodes,
        initialEdges: edges,
    };
}

/**
 * Generate edges between nodes
 */
function generateEdges(tasks, edges) {
    // Connect start to first task(s)
    if (tasks.length > 0) {
        // Find tasks with no dependencies
        const rootTasks = tasks.filter(t => !t.dependencies || t.dependencies.length === 0);

        if (rootTasks.length > 0) {
            rootTasks.forEach(task => {
                edges.push({
                    id: `start-to-${task.id}`,
                    source: 'start',
                    target: task.id,
                    type: 'smoothstep',
                    animated: true,
                });
            });
        } else {
            // If all tasks have dependencies, connect to first task
            edges.push({
                id: `start-to-${tasks[0].id}`,
                source: 'start',
                target: tasks[0].id,
                type: 'smoothstep',
                animated: true,
            });
        }
    }

    // Connect tasks based on dependencies
    tasks.forEach((task, index) => {
        if (task.dependencies && task.dependencies.length > 0) {
            task.dependencies.forEach(depId => {
                edges.push({
                    id: `${depId}-to-${task.id}`,
                    source: depId,
                    target: task.id,
                    type: 'smoothstep',
                    animated: true,
                });
            });
        } else if (index > 0 && !rootTaskConnected(task, edges)) {
            // Sequential connection for tasks without explicit dependencies
            edges.push({
                id: `${tasks[index - 1].id}-to-${task.id}`,
                source: tasks[index - 1].id,
                target: task.id,
                type: 'smoothstep',
                animated: true,
            });
        }
    });

    // Connect last task(s) to end
    const leafTasks = tasks.filter(task => {
        return !tasks.some(t =>
            t.dependencies && t.dependencies.includes(task.id)
        );
    });

    if (leafTasks.length > 0) {
        leafTasks.forEach(task => {
            edges.push({
                id: `${task.id}-to-end`,
                source: task.id,
                target: 'end',
                type: 'smoothstep',
                animated: true,
            });
        });
    } else if (tasks.length > 0) {
        // Fallback: connect last task to end
        const lastTask = tasks[tasks.length - 1];
        edges.push({
            id: `${lastTask.id}-to-end`,
            source: lastTask.id,
            target: 'end',
            type: 'smoothstep',
            animated: true,
        });
    }
}

/**
 * Check if task is already connected as a root task
 */
function rootTaskConnected(task, edges) {
    return edges.some(edge => edge.target === task.id && edge.source === 'start');
}

/**
 * Apply Dagre layout algorithm to position nodes
 */
function applyDagreLayout(nodes, edges, direction) {
    const dagreGraph = new dagre.graphlib.Graph();
    dagreGraph.setDefaultEdgeLabel(() => ({}));

    dagreGraph.setGraph({
        rankdir: direction === 'horizontal' ? 'LR' : 'TB',
        nodesep: 100,
        ranksep: 150,
    });

    // Add nodes to graph
    nodes.forEach((node) => {
        const width = node.type === 'task' ? NODE_WIDTH : START_END_SIZE;
        const height = node.type === 'task' ? NODE_HEIGHT : START_END_SIZE;
        dagreGraph.setNode(node.id, { width, height });
    });

    // Add edges to graph
    edges.forEach((edge) => {
        dagreGraph.setEdge(edge.source, edge.target);
    });

    // Calculate layout
    dagre.layout(dagreGraph);

    // Apply calculated positions to nodes
    return nodes.map((node) => {
        const nodeWithPosition = dagreGraph.node(node.id);
        const width = node.type === 'task' ? NODE_WIDTH : START_END_SIZE;
        const height = node.type === 'task' ? NODE_HEIGHT : START_END_SIZE;

        return {
            ...node,
            position: {
                x: nodeWithPosition.x - width / 2,
                y: nodeWithPosition.y - height / 2,
            },
        };
    });
}
