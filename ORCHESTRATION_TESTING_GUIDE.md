# Automatic Orchestration System - Testing Guide

## Overview

This guide provides step-by-step instructions for testing the complete automatic orchestration system on your deployed Laravel application. The system enables zero-intervention project execution through automatic task orchestration via n8n.

## Prerequisites

- Deployed Laravel application (collabflow-n8n.cloud)
- n8n instance configured and running
- API access (no authentication required for these endpoints)
- HTTP client (curl, Postman, or similar)
- Access to Laravel logs for monitoring

## System Architecture

```
User → POST /api/projects/{id}/start
  ↓
ProjectStarted Event
  ↓
TriggerOrchestration Listener → n8n Webhook
  ↓
n8n → GET /api/projects/{id}/ready-tasks
  ↓
n8n executes tasks → POST /api/orchestration/callback
  ↓
TaskCompleted Event → CheckForReadyTasks Listener
  ↓
If more tasks ready → Triggers n8n again (LOOP)
If orchestration complete → STOP
```

## Test Environment Setup

### 1. Database Preparation

Ensure migration has been run:
```bash
php artisan migrate
```

Verify these columns exist in `projects` table:
- orchestration_status
- orchestration_started_at
- orchestration_completed_at
- last_n8n_execution_id
- total_orchestration_runs
- orchestration_metadata

### 2. Configuration Verification

Check `.env` file has:
```env
N8N_WEBHOOK_URL=https://n8n.collabflow-n8n.cloud/webhook/project/orchestration
N8N_TIMEOUT=10
N8N_MAX_RETRIES=3
N8N_RETRY_DELAY=2
```

Clear caches:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 3. Create Test Project

Use the seeder command:
```bash
php artisan seed:demo-project --force
```

This creates a project with:
- 10 tasks with proper dependencies
- Mix of AI, Human, and HITL task types
- Realistic dependency chains
- Status: draft (ready to start)

## Testing Scenarios

### **Test 1: Get Project Information**

**Purpose:** Verify project exists and is in correct state.

**Request:**
```bash
curl -X GET https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid-here",
    "name": "Demo Project",
    "status": "draft",
    "orchestration_status": "not_started",
    "total_orchestration_runs": 0,
    ...
  }
}
```

**Validation:**
- ✅ Status is "draft"
- ✅ orchestration_status is "not_started"
- ✅ total_orchestration_runs is 0

---

### **Test 2: Get All Project Tasks**

**Purpose:** Verify tasks exist with correct dependencies.

**Request:**
```bash
curl -X GET https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}/tasks
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "task-uuid-1",
      "name": "Task 1",
      "type": "ai",
      "status": "pending",
      "dependencies": null,
      "sequence": 1
    },
    {
      "id": "task-uuid-2",
      "name": "Task 2",
      "type": "ai",
      "status": "pending",
      "dependencies": ["task-uuid-1"],
      "sequence": 2
    }
    ...
  ],
  "count": 10
}
```

**Validation:**
- ✅ All tasks have status "pending"
- ✅ Dependencies are correctly structured
- ✅ Task types include ai, human, and hitl

---

### **Test 3: Get Ready Tasks (Before Start)**

**Purpose:** Verify dependency resolution logic works.

**Request:**
```bash
curl -X GET https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}/ready-tasks
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "task-uuid-1",
      "name": "Task 1",
      "type": "ai",
      "status": "pending",
      "dependencies": null
    },
    {
      "id": "task-uuid-3",
      "name": "Task 3",
      "type": "ai",
      "status": "pending",
      "dependencies": null
    }
  ],
  "count": 2,
  "metadata": {
    "total_pending": 10,
    "batch_limits": {
      "ai_parallel": 2,
      "human_parallel": 1,
      "hitl_parallel": 1
    }
  }
}
```

**Validation:**
- ✅ Only returns tasks with no dependencies or completed dependencies
- ✅ Respects batch limits (max 2 AI tasks)
- ✅ Tasks are sorted by sequence

