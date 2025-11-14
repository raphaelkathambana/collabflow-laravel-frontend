# Manual End-to-End Testing Procedure

This document provides a step-by-step guide for manually testing the complete Laravel-Python AI integration from project creation to task execution.

## Prerequisites

### 1. Environment Setup
- ✅ Laravel app running (e.g., `http://laravel-app.test`)
- ✅ Python service running on `http://localhost:8001`
- ✅ PostgreSQL database running
- ✅ Redis running (for queues/cache)
- ⚠️ Optional: Chroma DB running on `http://localhost:8000`
- ⚠️ Optional: `ANTHROPIC_API_KEY` configured in `python-service/.env`

### 2. Database State
```bash
# Reset and migrate database
php artisan migrate:fresh --seed

# Verify migration
php artisan migrate:status
```

### 3. Service Health Checks
```bash
# Check Laravel
curl http://laravel-app.test/health

# Check Python service
curl http://localhost:8001/health

# Expected response:
{
  "status": "healthy",
  "service": "CollabFlow AI Engine",
  "version": "0.1.0"
}
```

---

## Test Suite 1: Project Creation Wizard (Happy Path)

### Test 1.1: AI-Powered Project Creation

**Objective**: Verify complete flow from project details to AI-generated tasks

**Steps**:

1. **Navigate to Create Project**
   - Go to: `http://laravel-app.test/projects/create`
   - Verify wizard loads with Step 1 visible

2. **Step 1: Enter Project Details**
   ```
   Name: E-commerce Platform
   Description: Build a modern online store with product catalog, shopping cart, payment processing, and order management
   Domain: Software Development
   Timeline: 3 months
   Team Size: 5
   Start Date: [Today's date]
   End Date: [3 months from today]
   ```
   - Click "Next Step"
   - **Expected**: Validation passes, moves to Step 2

3. **Step 2: Define Goals & Context**
   - Add Goal 1:
     ```
     Title: Launch MVP with Core Features
     Description: Implement product browsing, cart management, and checkout
     Priority: High
     ```
   - Add Goal 2:
     ```
     Title: Implement Secure Payments
     Description: Integrate Stripe/PayPal with PCI compliance
     Priority: High
     ```
   - Add Goal 3:
     ```
     Title: Build Admin Dashboard
     Description: Inventory management and order tracking
     Priority: Medium
     ```
   - Optional: Add success metrics and constraints
   - Click "Analyze with AI"

   **Expected**:
   - Loading indicator appears with "Analyzing your project..." message
   - Toast notification shows: "Project analyzed: [complexity] complexity, estimated [N] tasks"
   - Analysis results displayed (domain, complexity, estimated task count)
   - "Generate Tasks" button becomes enabled
   - Progress moves to ~30%

4. **Step 3: Generate Tasks**
   - Click "Generate Tasks with AI"

   **Expected**:
   - Loading indicator with streaming progress
   - Messages cycle through: "Analyzing...", "Generating tasks...", "Validating workflow..."
   - Progress bar fills to 100%
   - Task list populates with AI-generated tasks
   - Toast notification: "Successfully generated [N] tasks with AI assistance"
   - Tasks show mix of types: AI, Human, HITL
   - Each task displays:
     - Name and description
     - Type badge (color-coded)
     - Estimated hours
     - Complexity indicator
     - Sequence number

5. **Step 4: Review Workflow**
   - Verify task breakdown shows:
     - Total tasks count
     - AI tasks count
     - Human tasks count
     - HITL tasks count
     - Total estimated hours
   - Check flowchart visualization (if enabled)
   - Verify dependencies are visible (lines between tasks)
   - Click "Create Project"

   **Expected**:
   - Success toast: "Project created successfully!"
   - Redirect to project detail page
   - Project appears in dashboard with "In Progress" status

6. **Verify Database State**
   ```bash
   php artisan tinker
   ```
   ```php
   $project = \App\Models\Project::latest()->first();
   $project->name; // Should be "E-commerce Platform"
   $project->workflow_metadata; // Should contain task counts and metadata
   $project->tasks->count(); // Should match generated count

   // Check task types distribution
   $project->tasks->groupBy('type')->map->count();
   // Should show: ['ai' => X, 'human' => Y, 'hitl' => Z]

   // Verify dependencies
   $firstTask = $project->tasks->first();
   $firstTask->dependencies; // Should be array of UUIDs
   ```

**Pass Criteria**:
- ✅ All steps complete without errors
- ✅ Toast notifications appear at each stage
- ✅ Tasks generated with correct types and data
- ✅ Workflow metadata saved to database
- ✅ Dependencies stored correctly
- ✅ Project accessible on detail page

---

### Test 1.2: Fallback Mode (Without Python Service)

**Objective**: Verify graceful degradation when AI service unavailable

