# CollabFlow Laravel - Python Service Integration Tests

This directory contains comprehensive integration tests for the Laravel-Python AI service integration.

## Test Suite Overview

### Phase 2: Integration Testing (COMPLETED)

#### 2.1: AIEngineService Integration Tests
**File**: `tests/Feature/Services/AIEngineServiceTest.php`
**Status**: ✅ 18 tests, 51 assertions - ALL PASSING

Tests the core service layer that communicates with the Python AI engine.

**Coverage**:
- ✅ Health check (3 tests)
- ✅ Context analysis (3 tests)
- ✅ Task generation (2 tests)
- ✅ Field mapping (2 tests)
- ✅ Retry logic (2 tests)
- ✅ Error handling (2 tests)
- ✅ Service disabled states (3 tests)
- ✅ Configuration (1 test)

**Key Features Tested**:
- HTTP mocking with `Http::fake()`
- Timeout configuration (150s for task generation)
- Field name conversion (UPPERCASE → lowercase)
- Cache management for analysis results
- Graceful fallback when service unavailable
- Retry only on ConnectionException (not 500 errors)

#### 2.2: CreateProjectWizard Integration Tests
**File**: `tests/Feature/Livewire/CreateProjectWizardTest.php`
**Status**: ✅ 14 tests, 65 assertions - ALL PASSING

Tests the Livewire wizard component that orchestrates project creation with AI.

**Coverage**:
- ✅ Wizard initialization
- ✅ Step navigation and validation
- ✅ AI context analysis integration
- ✅ AI task generation integration
- ✅ Project creation with AI data
- ✅ Fallback mode activation
- ✅ Dependency management
- ✅ Workflow metadata storage
- ✅ Event dispatching
- ✅ Complex dependency scenarios

**Key Features Tested**:
- Livewire component testing with `Livewire::test()`
- Database assertions for projects and tasks
- Two-pass task creation with ID mapping
- Dependency storage as JSON arrays
- Toast notification dispatching
- Service disabled handling

#### 2.3: Python Service E2E Integration Tests
**File**: `tests/Feature/Integration/PythonServiceE2ETest.php`
**Status**: ✅ 2 passed, 4 skipped, 1 risky

Real end-to-end tests that call the actual Python service (requires service running).

**Coverage**:
- ✅ Health check endpoint
- ✅ Context analysis E2E (with real AI)
- ⏭️ Task generation E2E (skipped if ANTHROPIC_API_KEY not configured)
- ⏭️ Full workflow (analysis → tasks)
- ⏭️ Field mapping validation
- ⏭️ Performance testing
- ⚠️ Error handling with invalid data

**Requirements**:
- Python service running on `http://localhost:8001`
- Optional: `ANTHROPIC_API_KEY` in `python-service/.env` for full tests
- Optional: Chroma vector database running

**To Run Python Service**:
```bash
cd ../python-service
# Ensure .env is configured
uvicorn app.main:app --reload --port 8001
```

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# AIEngineService tests
php artisan test tests/Feature/Services/AIEngineServiceTest.php

# CreateProjectWizard tests
php artisan test tests/Feature/Livewire/CreateProjectWizardTest.php

# E2E tests (requires Python service)
php artisan test tests/Feature/Integration/PythonServiceE2ETest.php
```

### Run with Coverage (if xdebug installed)
```bash
php artisan test --coverage
```

## Test Results Summary

| Test Suite | Tests | Assertions | Status |
|------------|-------|------------|--------|
| AIEngineService | 18 | 51 | ✅ PASSING |
| CreateProjectWizard | 14 | 65 | ✅ PASSING |
| Python E2E | 7 | 17 | ⚠️ PARTIAL* |
| **TOTAL** | **39** | **133** | **✅ 32/39 PASSING** |

*E2E tests skip gracefully if Python service or API key not available

## Key Integration Points Tested

### 1. Context Analysis Flow
```
Laravel → AIEngineService → Python /api/context/analyze → Response
                                                            ↓
                                                    Cached & Returned
```

**Verified**:
- ✅ Request payload structure
- ✅ Response parsing
- ✅ Cache implementation
- ✅ Error handling and fallback
- ✅ Toast notifications

### 2. Task Generation Flow
```
Laravel → AIEngineService → Python /api/tasks/generate → Response
                                                            ↓
                                    Parse tasks, dependencies, metadata
                                                            ↓
                                        Convert UPPERCASE → lowercase
                                                            ↓
                                            Store in database
```

**Verified**:
- ✅ 150s timeout for generation
- ✅ Field mapping (AI/HUMAN/HITL → ai/human/hitl)
- ✅ Dependency extraction and storage
- ✅ Metadata persistence in `workflow_metadata` column
- ✅ Two-pass creation with Python ID → Laravel UUID mapping

### 3. Dependency Management
```
Python Response:
{
  "dependencies": [
    {"from_task_id": "task_001", "to_task_id": "task_002", "type": "blocks"}
  ]
}