---

### **Test 4: Start Project Orchestration**

**Purpose:** Trigger automatic orchestration loop.

**Request:**
```bash
curl -X POST https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}/start \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Project started successfully",
  "project": {
    "id": "uuid-here",
    "status": "active",
    "orchestration_status": "running",
    "orchestration_started_at": "2025-11-24T14:30:00.000000Z",
    "total_orchestration_runs": 1
  }
}
```

**Validation:**
- ✅ Status changed to "active"
- ✅ orchestration_status changed to "running"
- ✅ orchestration_started_at is set
- ✅ n8n webhook should be called (check n8n logs)

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log | grep "Triggering n8n workflow"
```

**Expected Log:**
```
[timestamp] local.INFO: Triggering n8n workflow {"project_id":"uuid","project_name":"Demo Project","webhook_url":"..."}
[timestamp] local.INFO: n8n workflow triggered successfully {"project_id":"uuid","total_runs":1}
```

---

### **Test 5: Task Status Update (Progress)**

**Purpose:** Test real-time progress updates during execution.

**Request:**
```bash
curl -X PATCH https://collabflow-n8n.cloud/api/orchestration/tasks/{TASK_ID}/status \
  -H "Content-Type: application/json" \
  -d '{
    "status": "in_progress",
    "execution_id": "n8n-exec-123",
    "progress": 50,
    "message": "Processing document analysis..."
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Task status updated",
  "task_id": "uuid-here",
  "status": "in_progress"
}
```

**Validation:**
- ✅ Task status updated to "in_progress"
- ✅ Progress tracked in metadata
- ✅ No TaskCompleted event triggered (only on "completed" status)

---

### **Test 6: Task Completion Callback**

**Purpose:** Test automatic re-triggering when tasks complete.

**Request:**
```bash
curl -X POST https://collabflow-n8n.cloud/api/orchestration/callback \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": "PROJECT_UUID",
    "task_id": "TASK_UUID",
    "task_type": "ai",
    "status": "completed",
    "execution_id": "n8n-exec-123",
    "result_data": {
      "output": "Analysis complete",
      "confidence": 0.95
    }
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Callback received and processed",
  "project_id": "uuid-here",
  "task_id": "uuid-here"
}
```

**Validation:**
- ✅ Task status updated to "completed"
- ✅ Task metadata includes result_data
- ✅ TaskCompleted event dispatched
- ✅ CheckForReadyTasks listener runs
- ✅ If more ready tasks exist, n8n triggered again

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log | grep -E "Task callback processed|Ready tasks found"
```

**Expected Log:**
```
[timestamp] local.INFO: Task callback processed {"project_id":"uuid","task_id":"uuid","status":"completed"}
[timestamp] local.INFO: Ready tasks found - triggering n8n again {"project_id":"uuid","ready_count":2}
[timestamp] local.INFO: Triggering n8n workflow {"project_id":"uuid",...}
```

---

### **Test 7: Pause Orchestration**

**Purpose:** Test manual pause control.

**Request:**
```bash
curl -X POST https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}/pause
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Orchestration paused successfully",
  "project": {
    "orchestration_status": "paused"
  }
}
```

**Validation:**
- ✅ orchestration_status changed to "paused"
- ✅ Subsequent n8n triggers are skipped
- ✅ In-progress tasks continue but no new triggers

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log | grep "Orchestration paused"
```

---

### **Test 8: Resume Orchestration**

**Purpose:** Test resume after pause.

**Request:**
```bash
curl -X POST https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}/resume
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Orchestration resumed successfully",
  "project": {
    "orchestration_status": "running"
  }
}
```

**Validation:**
- ✅ orchestration_status changed to "running"
- ✅ n8n workflow triggered immediately
- ✅ Orchestration loop continues

---

### **Test 9: Complete Orchestration**

**Purpose:** Verify automatic completion detection.

**Scenario:** After all tasks are completed (via callbacks).

**Request:**
```bash
curl -X GET https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "orchestration_status": "completed",
    "orchestration_completed_at": "2025-11-24T15:00:00.000000Z",
    "total_orchestration_runs": 5
  }
}
```

**Validation:**
- ✅ orchestration_status is "completed"
- ✅ orchestration_completed_at is set
- ✅ No more n8n triggers
- ✅ All tasks have status "completed"

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log | grep "orchestration complete"
```