**Setup**:
```bash
# Stop Python service temporarily
# Or set PYTHON_SERVICE_ENABLED=false in .env
```

**Steps**:

1. Navigate to Create Project
2. Enter project details (same as Test 1.1)
3. Click "Next Step"
4. Define goals
5. Click "Analyze with AI"

**Expected**:
- Toast warning: "AI service unavailable. Using basic analysis. Results may be less detailed."
- Basic analysis displayed (rule-based)
- `usingFallback` flag set to true
- "Generate Tasks" still available

6. Click "Generate Tasks"

**Expected**:
- Toast warning about fallback mode
- Mock tasks generated (3-5 basic tasks)
- Tasks still have proper structure
- Can proceed to create project

**Pass Criteria**:
- ✅ No errors or crashes
- ✅ Clear warning messages to user
- ✅ Fallback tasks generated
- ✅ Project creation succeeds

---

## Test Suite 2: Task Management

### Test 2.1: View Project with AI Tasks

**Steps**:

1. Navigate to project detail page
2. View task list

**Verify**:
- ✅ Tasks display with correct types (AI/Human/HITL badges)
- ✅ Task metadata visible (complexity, estimated hours)
- ✅ Dependencies shown (prerequisite tasks)
- ✅ Sequence numbers correct
- ✅ Filter by task type works
- ✅ Sort by sequence/complexity works

### Test 2.2: Edit AI-Generated Task

**Steps**:

1. Click "Edit" on any AI-generated task
2. Modify:
   - Name
   - Description
   - Estimated hours
   - Type (change from AI to Human)
3. Save changes

**Expected**:
- Toast: "Task updated successfully"
- Changes reflected immediately
- Metadata preserved (python_task_id, scores, etc.)

**Pass Criteria**:
- ✅ Edit modal opens correctly
- ✅ All fields editable
- ✅ Changes persist to database
- ✅ Metadata not lost during edit

### Test 2.3: Task Dependencies

**Steps**:

1. Find task with dependencies (blocked by other tasks)
2. Verify dependency indicator shows
3. Try to mark task as "In Progress" while dependency is "Pending"

**Expected**:
- Warning: "Cannot start task until dependencies are completed"
- Task remains in "Pending" state

4. Complete dependency task first
5. Now mark dependent task as "In Progress"

**Expected**:
- Status changes successfully
- No warnings

**Pass Criteria**:
- ✅ Dependency validation works
- ✅ Cannot start blocked tasks prematurely
- ✅ Can start after dependencies complete

---

## Test Suite 3: Workflow Regeneration

### Test 3.1: Regenerate Workflow

**Objective**: Verify workflow can be regenerated while preserving edits

**Steps**:

1. Open existing project with AI-generated tasks
2. Edit Task 1 (change name to "Modified Task")
3. Mark "Preserve Manual Edits" checkbox
4. Click "Regenerate Workflow"

**Expected**:
- Confirmation modal: "This will regenerate tasks using AI. Preserve manual edits?"
- Loading indicator
- New tasks generated
- Modified Task 1 name preserved
- Toast: "Workflow regenerated successfully"

5. Verify:
   - Task count may have changed
   - Edited task still has modified name
   - New tasks have fresh metadata
   - Dependencies recalculated

**Pass Criteria**:
- ✅ Regeneration completes successfully
- ✅ Manual edits preserved when checkbox selected
- ✅ New AI analysis reflected in tasks
- ✅ Database updated correctly

---

## Test Suite 4: Integration Edge Cases

### Test 4.1: Large Project (50+ Tasks)

**Setup**: Create project with complex description requiring many tasks

**Steps**:

1. Create project:
   ```
   Name: Enterprise ERP System
   Description: Build complete ERP with modules for: HR, Finance, Inventory, Sales, CRM, Reporting, Analytics, User Management, Document Management, Workflow Automation, Multi-tenant Architecture, API Gateway, Mobile Apps (iOS/Android), Desktop App, Email Integration, Calendar, Notifications, Search, Audit Logs, Data Export/Import
   Domain: Software Development
   Timeline: 12 months
   Team Size: 20
   ```

2. Proceed through wizard

**Expected**:
- Analysis estimates 40-60 tasks
- Task generation takes 90-120 seconds (within 150s timeout)
- All tasks generated successfully
- Performance acceptable

**Pass Criteria**:
- ✅ No timeout errors
- ✅ All tasks generated within 150s
- ✅ Database handles large task set
- ✅ UI remains responsive

### Test 4.2: Minimal Project (3-5 Tasks)

**Steps**:

1. Create simple project:
   ```
   Name: Personal Blog
   Description: Simple blog with posts and comments
   Domain: Software Development
   Timeline: 2 weeks
   Team Size: 1
   ```