Laravel Storage:
- Task 1 (Python: task_001) → Laravel UUID
- Task 2 (Python: task_002) → Laravel UUID
- Task 2.dependencies = [Task 1 UUID]
```

**Verified**:
- ✅ ID mapping stored correctly
- ✅ Dependencies as JSON arrays
- ✅ Multiple dependencies per task
- ✅ Dependency types preserved in metadata

## Configuration

### Laravel Configuration
```php
// config/services.php
'python' => [
    'enabled' => env('PYTHON_SERVICE_ENABLED', true),
    'url' => env('PYTHON_SERVICE_URL', 'http://localhost:8001'),
    'timeout' => env('PYTHON_SERVICE_TIMEOUT', 150),
],
```

### Environment Variables
```env
# .env
PYTHON_SERVICE_ENABLED=true
PYTHON_SERVICE_URL=http://localhost:8001
PYTHON_SERVICE_TIMEOUT=150
```

## API Compatibility

### Python Service → Laravel Field Mappings

| Python Field | Laravel Field | Notes |
|--------------|---------------|-------|
| `assigned_to: "AI"` | `type: "ai"` | Lowercase conversion |
| `assigned_to: "HUMAN"` | `type: "human"` | Lowercase conversion |
| `assigned_to: "HITL"` | `type: "hitl"` | Lowercase conversion |
| `assigned_to: "HYBRID"` | `type: "hitl"` | Smart fallback |
| `status: "PENDING"` | `status: "pending"` | Lowercase conversion |
| `complexity: "HIGH"` | `complexity: "HIGH"` | Preserved (uppercase in DB) |
| `estimated_hours: 10.0` | `estimated_hours: 10` | Converted to integer |

### Response Structure

**Context Analysis Response**:
```json
{
  "analysis": {
    "domain": "software_development",
    "complexity": "low",
    "estimated_task_count": 7,
    "key_objectives": [...],
    "required_skills": [...],
    "confidence_score": 0.85
  },
  "status": "success"
}
```

**Task Generation Response**:
```json
{
  "tasks": [
    {
      "id": "task_001",
      "name": "Task Name",
      "description": "Description",
      "assigned_to": "ai",
      "estimated_hours": 10,
      "complexity": "MEDIUM",
      "sequence": 1,
      "ai_suitability_score": 0.9,
      "confidence_score": 0.85,
      "status": "pending",
      "subtasks": [],
      "validation": {"score": 85, "passed": true}
    }
  ],
  "dependencies": [
    {
      "from_task_id": "task_001",
      "to_task_id": "task_002",
      "type": "blocks"
    }
  ],
  "metadata": {
    "total_tasks": 10,
    "ai_tasks": 4,
    "human_tasks": 4,
    "hitl_tasks": 2,
    "total_estimated_hours": 82.5,
    "avg_validation_score": 0.875
  }
}
```

## Known Issues & Limitations

### 1. Task Generation Requires API Key
- **Issue**: E2E task generation tests skip if `ANTHROPIC_API_KEY` not configured
- **Impact**: Cannot test full workflow without API key
- **Workaround**: Use mocked tests (AIEngineService/CreateProjectWizard suites)
- **Resolution**: Configure API key in `python-service/.env` for full E2E testing

### 2. Chroma Optional
- **Issue**: Python service warns if Chroma not available
- **Impact**: No knowledge persistence, but tasks still generate
- **Workaround**: Tests skip gracefully
- **Resolution**: Run Chroma with Docker: `docker run -p 8000:8000 chromadb/chroma`

### 3. Timeout on Large Projects
- **Issue**: Very large projects (50+ estimated tasks) might timeout
- **Impact**: Task generation fails after 150s
- **Workaround**: Increase `PYTHON_SERVICE_TIMEOUT` in .env
- **Resolution**: Currently set to 150s to accommodate 82-120s average generation time

## Debugging Tips

### Test Failures

**If AIEngineService tests fail**:
1. Check HTTP mocking setup
2. Verify field mapping in `convertAssignmentType()`
3. Check cache configuration

**If CreateProjectWizard tests fail**:
1. Run database migrations: `php artisan migrate`
2. Check property names match component (e.g., `$name` not `$projectName`)
3. Verify `user_id` not `owner_id` in assertions
4. Check dependencies stored as arrays, not relations

**If E2E tests fail**:
1. Verify Python service running: `curl http://localhost:8001/health`
2. Check Python service logs for errors
3. Verify `ANTHROPIC_API_KEY` in `python-service/.env`
4. Optional: Start Chroma if using knowledge persistence

### Viewing Test Output
```bash
# Verbose output
php artisan test --verbose

# Stop on first failure
php artisan test --stop-on-failure

# Show full error traces
php artisan test --colors=always
```

### Inspecting HTTP Calls
```php
// In tests, add after Http::fake()
Http::assertSent(function ($request) {
    dump($request->url());
    dump($request->data());
    return true;
});
```

## Next Steps (Phase 3)

### Planned Enhancements
- 🔄 Manual E2E testing procedure document
- 🔄 Enhanced task fields migration (complexity, sequence as indexed columns)
- 🔄 User notification system UI components
- 🔄 Performance monitoring and logging
- 🔄 Health dashboard command (`php artisan ai:check`)

## Contributing

When adding new integration tests:

1. **Use HTTP mocking** for unit/integration tests (AIEngineService/CreateProjectWizard)
2. **Use E2E tests** sparingly for critical flows only (requires running services)
3. **Skip gracefully** if dependencies unavailable (Python service, API key)
4. **Document new fields** in this README if API changes
5. **Update field mappings** if Python service response structure changes

## References

- **Python Service Design**: `../python-service/01_Python_Service_Complete_Design.md`
- **Integration Guide**: `02_Laravel_Integration_Implementation_Guide.md`
- **Pest Documentation**: https://pestphp.com/docs
- **Livewire Testing**: https://livewire.laravel.com/docs/testing
