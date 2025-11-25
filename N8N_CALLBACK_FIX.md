# n8n Workflow Callback Fix

## Problem Identified

The **"Send Callback to Laravel"** node in your n8n workflow has an incorrect payload structure that causes Laravel validation to fail.

### Current Configuration (BROKEN):
```json
{
  "project_id": "{{ $('Split Tasks').first().json.project_id }}",
  "task_id": "{{ $json.task_id }}",
  "task_type": "{{ $json.task_type }}",
  "status": "{{ $json.status }}",
  "execution_id": "{{ $execution.id }}",
  "workflow_name": "CollabFlow_Project_Orchestration",
  "result_data": {{ $json.toJsonString() }},
  "completed_at": "{{ $now.toISO() }}"
}
```

**Problem**: `result_data` is dumping the entire `$json` object, which includes duplicate fields and violates Laravel's validation rules.

## The Fix

### Step 1: Update the "Send Callback to Laravel" Node

1. Open your n8n workflow: **CollabFlow_Project_Orchestration**
2. Click on the **"Send Callback to Laravel"** node (node ID: `send-callback`)
3. In the **"Body Parameters"** section, change the **JSON Body** to:

```json
{
  "project_id": "{{ $('Split Tasks').first().json.project_id }}",
  "task_id": "{{ $json.task_id }}",
  "task_type": "{{ $json.task_type }}",
  "status": "completed",
  "execution_id": "{{ $execution.id }}",
  "result_data": {
    "workflow_name": "CollabFlow_Project_Orchestration",
    "execution_time": "{{ $json.execution_time || '' }}",
    "model_used": "{{ $json.model_used || '' }}",
    "confidence_score": "{{ $json.confidence_score || 0 }}",
    "assigned_to": "{{ $json.assigned_to || '' }}",
    "assigned_at": "{{ $json.assigned_at || '' }}",
    "notification_sent": {{ $json.notification_sent || false }},
    "notification_channels": {{ $json.notification_channels ? JSON.stringify($json.notification_channels) : '[]' }},
    "notification_required": {{ $json.notification_required || false }},
    "ai_portion": {{ $json.ai_portion ? JSON.stringify($json.ai_portion) : 'null' }},
    "human_portion": {{ $json.human_portion ? JSON.stringify($json.human_portion) : 'null' }},
    "checkpoint_subtasks": {{ $json.checkpoint_subtasks ? JSON.stringify($json.checkpoint_subtasks) : '[]' }},
    "total_checkpoints": {{ $json.total_checkpoints || 0 }},
    "workflow_steps": {{ $json.workflow_steps ? JSON.stringify($json.workflow_steps) : '[]' }},
    "priority": "{{ $json.priority || 'medium' }}",
    "estimated_hours": {{ $json.estimated_hours || 0 }},
    "completed_at": "{{ $now.toISO() }}"
  }
}
```

### Step 2: Key Changes Explained

| Field | Old Value | New Value | Why? |
|-------|-----------|-----------|------|
| `status` | `"{{ $json.status }}"` | `"completed"` | Always "completed" for callbacks (in_progress handled separately) |
| `result_data` | `{{ $json.toJsonString() }}` | Structured object | Only execution details, not control flow fields |

### Step 3: Alternative Simpler Version

If you want a simpler callback that works for all task types:

```json
{
  "project_id": "{{ $('Split Tasks').first().json.project_id }}",
  "task_id": "{{ $json.task_id }}",
  "task_type": "{{ $json.task_type }}",
  "status": "completed",
  "execution_id": "{{ $execution.id }}",
  "result_data": {
    "workflow_name": "CollabFlow_Project_Orchestration",
    "completed_at": "{{ $now.toISO() }}",
    "execution_summary": "Task completed successfully via n8n orchestration",
    "task_specific_data": {{ JSON.stringify($json) }}
  }
}
```

This version wraps all task-specific data inside `result_data.task_specific_data`.

## What Laravel Expects

Laravel's `OrchestrationController::callback()` validates:

```php
[
    'project_id' => 'required|uuid|exists:projects,id',
    'task_id' => 'required|uuid|exists:tasks,id',       // ✅ Top level
    'task_type' => 'required|string|in:ai,human,hitl',  // ✅ Top level
    'status' => 'required|string|in:completed,assigned,in_progress,failed',
    'execution_id' => 'required|string',
    'result_data' => 'required|array'                   // ✅ Must be object/array
]
```

**Critical Rules**:
1. `task_id`, `task_type`, `status`, `execution_id` must be at **top level**
2. `result_data` must be an **object** (not a string, not the entire payload)
3. `result_data` should contain **execution-specific details only**

## Testing the Fix

After updating the n8n workflow:

### 1. Deploy Changes
- Save the workflow in n8n
- Ensure it's activated

### 2. Test with Existing Project
```bash
# Check current state
curl https://collabflow-laravel-app.on-forge.com/api/projects/019abb15-91e9-7201-979c-38d7db69d9c0

# Let n8n process a task, then check again
curl https://collabflow-laravel-app.on-forge.com/api/projects/019abb15-91e9-7201-979c-38d7db69d9c0

# Verify:
# - total_orchestration_runs incremented (automatic re-trigger!)
# - One task status changed to "completed"
# - orchestration_status still "running" (until all tasks done)
```

### 3. Monitor Automatic Re-triggering
Run the monitoring script:
```powershell
cd D:\webdev\laravel-app
.\monitor-orchestration.ps1
```

You should see:
```
[N] Checking at HH:MM:SS...
  Orchestration Status: running
  Total Runs: 3  ← INCREMENTED!
  Tasks - Pending: 4 | In Progress: 0 | Completed: 1
  ✓ Completed Tasks:
    - Setup Development Environment (Type: human)
```

## Why This Fix Works

1. **Separates Control Flow from Data**: `task_id`, `task_type`, `status` are control flow fields that Laravel needs to route the callback correctly
2. **Wraps Execution Details**: `result_data` contains the actual execution results for audit trail
3. **Enables Automatic Loop**: With correct `task_id` and `task_type`, Laravel can dispatch the `TaskCompleted` event → `CheckForReadyTasks` listener → automatic re-trigger

## Additional Notes

### For Status Updates During Execution

If you want to send progress updates while a task is running (not just completion), use the separate endpoint:

```http
PATCH /api/orchestration/tasks/{taskId}/status
Content-Type: application/json

{
  "status": "in_progress",
  "execution_id": "{{ $execution.id }}",
  "progress": 50,
  "message": "Halfway done processing..."
}
```

This endpoint **does NOT** trigger re-orchestration (only `status: "completed"` does).

### Laravel Frontend Integration

Now that we've fixed both:
- ✅ Frontend task completion (dispatches TaskCompleted event)
- ✅ n8n callback format (correct payload structure)

Both flows will trigger automatic re-orchestration when `orchestration_status === 'running'`!

## Summary

**Before Fix**:
- n8n sends malformed callback → Laravel validation fails
- No TaskCompleted event dispatched
- No automatic re-triggering
- Manual intervention required

**After Fix**:
- n8n sends correct callback → Laravel validates successfully
- TaskCompleted event dispatched
- CheckForReadyTasks listener triggers n8n again
- Zero-intervention automatic loop works! 🎉