**Expected**:
- Analysis estimates 3-7 tasks
- Tasks generated quickly (< 30s)
- Minimal but complete workflow

**Pass Criteria**:
- ✅ Appropriate task count for scope
- ✅ Fast generation time
- ✅ Tasks still properly structured

### Test 4.3: Network Failure Handling

**Setup**: Simulate network issues

**Test 4.3a - During Analysis**:
1. Start project creation
2. Kill Python service after clicking "Analyze"
3. Wait for timeout

**Expected**:
- Error toast after timeout
- Fallback mode activated automatically
- User can continue with basic analysis

**Test 4.3b - During Task Generation**:
1. Start task generation
2. Kill Python service mid-generation
3. Wait for timeout

**Expected**:
- Error toast: "Task generation failed"
- Option to retry
- Can fall back to manual task creation

**Pass Criteria**:
- ✅ No unhandled exceptions
- ✅ Clear error messages
- ✅ Graceful fallback options
- ✅ No data corruption

---

## Test Suite 5: Data Integrity

### Test 5.1: Verify Python → Laravel Field Mapping

**Steps**:

1. Create project with AI
2. Inspect database:
   ```bash
   php artisan tinker
   ```
   ```php
   $project = \App\Models\Project::latest()->first();
   $task = $project->tasks->first();

   // Verify fields
   $task->type; // Should be: 'ai', 'human', or 'hitl' (lowercase)
   $task->status; // Should be: 'pending' (lowercase)
   $task->estimated_hours; // Should be integer
   $task->complexity; // Should be: 'LOW', 'MEDIUM', 'HIGH' (uppercase preserved)

   // Verify metadata
   $task->metadata; // Should contain:
   // - python_task_id
   // - ai_suitability_score
   // - confidence_score
   // - validation
   // - position (for flowchart)

   // Verify dependencies
   $task->dependencies; // Should be array of UUIDs
   ```

**Pass Criteria**:
- ✅ All field types correct (lowercase where expected)
- ✅ Metadata structure matches Python response
- ✅ No field mapping errors
- ✅ Dependencies are valid UUIDs

### Test 5.2: Workflow Metadata Storage

**Steps**:

1. Create AI-generated project
2. Check `workflow_metadata` column:
   ```php
   $project->workflow_metadata;
   ```

**Expected Structure**:
```json
{
  "total_tasks": 12,
  "ai_tasks": 5,
  "human_tasks": 5,
  "hitl_tasks": 2,
  "total_estimated_hours": 156.5,
  "avg_validation_score": 0.87,
  "generated_at": "2025-11-11T10:30:00Z",
  "python_service_version": "0.1.0"
}
```

**Pass Criteria**:
- ✅ Metadata saved correctly
- ✅ Task counts match actual tasks
- ✅ Hours sum correctly
- ✅ Timestamp present

---

## Test Suite 6: Performance Testing

### Test 6.1: Response Times

**Measure response times for each operation**:

**Context Analysis**:
- Start timer when "Analyze" clicked
- End when results displayed
- **Target**: < 15 seconds

**Task Generation**:
- Start timer when "Generate Tasks" clicked
- End when tasks displayed
- **Target**: < 120 seconds for 10-20 tasks

**Project Creation**:
- Start timer when "Create Project" clicked
- End when redirected to detail page
- **Target**: < 5 seconds

**Pass Criteria**:
- ✅ All operations within target times
- ✅ No performance degradation on repeat

### Test 6.2: Concurrent Users

**Setup**: Simulate multiple users creating projects simultaneously

**Steps**:

1. Open 3 browser tabs/windows
2. Start project creation in all 3 at same time
3. Proceed through wizard simultaneously

**Expected**:
- All 3 complete successfully
- No race conditions
- Database state consistent
- No deadlocks or conflicts

**Pass Criteria**:
- ✅ All projects created successfully
- ✅ No errors or conflicts
- ✅ Response times acceptable
- ✅ Data integrity maintained

---

## Test Suite 7: UI/UX Validation

### Test 7.1: Loading States

**Verify loading indicators at each stage**:

1. **Analysis Loading**:
   - ✅ Spinner visible
   - ✅ Message: "Analyzing your project..."
   - ✅ Progress updates
   - ✅ Cancel button available

2. **Task Generation Loading**:
   - ✅ Streaming progress bar
   - ✅ Messages update: "Analyzing..." → "Generating..." → "Validating..."
   - ✅ Task count updates in real-time (if streaming enabled)
   - ✅ Cancel button available

3. **Project Creation Loading**:
   - ✅ Button disabled during save
   - ✅ Spinner on button
   - ✅ "Creating..." text

### Test 7.2: Toast Notifications

**Verify toasts appear for all events**:

- ✅ Analysis complete (info)
- ✅ Analysis failed (warning)
- ✅ Tasks generated (success)
- ✅ Task generation failed (warning)
- ✅ Project created (success)
- ✅ Task updated (success)
- ✅ Validation errors (error)

**Check toast properties**:
- ✅ Auto-dismiss after 5 seconds
- ✅ Correct icon for type
- ✅ Readable message text
- ✅ Dismissible manually

### Test 7.3: Error Messages

**Trigger validation errors**:

1. Try to proceed without required fields
2. Enter invalid data (e.g., end date before start date)
3. Network failures

**Verify**:
- ✅ Clear error messages
- ✅ Field-level validation highlights
- ✅ Helpful suggestions for fixes
- ✅ No technical jargon exposed to users

---

## Test Suite 8: Browser Compatibility

**Test in multiple browsers**:

- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari (if on Mac)

**Verify**:
- ✅ All UI elements render correctly
- ✅ Toast notifications work
- ✅ Loading states display
- ✅ Livewire updates work
- ✅ No console errors

---

## Troubleshooting Guide

### Issue: "Python service unavailable"

**Check**:
```bash
# Is Python service running?
curl http://localhost:8001/health

# Check Python service logs
cd ../python-service
# If running with uvicorn, check terminal output
```

**Solution**:
```bash
cd ../python-service
uvicorn app.main:app --reload --port 8001
```

### Issue: "Task generation timeout"

**Check**:
- Current timeout: Check `.env` → `PYTHON_SERVICE_TIMEOUT`
- Python service health
- ANTHROPIC_API_KEY configured

**Solution**:
```bash
# Increase timeout in .env
PYTHON_SERVICE_TIMEOUT=180

# Clear config cache
php artisan config:clear
```

### Issue: "Tasks not displaying"

**Check**:
```php
// In tinker
$project = \App\Models\Project::latest()->first();
$project->tasks; // Empty?
$project->workflow_state; // Contains tasks?
```

**Common Causes**:
- Tasks stored in `workflow_state` but not persisted to `tasks` table
- `completeGeneration()` not called
- Database transaction not committed

### Issue: "Dependencies not working"

**Check**:
```php
$task = \App\Models\Task::first();
$task->dependencies; // Should be array, not null
```

**Common Causes**:
- Dependencies stored as JSON array, not relationship
- Python task IDs not mapped to Laravel UUIDs
- Two-pass creation not working

---

## Test Execution Checklist

Use this checklist when performing manual E2E testing:

### Pre-Testing
- [ ] All services running (Laravel, Python, DB, Redis)
- [ ] Database migrated and seeded
- [ ] `.env` configurations correct
- [ ] Browser cache cleared
- [ ] Python service logs accessible

### Test Execution
- [ ] Test Suite 1: Project Creation (Happy Path)
- [ ] Test Suite 1: Fallback Mode
- [ ] Test Suite 2: Task Management (all 3 tests)
- [ ] Test Suite 3: Workflow Regeneration
- [ ] Test Suite 4: Edge Cases (all 3 tests)
- [ ] Test Suite 5: Data Integrity (all 2 tests)
- [ ] Test Suite 6: Performance (all 2 tests)
- [ ] Test Suite 7: UI/UX (all 3 tests)
- [ ] Test Suite 8: Browser Compatibility

### Post-Testing
- [ ] All tests passed
- [ ] Issues logged (if any)
- [ ] Database state verified
- [ ] Performance metrics recorded
- [ ] Test report completed

---

## Expected Results Summary

| Test Suite | Tests | Expected Pass Rate |
|------------|-------|--------------------|
| Project Creation | 2 | 100% |
| Task Management | 3 | 100% |
| Workflow Regeneration | 1 | 100% |
| Edge Cases | 3 | 100% |
| Data Integrity | 2 | 100% |
| Performance | 2 | 100% |
| UI/UX | 3 | 100% |
| Browser Compatibility | 1 | 100% |
| **TOTAL** | **17** | **100%** |

---

## Reporting Issues

When reporting issues found during manual testing, include:

1. **Test Details**:
   - Test suite and test number
   - Steps to reproduce
   - Expected vs actual behavior

2. **Environment**:
   - Browser and version
   - Laravel version: `php artisan --version`
   - Python service version: `curl http://localhost:8001/ | jq .version`

3. **Logs**:
   - Laravel logs: `storage/logs/laravel.log`
   - Python service logs: Console output
   - Browser console errors

4. **Database State**:
   - Relevant table dumps
   - Query results from troubleshooting

5. **Screenshots/Videos**:
   - UI state when issue occurred
   - Error messages
   - Network requests (DevTools)

---

**Document Version**: 1.0
**Last Updated**: November 11, 2025
**Maintained By**: CollabFlow Development Team