---

### **Test 10: Error Handling & Retry**

**Purpose:** Test automatic retry on n8n connection failures.

**Simulation:** Temporarily disable n8n or use invalid webhook URL.

**Setup:**
```env
N8N_WEBHOOK_URL=https://invalid-url-for-testing.com/webhook
```

**Request:**
```bash
curl -X POST https://collabflow-n8n.cloud/api/projects/{PROJECT_ID}/start
```

**Expected Behavior:**
- Laravel attempts 3 retries (configurable)
- Each retry waits 2 seconds (configurable)
- After max retries, orchestration_status set to "failed"

**Check Laravel Logs:**
```bash
tail -f storage/logs/laravel.log | grep -E "Triggering n8n|failed|attempt"
```

**Expected Log:**
```
[timestamp] local.INFO: Triggering n8n workflow {"attempt":1}
[timestamp] local.ERROR: n8n workflow trigger failed {"attempt":1}
[timestamp] local.INFO: Triggering n8n workflow {"attempt":2}
[timestamp] local.ERROR: n8n workflow trigger failed {"attempt":2}
[timestamp] local.INFO: Triggering n8n workflow {"attempt":3}
[timestamp] local.ERROR: n8n workflow trigger failed {"attempt":3}
[timestamp] local.CRITICAL: Project orchestration failed after max retries {"max_retries":3}
```

**Validation:**
- ✅ Retries exactly 3 times
- ✅ orchestration_status set to "failed"
- ✅ Error details stored in orchestration_metadata

---

## Complete Integration Test Flow

### Full End-to-End Test

1. **Create Demo Project**
   ```bash
   php artisan seed:demo-project --force
   ```

2. **Get Ready Tasks** (should return tasks with no dependencies)
   ```bash
   curl -X GET https://collabflow-n8n.cloud/api/projects/{ID}/ready-tasks
   ```

3. **Start Orchestration**
   ```bash
   curl -X POST https://collabflow-n8n.cloud/api/projects/{ID}/start
   ```

4. **Monitor n8n execution** - n8n should:
   - Receive webhook trigger
   - Call /api/projects/{id}/ready-tasks
   - Execute returned tasks
   - Send callbacks for each completed task

5. **Simulate Task Completion**
   For each ready task:
   ```bash
   curl -X POST https://collabflow-n8n.cloud/api/orchestration/callback \
     -H "Content-Type: application/json" \
     -d '{
       "project_id": "PROJECT_ID",
       "task_id": "TASK_ID",
       "task_type": "ai",
       "status": "completed",
       "execution_id": "test-exec-1",
       "result_data": {"output": "Task completed"}
     }'
   ```

6. **Verify Auto-Retriggering**
   - Check logs for "Ready tasks found - triggering n8n again"
   - Verify n8n receives new webhook call
   - Verify new batch of ready tasks returned

7. **Repeat Until Complete**
   Continue completing tasks until no more ready tasks exist

8. **Verify Completion**
   ```bash
   curl -X GET https://collabflow-n8n.cloud/api/projects/{ID}
   ```
   Should show:
   - `orchestration_status`: "completed"
   - `orchestration_completed_at`: timestamp
   - All tasks with status "completed"

---

## Monitoring & Debugging

### Log Monitoring Commands

**Real-time orchestration flow:**
```bash
tail -f storage/logs/laravel.log | grep -E "Trigger|callback|Ready tasks|orchestration"
```

**Error tracking:**
```bash
tail -f storage/logs/laravel.log | grep ERROR
```

