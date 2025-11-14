/**
 * Validation utilities for task and workflow quality checking
 */

const ACTION_VERBS = [
    'create', 'design', 'implement', 'develop', 'build',
    'test', 'deploy', 'configure', 'setup', 'install',
    'review', 'analyze', 'research', 'document', 'write',
    'refactor', 'optimize', 'debug', 'fix', 'update',
    'integrate', 'migrate', 'validate', 'monitor', 'define'
];

/**
 * Validate a single task
 * @param {Object} task - Task object with name, description, estimatedHours
 * @returns {Object} { score: number (0-100), issues: Array, suggestions: Array }
 */
export function validateTask(task) {
    let score = 100;
    const issues = [];
    const suggestions = [];

    // Name clarity (30 pts)
    const nameWords = task.name?.trim().split(/\s+/) || [];
    if (nameWords.length < 3) {
        issues.push({
            type: 'vague_name',
            severity: 'high',
            message: 'Task name is too short or vague'
        });
        suggestions.push('Add more specific details to the task name');
        score -= 30;
    }

    // Description completeness (25 pts)
    if (!task.description || task.description.length < 20) {
        issues.push({
            type: 'missing_description',
            severity: 'medium',
            message: 'Task lacks detailed description'
        });
        suggestions.push('Add a detailed description explaining what needs to be done');
        score -= 25;
    }

    // Actionable verb (20 pts)
    const firstWord = nameWords[0]?.toLowerCase() || '';
    if (!ACTION_VERBS.includes(firstWord)) {
        issues.push({
            type: 'no_action_verb',
            severity: 'medium',
            message: 'Task name should start with an action verb'
        });
        suggestions.push(`Consider starting with: ${ACTION_VERBS.slice(0, 5).join(', ')}, etc.`);
        score -= 20;
    }

    // Time estimate (15 pts)
    if (!task.estimatedHours || task.estimatedHours === 0) {
        issues.push({
            type: 'no_time_estimate',
            severity: 'low',
            message: 'Task is missing time estimate'
        });
        suggestions.push('Add an estimated time to complete this task');
        score -= 15;
    }

    // Appropriate scope (10 pts)
    if (task.estimatedHours && task.estimatedHours > 40) {
        issues.push({
            type: 'task_too_large',
            severity: 'medium',
            message: 'Task may be too large (>40 hours)'
        });
        suggestions.push('Consider breaking this into smaller subtasks');
        score -= 10;
    }

    return {
        score: Math.max(0, score),
        issues,
        suggestions
    };
}

/**
 * Validate entire workflow
 * @param {Array} tasks - Array of all tasks
 * @param {Array} edges - Array of edges/connections
 * @returns {Object} { score: number, issues: Array, isValid: boolean }
 */
export function validateWorkflow(tasks, edges) {
    let score = 100;
    const issues = [];

    // Check for poorly defined tasks
    const poorlyDefinedTasks = tasks.filter(task => {
        const validation = validateTask(task);
        return validation.score < 50;
    });

    if (poorlyDefinedTasks.length > 0) {
        issues.push({
            type: 'poorly_defined_tasks',
            severity: 'high',
            message: `${poorlyDefinedTasks.length} tasks have quality scores below 50`,
            affectedTasks: poorlyDefinedTasks.map(t => t.id)
        });
        score -= 20;
    }

    // Check for HITL checkpoints
    const hitlTasks = tasks.filter(t => t.type === 'hitl');
    if (hitlTasks.length === 0 && tasks.length > 5) {
        issues.push({
            type: 'missing_hitl',
            severity: 'medium',
            message: 'No Human-in-the-Loop checkpoints found'
        });
        score -= 10;
    }

    // Check for too few tasks
    if (tasks.length < 3) {
        issues.push({
            type: 'too_few_tasks',
            severity: 'low',
            message: 'Project has very few tasks - consider adding more detail'
        });
        score -= 10;
    }

    // Check for too many tasks
    if (tasks.length > 50) {
        issues.push({
            type: 'too_many_tasks',
            severity: 'low',
            message: 'Project has many tasks - consider grouping into phases'
        });
        score -= 5;
    }

    // Check for orphaned tasks (no connections)
    const connectedTaskIds = new Set();
    edges.forEach(edge => {
        if (edge.source !== 'start' && edge.source !== 'end') {
            connectedTaskIds.add(edge.source);
        }
        if (edge.target !== 'start' && edge.target !== 'end') {
            connectedTaskIds.add(edge.target);
        }
    });

    const orphanedTasks = tasks.filter(task => !connectedTaskIds.has(task.id));
    if (orphanedTasks.length > 0) {
        issues.push({
            type: 'orphaned_tasks',
            severity: 'high',
            message: `${orphanedTasks.length} tasks are not connected to the workflow`,
            affectedTasks: orphanedTasks.map(t => t.id)
        });
        score -= 15;
    }

    // Check AI/Human balance
    const aiCount = tasks.filter(t => t.type === 'ai').length;
    const humanCount = tasks.filter(t => t.type === 'human').length;
    const ratio = aiCount / (humanCount || 1);

    if (ratio > 5 || ratio < 0.2) {
        issues.push({
            type: 'imbalanced_workflow',
            severity: 'low',
            message: 'Workflow may have imbalanced AI/Human task distribution'
        });
        score -= 5;
    }

    return {
        score: Math.max(0, score),
        issues,
        isValid: score >= 50
    };
}

/**
 * Get score label
 */
export function getScoreLabel(score) {
    if (score >= 90) return 'Excellent';
    if (score >= 70) return 'Good';
    if (score >= 50) return 'Needs Work';
    return 'Poor';
}

/**
 * Get score color (Tailwind class)
 */
export function getScoreColor(score) {
    if (score >= 90) return 'text-green-600';
    if (score >= 70) return 'text-blue-600';
    if (score >= 50) return 'text-orange-600';
    return 'text-red-600';
}