**Event dispatching:**
```bash
tail -f storage/logs/laravel.log | grep "event"
```

### Database Queries for Verification

**Check project orchestration status:**
```sql
SELECT id, name, status, orchestration_status,
       orchestration_started_at, orchestration_completed_at,
       total_orchestration_runs
FROM projects
WHERE id = 'PROJECT_UUID';
```

**Check task statuses:**
```sql
SELECT id, name, type, status, sequence, dependencies
FROM tasks
WHERE project_id = 'PROJECT_UUID'
ORDER BY sequence;
```

**Check orchestration metadata:**
```sql
SELECT orchestration_metadata
FROM projects
WHERE id = 'PROJECT_UUID';
```

---

## Validation Checklist

After completing all tests, verify:

- [ ] ProjectStarted event triggers n8n webhook
- [ ] Ready tasks endpoint returns correct dependency-resolved tasks
- [ ] Task batching limits work (2 AI, 1 Human, 1 HITL)
- [ ] Task callbacks update status and metadata
- [ ] TaskCompleted event triggers CheckForReadyTasks listener
- [ ] Automatic re-triggering works when more tasks are ready
- [ ] Orchestration stops when all tasks complete
- [ ] Pause/resume functionality works correctly
- [ ] Retry logic attempts correct number of times
- [ ] Failed orchestrations marked with "failed" status
- [ ] Progress updates tracked in task metadata
- [ ] All logs show correct information flow

---

## Common Issues & Solutions

### Issue: n8n webhook not triggered

**Check:**
- N8N_WEBHOOK_URL is correct in .env
- Config cache cleared: `php artisan config:clear`
- EventServiceProvider registered in bootstrap/providers.php
- Check Laravel logs for HTTP errors

### Issue: No tasks returned from /ready-tasks

**Check:**
- All dependent tasks are marked "completed"
- Tasks have status "pending"
- Dependencies field is correctly formatted JSON array
- Task sequence is set correctly

### Issue: TaskCompleted event not triggering re-execution

**Check:**
- Event listener registered in EventServiceProvider
- Callback sends status as "completed" (not "in_progress")
- CheckForReadyTasks listener code is correct
- Event dispatching in callback controller

### Issue: Orchestration stuck in "running"

**Check:**
- Are there tasks stuck in "in_progress"?
- Check for failed n8n executions
- Verify callback endpoint is being called
- Check Laravel logs for exceptions

---

## Performance Metrics

Track these metrics during testing:

- **Orchestration Loop Speed:** Time between task completion and next n8n trigger
- **Task Batching Efficiency:** Number of parallel tasks vs sequential
- **Retry Success Rate:** Successful triggers after retry vs failed
- **Complete Orchestration Time:** Start to completion for 10-task project

Expected benchmarks:
- Loop trigger: < 1 second
- Batch optimization: 40-60% reduction in total time vs sequential
- Retry success: > 95% on transient failures

---

## Security Considerations

For production deployment:

1. **Add API Authentication**
   - Implement token-based auth for project endpoints
   - Allow n8n webhook URL in CORS
   - Use API keys for n8n → Laravel communication

2. **Rate Limiting**
   - Add throttling to orchestration endpoints
   - Prevent abuse of start/pause/resume

3. **Input Validation**
   - Validate all UUID formats
   - Sanitize metadata inputs
   - Prevent injection attacks

4. **Logging**
   - Don't log sensitive data in metadata
   - Rotate logs regularly
   - Monitor for suspicious patterns

---

## Next Steps

After successful testing:

1. Configure production n8n workflows
2. Set up monitoring and alerting
3. Document n8n workflow structure
4. Train team on pause/resume controls
5. Establish SLAs for orchestration completion
6. Create runbook for failure scenarios

---

**Document Version:** 1.0
**Last Updated:** 2025-11-24
**Author:** Claude (AI Assistant)
**System Version:** Phase 1-5 Complete
