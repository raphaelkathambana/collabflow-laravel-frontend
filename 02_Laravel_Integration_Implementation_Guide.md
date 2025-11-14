# CollabFlow Laravel Integration - Implementation Guide

**Version:** 1.0
**Date:** November 9, 2025
**Target Audience:** Implementation agents and developers
**Prerequisite Reading**: `01_Python_Service_Complete_Design.md`, `03_CollabFlow_Laravel_Python_Integration_UPDATED.md`

---

## Table of Contents

1. [Integration Overview](#1-integration-overview)
2. [Prerequisites & Setup](#2-prerequisites--setup)
3. [Database Schema Alignment](#3-database-schema-alignment)
4. [Model Mismatch Resolution Protocol](#4-model-mismatch-resolution-protocol)
5. [AIEngineService Implementation](#5-aiengineservice-implementation)
6. [Livewire Wizard Implementation](#6-livewire-wizard-implementation)
7. [Frontend Components](#7-frontend-components)
8. [Error Handling & Fallbacks](#8-error-handling--fallbacks)
9. [Testing Requirements](#9-testing-requirements)
10. [Configuration Setup](#10-configuration-setup)
11. [Ambiguity Resolution Protocol](#11-ambiguity-resolution-protocol)
12. [Implementation Checklist](#12-implementation-checklist)
13. [Compliance Requirements](#13-compliance-requirements)
14. [Troubleshooting Guide](#14-troubleshooting-guide)

---

## 1. Integration Overview

### 1.1 Architecture Overview

```
┌────────────────────────────────────────────────────────────┐
│                    User Browser                             │
│                  (Livewire Components)                      │
└────────────────────┬───────────────────────────────────────┘
                     │ HTTP
                     ▼
┌────────────────────────────────────────────────────────────┐
│               Laravel Application                           │
│                                                             │
│  ┌──────────────────┐  ┌──────────────┐  ┌─────────────┐ │
│  │   Livewire       │  │ AIEngine     │  │  Eloquent   │ │
│  │   CreateProject  │──│ Service      │  │  Models     │ │
│  │   Wizard         │  │ (HTTP Client)│  │             │ │
│  └──────────────────┘  └──────┬───────┘  └──────┬──────┘ │
│                                │                 │         │
└────────────────────────────────┼─────────────────┼─────────┘
                                 │                 │
                        HTTP/REST│                 │ PostgreSQL
                                 ▼                 ▼
                    ┌─────────────────────┐  ┌──────────┐
                    │   Python Service    │  │   DB     │
                    │   (FastAPI)         │  │          │
                    │   localhost:8001    │  │          │
                    └─────────────────────┘  └──────────┘
```

### 1.2 Data Flow

```
Step 1-2: Pure Laravel (Project Details + Goals/KPIs)
    │
    ├─→ Collect user input
    ├─→ Validate in Livewire component
    └─→ Store in component state

Step 3: Laravel + Python Integration (AI Analysis + Task Generation)
    │
    ├─→ AIEngineService::analyzeContext()
    │   └─→ POST http://localhost:8001/api/context/analyze
    │       └─→ Returns ContextAnalysis
    │
    ├─→ AIEngineService::generateTasks()
    │   └─→ POST http://localhost:8001/api/tasks/generate
    │       └─→ Returns Tasks + Dependencies + Metadata
    │
    └─→ Display results in Livewire UI

Step 4: Pure Laravel (Review + Create)
    │
    ├─→ User selects tasks
    ├─→ Confirm creation
    ├─→ DB::transaction
    │   ├─→ Create Project
    │   ├─→ Create ProjectGoals
    │   ├─→ Create ProjectKpis
    │   └─→ Create Tasks
    └─→ Redirect to project detail
```

### 1.3 Integration Points

| Step | Component | Laravel | Python | Database |
|------|-----------|---------|--------|----------|
| 1 | Project Details | ✅ | ❌ | ❌ |
| 2 | Goals & KPIs | ✅ | ❌ | ❌ |
| 3 | AI Analysis | ✅ | ✅ | ❌ |
| 3 | Task Generation | ✅ | ✅ | ❌ |
| 4 | Review & Create | ✅ | ❌ | ✅ |

---

## 2. Prerequisites & Setup

### 2.1 Laravel Prerequisites

**Required Packages**:
```json
{
  "php": "^8.2",
  "laravel/framework": "^12.0",
  "livewire/livewire": "^3.0",
  "laravel/breeze": "^2.0"
}
```

**Installation**:
```bash
cd d:/collabflow/laravel-app

# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Compile assets
npm run dev
```

### 2.2 Python Service Prerequisites

**Python Service Must Be Running**:
```bash
cd d:/collabflow/python-service

# Activate virtual environment
.\venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Start service
python dev.py
```

**Verify Python Service**:
```bash
curl http://localhost:8001/health
```

Expected response:
```json
{
  "status": "healthy",
  "environment": "development",
  "debug": false
}
```

### 2.3 ChromaDB Prerequisites (Optional)

**Start ChromaDB**:
```bash
docker run -p 8000:8000 chromadb/chroma
```

**Note**: Python service will work without ChromaDB (degraded mode)

---

## 3. Database Schema Alignment

### 3.1 CRITICAL: Source of Truth

**RULE**: **Laravel database schema is the SINGLE SOURCE OF TRUTH**

- Python service generates data
- Laravel validates and stores data
- If Python returns extra fields: ignore gracefully
- If Laravel expects missing fields: use fallback defaults
- Never modify Laravel schema to match Python output

### 3.2 Model Field Mapping

#### Projects Table

**Laravel Schema** (`projects` table):
```sql
id                  UUID PRIMARY KEY
user_id             UUID NOT NULL
name                VARCHAR(255)
description         TEXT
domain              VARCHAR(100)
timeline            VARCHAR(100)
status              VARCHAR(50) DEFAULT 'active'
progress_percentage DECIMAL(5,2) DEFAULT 0
ai_analysis         JSONB                -- Store entire Python response
created_at          TIMESTAMP
updated_at          TIMESTAMP
completed_at        TIMESTAMP NULL
deleted_at          TIMESTAMP NULL
```

**Python Output** (`ContextAnalysis`):
```python
{
  "domain": "SOFTWARE_DEVELOPMENT",
  "complexity": "HIGH",
  "estimated_task_count": 30,
  "key_objectives": [...],
  "challenges": [...],
  "required_skills": [...],
  "recommendations": [...],
  "confidence_score": 0.88
}
```

**Mapping Strategy**:
```php
// Store entire analysis in JSON column
$project->ai_analysis = $pythonAnalysis;

// Extract specific fields
$project->domain = strtolower($pythonAnalysis['domain']); // Convert to lowercase
```

---

#### Tasks Table

**Laravel Schema** (`tasks` table):
```sql
id                UUID PRIMARY KEY
project_id        UUID NOT NULL
parent_task_id    UUID NULL
name              VARCHAR(255)
description       TEXT
type              ENUM('ai', 'human', 'hitl')    -- CRITICAL: lowercase
status            VARCHAR(50) DEFAULT 'pending'
priority          VARCHAR(20) DEFAULT 'medium'
assigned_to       UUID NULL
estimated_hours   DECIMAL(6,2)
actual_hours      DECIMAL(6,2)
due_date          DATE
start_date        DATE
completed_at      TIMESTAMP
dependencies      JSONB                          -- Array of task IDs
metadata          JSONB                          -- AI scores, complexity
created_at        TIMESTAMP
updated_at        TIMESTAMP
deleted_at        TIMESTAMP
```

**Python Output** (`Task`):
```python
{
  "id": "task_001",
  "name": "Set up project infrastructure",
  "description": "Initialize React frontend...",
  "estimated_hours": 16.0,
  "complexity": "MEDIUM",
  "assigned_to": "HUMAN",                    # UPPERCASE
  "ai_suitability_score": 0.35,
  "confidence_score": 0.82,
  "sequence": 1,
  "status": "PENDING",
  "subtasks": [...],
  "validation": {...},
  "position": {"x": 100, "y": 200},
  "created_at": "2025-11-09T10:30:00Z"
}
```

**CRITICAL Mapping**:
```php
// Convert Python 'assigned_to' to Laravel 'type'
$taskType = match($pythonTask['assigned_to']) {
    'AI' => 'ai',          // MUST convert to lowercase
    'HUMAN' => 'human',    // MUST convert to lowercase
    'HITL' => 'hitl',      // MUST convert to lowercase
    default => 'human'     // Fallback to human
};

Task::create([
    'project_id' => $project->id,
    'name' => $pythonTask['name'],
    'description' => $pythonTask['description'],
    'type' => $taskType,  // Converted to lowercase
    'estimated_hours' => $pythonTask['estimated_hours'],
    'status' => 'pending', // Laravel default
    'priority' => 'medium', // Laravel default
    'dependencies' => $pythonTask['dependencies'] ?? [],
    'metadata' => [
        'complexity' => $pythonTask['complexity'] ?? null,
        'ai_suitability_score' => $pythonTask['ai_suitability_score'] ?? null,
        'confidence_score' => $pythonTask['confidence_score'] ?? null,
        'sequence' => $pythonTask['sequence'] ?? 0,
        'validation' => $pythonTask['validation'] ?? null,
        'position' => $pythonTask['position'] ?? null
    ]
]);
```

---

#### Subtasks Mapping

**Python Output**:
```python
{
  "subtasks": [
    {
      "id": "subtask_001",
      "name": "Set up JWT token generation",
      "description": "...",
      "estimated_hours": 6.0,
      "sequence": 1,
      "status": "PENDING"
    }
  ]
}
```

**Laravel Mapping**:
```php
// Option 1: Store subtasks as separate tasks with parent_task_id
foreach ($pythonTask['subtasks'] as $subtask) {
    Task::create([
        'project_id' => $project->id,
        'parent_task_id' => $parentTask->id,  // Link to parent
        'name' => $subtask['name'],
        'description' => $subtask['description'],
        'type' => $parentTask->type, // Inherit from parent
        'estimated_hours' => $subtask['estimated_hours'],
        'status' => 'pending',
        'metadata' => [
            'sequence' => $subtask['sequence']
        ]
    ]);
}

// Option 2: Store subtasks in parent task metadata
$parentTask->metadata = array_merge($parentTask->metadata, [
    'subtasks' => $pythonTask['subtasks']
]);
```

**RECOMMENDATION**: Use Option 1 (separate tasks) for better query support

---

### 3.3 Field Mismatch Handling

**Common Mismatches**:

| Python Field | Laravel Field | Resolution |
|--------------|---------------|------------|
| `assigned_to` (UPPERCASE) | `type` (lowercase enum) | Convert to lowercase |
| `id` (task_001 format) | `id` (UUID) | Generate new UUID, ignore Python ID |
| `complexity` (MEDIUM) | Not in schema | Store in `metadata` JSON |
| `ai_suitability_score` | Not in schema | Store in `metadata` JSON |
| `validation` | Not in schema | Store in `metadata` JSON |
| `position` {x, y} | Not in schema | Store in `metadata` JSON |
| `sequence` | Not in schema | Store in `metadata` JSON |

**Mapping Code**:
```php
class TaskMapper
{
    public static function fromPythonTask(array $pythonTask, string $projectId): array
    {
        return [
            'project_id' => $projectId,
            'name' => $pythonTask['name'],
            'description' => $pythonTask['description'] ?? '',
            'type' => self::convertAssignmentType($pythonTask['assigned_to'] ?? 'HUMAN'),
            'status' => 'pending',
            'priority' => 'medium',
            'estimated_hours' => $pythonTask['estimated_hours'] ?? 0,
            'dependencies' => $pythonTask['dependencies'] ?? [],
            'metadata' => self::extractMetadata($pythonTask)
        ];
    }

    private static function convertAssignmentType(string $pythonType): string
    {
        return match(strtoupper($pythonType)) {
            'AI' => 'ai',
            'HUMAN' => 'human',
            'HITL' => 'hitl',
            default => 'human'
        };
    }

    private static function extractMetadata(array $pythonTask): array
    {
        return [
            'complexity' => $pythonTask['complexity'] ?? null,
            'ai_suitability_score' => $pythonTask['ai_suitability_score'] ?? null,
            'confidence_score' => $pythonTask['confidence_score'] ?? null,
            'sequence' => $pythonTask['sequence'] ?? 0,
            'validation' => $pythonTask['validation'] ?? null,
            'position' => $pythonTask['position'] ?? null,
            'python_task_id' => $pythonTask['id'] ?? null // Store original ID for reference
        ];
    }
}
```

---

## 4. Model Mismatch Resolution Protocol

### 4.1 When to STOP and Ask for Clarity

**STOP immediately and ask the user if you encounter**:

1. **Critical Field Conflicts**:
   - Python requires a field that doesn't exist in Laravel schema
   - Laravel requires a field that Python never provides
   - Data type incompatibilities (e.g., Python returns array, Laravel expects string)

   Example:
   ```
   ❌ Python returns `workflow_type: "sequential"` but Laravel has no such column

   STOP: Ask user whether to:
   a) Add column to Laravel schema
   b) Store in metadata JSON
   c) Ignore the field
   ```

2. **Enum Value Mismatches**:
   - Python returns enum value not in Laravel's allowed list
   - Different naming conventions that could cause data loss

   Example:
   ```
   ❌ Python returns assigned_to: "HYBRID" but Laravel only accepts "ai", "human", "hitl"

   STOP: Ask user how to handle "HYBRID" type
   ```

3. **Business Logic Ambiguities**:
   - Unclear how to handle task dependencies (store as JSON or separate table?)
   - Unclear how to handle subtasks (separate tasks or nested JSON?)
   - Missing information about required workflows

   Example:
   ```
   ❓ Python returns dependencies as array of task IDs. Should I:
   a) Store as JSON array in dependencies column
   b) Create separate task_dependencies table
   c) Use both?

   STOP: Ask user for preference
   ```

4. **Security/Authorization Concerns**:
   - User ID validation unclear
   - Project ownership verification unclear
   - Permission checking missing

   Example:
   ```
   ⚠️ Python service doesn't verify user owns project. Should I:
   a) Add user_id check in Laravel before calling Python
   b) Pass user_id to Python for validation
   c) Both?

   STOP: Ask user about security requirements
   ```

5. **Data Loss Scenarios**:
   - Any situation where mapping would lose important data
   - Irreversible operations

   Example:
   ```
   ⚠️ Python returns 15 metadata fields but Laravel metadata column can only store JSON.
   If we encounter JSON encoding issues, data could be lost.

   STOP: Confirm this approach before proceeding
   ```

---

### 4.2 When to Proceed Autonomously

**Proceed without asking if**:

1. **Minor Field Name Differences**:
   ```php
   // Python uses "estimated_hours", Laravel uses "estimated_hours"
   // ✅ Same name, proceed

   // Python uses "ai_suitability_score", Laravel has metadata JSON
   // ✅ Store in metadata, proceed
   ```

2. **Case Conversions**:
   ```php
   // Python: "HUMAN", Laravel: "human"
   // ✅ Convert to lowercase, proceed

   // Python: "SOFTWARE_DEVELOPMENT", Laravel: "software_development"
   // ✅ Convert to lowercase, proceed
   ```

3. **UI/UX Decisions**:
   ```php
   // Loading spinner style
   // ✅ Choose reasonable default, proceed

   // Error message wording
   // ✅ Use clear, friendly language, proceed

   // Button colors and placement
   // ✅ Follow Laravel Breeze conventions, proceed
   ```

4. **Fallback Behavior**:
   ```php
   // Python service unavailable
   // ✅ Use documented fallback analysis, proceed

   // Optional field missing
   // ✅ Use null or default value, proceed
   ```

5. **Standard Patterns**:
   ```php
   // Transaction handling for database operations
   // ✅ Use DB::transaction, proceed

   // Validation rules for user input
   // ✅ Use Laravel validation, proceed

   // Error logging
   // ✅ Use Laravel logger, proceed
   ```

---

### 4.3 Decision Tree

```
┌─────────────────────────────────────┐
│   Encountered Issue/Decision        │
└────────────┬────────────────────────┘
             │
             ▼
      ┌─────────────┐
      │ Is it about │     YES    ┌──────────────────┐
      │ data model  │───────────→│ Could cause      │
      │ structure?  │            │ data loss?       │
      └──────┬──────┘            └────┬────────┬────┘
             │ NO                     │ YES    │ NO
             │                        │        │
             ▼                        ▼        ▼
      ┌─────────────┐         ┌──────────┐  ┌────────────┐
      │ Is it about │   YES   │   STOP   │  │ Can map to │
      │ security or │────────→│   ASK    │  │ metadata?  │
      │ auth?       │         │   USER   │  └─────┬──────┘
      └──────┬──────┘         └──────────┘        │
             │ NO                                  │ YES
             │                                     ▼
             ▼                              ┌────────────┐
      ┌─────────────┐                      │  PROCEED   │
      │ Is it about │   YES                │  Store in  │
      │ UI/UX       │─────────────────────→│  metadata  │
      │ styling?    │                      └────────────┘
      └──────┬──────┘
             │ NO
             ▼
      ┌─────────────┐
      │ Is it a     │    YES    ┌────────────┐
      │ documented  │──────────→│  PROCEED   │
      │ fallback?   │           │  Use doc   │
      └──────┬──────┘           └────────────┘
             │ NO
             │
             ▼
      ┌─────────────┐
      │    STOP     │
      │    ASK      │
      │    USER     │
      └─────────────┘
```

---

## 5. AIEngineService Implementation

### 5.1 Service Class Location

**File**: `app/Services/AIEngineService.php`

### 5.2 Complete Implementation

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class AIEngineService
{
    private string $baseUrl;
    private int $timeout;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('services.python.url');
        $this->timeout = config('services.python.timeout');
        $this->enabled = config('services.python.enabled');
    }

    /**
     * Check if Python service is healthy
     *
     * @return bool
     */
    public function healthCheck(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                $data = $response->json();
                return isset($data['status']) && $data['status'] === 'healthy';
            }

            return false;

        } catch (ConnectionException $e) {
            Log::warning('Python service health check failed: Connection error', [
                'error' => $e->getMessage()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::warning('Python service health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Analyze project context
     *
     * @param array $projectData
     * @return array|null
     */
    public function analyzeContext(array $projectData): ?array
    {
        if (!$this->enabled) {
            Log::info('Python service disabled, skipping context analysis');
            return null;
        }

        // Create cache key from project data
        $cacheKey = 'context_analysis_' . md5(json_encode($projectData));

        // Check cache first (valid for 1 hour)
        return Cache::remember($cacheKey, 3600, function () use ($projectData) {
            try {
                Log::info('Calling Python service for context analysis', [
                    'project_name' => $projectData['details']['name'] ?? 'Unknown'
                ]);

                $response = Http::timeout($this->timeout)
                    ->retry(3, 100, function ($exception, $request) {
                        // Only retry on connection errors, not validation errors
                        return $exception instanceof ConnectionException;
                    })
                    ->post("{$this->baseUrl}/api/context/analyze", [
                        'project_details' => $projectData['details'] ?? [],
                        'goals_context' => $projectData['goals'] ?? [],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['analysis'])) {
                        Log::info('Context analysis successful', [
                            'domain' => $data['analysis']['domain'] ?? 'unknown',
                            'complexity' => $data['analysis']['complexity'] ?? 'unknown'
                        ]);

                        return $data['analysis'];
                    }

                    Log::error('Context analysis response missing analysis field', [
                        'response' => $data
                    ]);
                    return null;
                }

                Log::error('Context analysis failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;

            } catch (ConnectionException $e) {
                Log::error('Context analysis connection failed', [
                    'error' => $e->getMessage(),
                    'url' => $this->baseUrl
                ]);
                return null;

            } catch (RequestException $e) {
                Log::error('Context analysis request failed', [
                    'error' => $e->getMessage(),
                    'response' => $e->response->body() ?? null
                ]);
                return null;

            } catch (\Exception $e) {
                Log::error('Context analysis exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return null;
            }
        });
    }

    /**
     * Generate tasks for project
     *
     * @param string $projectId
     * @param string $userId
     * @param array $context
     * @param array $analysis
     * @return array|null
     */
    public function generateTasks(
        string $projectId,
        string $userId,
        array $context,
        array $analysis
    ): ?array {
        if (!$this->enabled) {
            Log::info('Python service disabled, skipping task generation');
            return null;
        }

        try {
            Log::info('Calling Python service for task generation', [
                'project_id' => $projectId,
                'user_id' => $userId
            ]);

            // Longer timeout for task generation (can take 30-60 seconds)
            $response = Http::timeout($this->timeout * 2)
                ->retry(2, 200, function ($exception, $request) {
                    return $exception instanceof ConnectionException;
                })
                ->post("{$this->baseUrl}/api/tasks/generate", [
                    'project_id' => $projectId,
                    'user_id' => $userId,
                    'context' => $context,
                    'context_analysis' => $analysis,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!isset($data['tasks'])) {
                    Log::error('Task generation response missing tasks field', [
                        'response' => $data
                    ]);
                    return null;
                }

                // Map Python tasks to Laravel format
                $tasks = collect($data['tasks'])->map(function ($task) {
                    return $this->mapPythonTaskToLaravel($task);
                })->toArray();

                Log::info('Task generation successful', [
                    'task_count' => count($tasks),
                    'ai_tasks' => $data['metadata']['ai_tasks'] ?? 0,
                    'human_tasks' => $data['metadata']['human_tasks'] ?? 0,
                    'hitl_tasks' => $data['metadata']['hitl_tasks'] ?? 0
                ]);

                return [
                    'tasks' => $tasks,
                    'dependencies' => $data['dependencies'] ?? [],
                    'metadata' => $data['metadata'] ?? [],
                ];
            }

            Log::error('Task generation failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (ConnectionException $e) {
            Log::error('Task generation connection failed', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Task generation exception', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);
            return null;
        }
    }

    /**
     * Map Python task format to Laravel format
     *
     * @param array $pythonTask
     * @return array
     */
    private function mapPythonTaskToLaravel(array $pythonTask): array
    {
        // Convert Python's assigned_to (UPPERCASE) to Laravel's type (lowercase)
        $type = $this->convertAssignmentType($pythonTask['assigned_to'] ?? 'HUMAN');

        return [
            'id' => $pythonTask['id'], // Keep Python ID for dependency mapping
            'name' => $pythonTask['name'],
            'description' => $pythonTask['description'] ?? '',
            'type' => $type,
            'estimated_hours' => $pythonTask['estimated_hours'] ?? 0,
            'complexity' => $pythonTask['complexity'] ?? null,
            'sequence' => $pythonTask['sequence'] ?? 0,
            'status' => strtolower($pythonTask['status'] ?? 'pending'),
            'dependencies' => $pythonTask['dependencies'] ?? [],
            'subtasks' => $pythonTask['subtasks'] ?? [],
            'metadata' => [
                'ai_suitability_score' => $pythonTask['ai_suitability_score'] ?? null,
                'confidence_score' => $pythonTask['confidence_score'] ?? null,
                'validation' => $pythonTask['validation'] ?? null,
                'position' => $pythonTask['position'] ?? null,
                'python_task_id' => $pythonTask['id'] ?? null
            ],
        ];
    }

    /**
     * Convert Python's assignment type to Laravel's task type
     *
     * @param string $pythonType
     * @return string
     */
    private function convertAssignmentType(string $pythonType): string
    {
        return match(strtoupper($pythonType)) {
            'AI' => 'ai',
            'HUMAN' => 'human',
            'HITL' => 'hitl',
            'HYBRID' => 'hitl', // Map HYBRID to HITL if encountered
            default => 'human'   // Safe fallback
        };
    }

    /**
     * Validate task structure
     *
     * @param array $task
     * @return array|null
     */
    public function validateTask(array $task): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/api/tasks/validate", [
                    'task' => $task
                ]);

            if ($response->successful()) {
                return $response->json('validation');
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Task validation exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Regenerate specific tasks
     *
     * @param array $taskIds
     * @param array $existingTasks
     * @param array $context
     * @return array|null
     */
    public function regenerateTasks(
        array $taskIds,
        array $existingTasks,
        array $context
    ): ?array {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/tasks/regenerate", [
                    'task_ids' => $taskIds,
                    'existing_tasks' => $existingTasks,
                    'context' => $context,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['regenerated_tasks'])) {
                    return collect($data['regenerated_tasks'])
                        ->map(fn($task) => $this->mapPythonTaskToLaravel($task))
                        ->toArray();
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Task regeneration exception', [
                'error' => $e->getMessage(),
                'task_ids' => $taskIds
            ]);
            return null;
        }
    }
}
```

---

### 5.3 Service Configuration

**File**: `config/services.php`

```php
<?php

return [
    // ... existing services

    'python' => [
        'url' => env('PYTHON_SERVICE_URL', 'http://localhost:8001'),
        'timeout' => env('PYTHON_SERVICE_TIMEOUT', 30),
        'enabled' => env('PYTHON_SERVICE_ENABLED', true),
    ],
];
```

---

### 5.4 Environment Variables

**File**: `.env`

```env
# Python AI Engine Service
PYTHON_SERVICE_URL=http://localhost:8001
PYTHON_SERVICE_TIMEOUT=30
PYTHON_SERVICE_ENABLED=true
```

---

## 6. Livewire Wizard Implementation

### 6.1 Component Location

**File**: `app/Livewire/Projects/CreateProjectWizard.php`

### 6.2 Complete Implementation

```php
<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectGoal;
use App\Models\ProjectKpi;
use App\Models\Task;
use App\Services\AIEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateProjectWizard extends Component
{
    // Step tracking
    public int $currentStep = 1;
    public array $completedSteps = [];

    // Step 1: Project Details
    public string $name = '';
    public string $description = '';
    public string $domain = '';
    public string $timeline = '';

    // Step 2: Goals & KPIs
    public array $goals = [['description' => '', 'priority' => 1]];
    public array $kpis = [['name' => '', 'target_value' => '', 'unit' => '']];

    // Step 3: AI Analysis Results
    public ?array $aiAnalysis = null;
    public array $generatedTasks = [];
    public bool $isAnalyzing = false;
    public bool $isGeneratingTasks = false;
    public ?string $analysisError = null;
    public ?string $taskGenerationError = null;

    // Step 4: Final Review
    public array $selectedTasks = [];
    public bool $confirmCreation = false;

    protected AIEngineService $aiService;

    public function boot(AIEngineService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function rules()
    {
        $rules = [];

        if ($this->currentStep === 1) {
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'required|string|min:20',
                'domain' => 'nullable|string|max:100',
                'timeline' => 'nullable|string|max:100',
            ];
        }

        if ($this->currentStep === 2) {
            $rules = [
                'goals' => 'required|array|min:1',
                'goals.*.description' => 'required|string',
                'goals.*.priority' => 'integer|min:0',
                'kpis' => 'nullable|array',
                'kpis.*.name' => 'required_with:kpis.*.target_value|string',
                'kpis.*.target_value' => 'nullable|numeric',
                'kpis.*.unit' => 'nullable|string',
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Project name is required',
            'description.required' => 'Project description is required',
            'description.min' => 'Please provide a more detailed description (at least 20 characters)',
            'goals.required' => 'At least one goal is required',
            'goals.*.description.required' => 'Goal description cannot be empty',
        ];
    }

    /**
     * Add new goal input
     */
    public function addGoal()
    {
        $this->goals[] = ['description' => '', 'priority' => count($this->goals) + 1];
    }

    /**
     * Remove goal
     */
    public function removeGoal(int $index)
    {
        if (count($this->goals) > 1) {
            unset($this->goals[$index]);
            $this->goals = array_values($this->goals);
        }
    }

    /**
     * Add new KPI input
     */
    public function addKpi()
    {
        $this->kpis[] = ['name' => '', 'target_value' => '', 'unit' => ''];
    }

    /**
     * Remove KPI
     */
    public function removeKpi(int $index)
    {
        unset($this->kpis[$index]);
        $this->kpis = array_values($this->kpis);
    }

    /**
     * Go to next step
     */
    public function nextStep()
    {
        $this->validate();

        $this->completedSteps[] = $this->currentStep;
        $this->currentStep++;

        // Trigger AI analysis when entering Step 3
        if ($this->currentStep === 3 && !$this->aiAnalysis) {
            $this->analyzeProject();
        }
    }

    /**
     * Go to previous step
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Analyze project context with Python service
     */
    public function analyzeProject()
    {
        $this->isAnalyzing = true;
        $this->analysisError = null;

        try {
            // Prepare data for Python service
            $projectData = [
                'details' => [
                    'name' => $this->name,
                    'description' => $this->description,
                    'domain' => $this->domain,
                    'timeline' => $this->timeline,
                    'team_size' => 1, // Default for MVP
                ],
                'goals' => [
                    'goals' => array_column(
                        array_filter($this->goals, fn($g) => !empty($g['description'])),
                        'description'
                    ),
                    'success_metrics' => null,
                    'constraints' => null,
                ]
            ];

            // Call Python service
            $analysis = $this->aiService->analyzeContext($projectData);

            if ($analysis) {
                $this->aiAnalysis = $analysis;

                // Automatically generate tasks after analysis
                $this->dispatch('analysis-complete');
                $this->generateTasks();
            } else {
                // Fallback: Use rule-based analysis
                $this->aiAnalysis = $this->fallbackAnalysis();
                $this->dispatch('analysis-complete');
                $this->dispatch('analysis-used-fallback'); // Notify user
            }

        } catch (\Exception $e) {
            $this->analysisError = 'Unable to analyze project. Please try again.';
            logger()->error('Project analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isAnalyzing = false;
        }
    }

    /**
     * Generate tasks using Python service
     */
    public function generateTasks()
    {
        $this->isGeneratingTasks = true;
        $this->taskGenerationError = null;

        try {
            $tempProjectId = 'temp_' . Str::uuid();

            $context = [
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain ?: 'custom',
                'goals' => array_column(
                    array_filter($this->goals, fn($g) => !empty($g['description'])),
                    'description'
                ),
            ];

            // Call Python service
            $result = $this->aiService->generateTasks(
                $tempProjectId,
                Auth::id(),
                $context,
                $this->aiAnalysis
            );

            if ($result && isset($result['tasks'])) {
                $this->generatedTasks = $result['tasks'];
                $this->selectedTasks = array_column($result['tasks'], 'id');
                $this->dispatch('tasks-generated');
            } else {
                // Fallback: Generate basic tasks
                $this->generatedTasks = $this->fallbackTasks();
                $this->selectedTasks = array_column($this->generatedTasks, 'id');
                $this->dispatch('tasks-used-fallback'); // Notify user
            }

        } catch (\Exception $e) {
            $this->taskGenerationError = 'Unable to generate tasks. Please try again.';
            logger()->error('Task generation failed', [
                'error' => $e->getMessage()
            ]);
        } finally {
            $this->isGeneratingTasks = false;
        }
    }

    /**
     * Toggle task selection
     */
    public function toggleTask(string $taskId)
    {
        $key = array_search($taskId, $this->selectedTasks);

        if ($key !== false) {
            unset($this->selectedTasks[$key]);
            $this->selectedTasks = array_values($this->selectedTasks);
        } else {
            $this->selectedTasks[] = $taskId;
        }
    }

    /**
     * Regenerate specific tasks
     */
    public function regenerateTask(string $taskId)
    {
        try {
            $taskToRegenerate = collect($this->generatedTasks)
                ->firstWhere('id', $taskId);

            if (!$taskToRegenerate) {
                return;
            }

            $context = [
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain,
            ];

            $regenerated = $this->aiService->regenerateTasks(
                [$taskId],
                [$taskToRegenerate],
                $context
            );

            if ($regenerated && count($regenerated) > 0) {
                // Replace task in list
                $index = collect($this->generatedTasks)
                    ->search(fn($t) => $t['id'] === $taskId);

                if ($index !== false) {
                    $this->generatedTasks[$index] = $regenerated[0];
                }

                $this->dispatch('task-regenerated', taskId: $taskId);
            }

        } catch (\Exception $e) {
            logger()->error('Task regeneration failed', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
        }
    }

    /**
     * Create the project
     */
    public function createProject()
    {
        $this->validate([
            'confirmCreation' => 'accepted',
        ], [
            'confirmCreation.accepted' => 'Please confirm project creation'
        ]);

        try {
            DB::beginTransaction();

            // Create project
            $project = Project::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain ?: null,
                'timeline' => $this->timeline ?: null,
                'status' => 'active',
                'ai_analysis' => $this->aiAnalysis,
            ]);

            // Create goals
            foreach ($this->goals as $goal) {
                if (!empty($goal['description'])) {
                    ProjectGoal::create([
                        'project_id' => $project->id,
                        'description' => $goal['description'],
                        'priority' => $goal['priority'] ?? 0,
                    ]);
                }
            }

            // Create KPIs
            foreach ($this->kpis as $kpi) {
                if (!empty($kpi['name'])) {
                    ProjectKpi::create([
                        'project_id' => $project->id,
                        'name' => $kpi['name'],
                        'target_value' => $kpi['target_value'] ?? null,
                        'unit' => $kpi['unit'] ?? null,
                    ]);
                }
            }

            // Create tasks
            $pythonIdToDbId = []; // Map Python IDs to DB UUIDs

            $selectedTaskData = collect($this->generatedTasks)
                ->whereIn('id', $this->selectedTasks)
                ->values();

            foreach ($selectedTaskData as $taskData) {
                $task = Task::create([
                    'project_id' => $project->id,
                    'name' => $taskData['name'],
                    'description' => $taskData['description'],
                    'type' => $taskData['type'], // Already converted by AIEngineService
                    'estimated_hours' => $taskData['estimated_hours'],
                    'status' => 'pending',
                    'priority' => 'medium',
                    'dependencies' => $taskData['dependencies'] ?? [],
                    'metadata' => $taskData['metadata'] ?? [],
                ]);

                // Store mapping for dependency resolution
                $pythonIdToDbId[$taskData['id']] = $task->id;

                // Create subtasks if present
                if (!empty($taskData['subtasks'])) {
                    foreach ($taskData['subtasks'] as $subtaskData) {
                        Task::create([
                            'project_id' => $project->id,
                            'parent_task_id' => $task->id,
                            'name' => $subtaskData['name'],
                            'description' => $subtaskData['description'] ?? '',
                            'type' => $task->type, // Inherit from parent
                            'estimated_hours' => $subtaskData['estimated_hours'],
                            'status' => 'pending',
                            'priority' => 'medium',
                            'metadata' => [
                                'sequence' => $subtaskData['sequence'] ?? 0
                            ],
                        ]);
                    }
                }
            }

            // Update dependencies with real UUIDs
            $this->updateTaskDependencies($project, $pythonIdToDbId);

            DB::commit();

            session()->flash('success', 'Project created successfully!');
            return redirect()->route('projects.show', $project);

        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error('Project creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Failed to create project. Please try again.');
        }
    }

    /**
     * Update task dependencies with real database UUIDs
     */
    private function updateTaskDependencies(Project $project, array $pythonIdToDbId)
    {
        foreach ($project->tasks as $task) {
            if (!empty($task->dependencies)) {
                $updatedDependencies = collect($task->dependencies)
                    ->map(fn($pythonId) => $pythonIdToDbId[$pythonId] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();

                $task->update(['dependencies' => $updatedDependencies]);
            }
        }
    }

    /**
     * Fallback analysis when Python service unavailable
     */
    private function fallbackAnalysis(): array
    {
        $estimatedHours = str_word_count($this->description) * 2; // Rough estimate

        return [
            'domain' => $this->domain ?: 'unknown',
            'complexity' => 'medium',
            'estimated_task_count' => min(max(count($this->goals) * 3, 7), 20),
            'key_objectives' => array_column(
                array_filter($this->goals, fn($g) => !empty($g['description'])),
                'description'
            ),
            'challenges' => [
                'Limited AI analysis available',
                'Manual task planning may be required'
            ],
            'required_skills' => [],
            'recommendations' => [
                'Define clear milestones',
                'Break work into manageable tasks',
                'Set up regular check-ins',
            ],
            'confidence_score' => 0.7,
        ];
    }

    /**
     * Fallback task generation
     */
    private function fallbackTasks(): array
    {
        return [
            [
                'id' => 'task_001',
                'name' => 'Project Setup & Planning',
                'description' => 'Set up project structure, define requirements, create initial documentation',
                'type' => 'human',
                'estimated_hours' => 8,
                'complexity' => 'medium',
                'sequence' => 1,
                'status' => 'pending',
                'dependencies' => [],
                'subtasks' => [],
                'metadata' => [],
            ],
            [
                'id' => 'task_002',
                'name' => 'Core Implementation',
                'description' => 'Implement main features and functionality',
                'type' => 'human',
                'estimated_hours' => 40,
                'complexity' => 'high',
                'sequence' => 2,
                'status' => 'pending',
                'dependencies' => ['task_001'],
                'subtasks' => [],
                'metadata' => [],
            ],
            [
                'id' => 'task_003',
                'name' => 'Testing & Quality Assurance',
                'description' => 'Write tests, perform QA, fix bugs',
                'type' => 'human',
                'estimated_hours' => 16,
                'complexity' => 'medium',
                'sequence' => 3,
                'status' => 'pending',
                'dependencies' => ['task_002'],
                'subtasks' => [],
                'metadata' => [],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.projects.create-project-wizard');
    }
}
```

---

### 6.3 Route Registration

**File**: `routes/web.php`

```php
<?php

use App\Livewire\Projects\CreateProjectWizard;
use Illuminate\Support\Facades\Route;

// ... existing routes

Route::middleware(['auth'])->group(function () {
    Route::get('/projects/create', CreateProjectWizard::class)
        ->name('projects.create');

    // ... other project routes
});
```

---

## 7. Frontend Components

### 7.1 Wizard Blade Template

**File**: `resources/views/livewire/projects/create-project-wizard.blade.php`

```blade
<div class="max-w-4xl mx-auto py-8">
    {{-- Progress Steps --}}
    <div class="mb-8">
        <div class="flex justify-between items-center">
            @foreach([1 => 'Project Details', 2 => 'Goals & KPIs', 3 => 'AI Analysis', 4 => 'Review'] as $step => $label)
                <div class="flex-1 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                            {{ $currentStep > $step ? 'bg-green-500 text-white' : '' }}
                            {{ $currentStep === $step ? 'bg-blue-500 text-white' : '' }}
                            {{ $currentStep < $step ? 'bg-gray-200 text-gray-600' : '' }}">
                            @if($currentStep > $step)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @else
                                {{ $step }}
                            @endif
                        </div>
                        @if($step < 4)
                            <div class="flex-1 h-1 mx-2
                                {{ $currentStep > $step ? 'bg-green-500' : 'bg-gray-200' }}">
                            </div>
                        @endif
                    </div>
                    <div class="text-xs text-center mt-2">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step Content --}}
    <div class="bg-white rounded-lg shadow-lg p-6">
        @if($currentStep === 1)
            {{-- Step 1: Project Details --}}
            <h2 class="text-2xl font-bold mb-6">Project Details</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Project Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="name"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter project name">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="description" rows="4"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Describe your project in detail (at least 20 characters)..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ strlen($description) }} characters</p>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Domain
                        </label>
                        <select wire:model="domain"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Auto-detect domain...</option>
                            <option value="software_development">Software Development</option>
                            <option value="content_creation">Content Creation</option>
                            <option value="data_analysis">Data Analysis</option>
                            <option value="research">Research & Analysis</option>
                            <option value="design">Design</option>
                            <option value="marketing">Marketing Campaign</option>
                            <option value="event_planning">Event Planning</option>
                            <option value="product_launch">Product Launch</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Timeline
                        </label>
                        <input type="text" wire:model="timeline"
                               placeholder="e.g., 3 months, 12 weeks"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

        @elseif($currentStep === 2)
            {{-- Step 2: Goals & KPIs --}}
            <h2 class="text-2xl font-bold mb-6">Goals & KPIs</h2>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Project Goals <span class="text-red-500">*</span>
                    </label>
                    <p class="text-sm text-gray-500 mb-3">Define what you want to achieve with this project</p>

                    @foreach($goals as $index => $goal)
                        <div class="flex gap-2 mb-2">
                            <div class="flex-1">
                                <input type="text" wire:model="goals.{{ $index }}.description"
                                       placeholder="Goal {{ $index + 1 }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('goals.' . $index . '.description')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            @if(count($goals) > 1)
                                <button type="button" wire:click="removeGoal({{ $index }})"
                                        class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                    Remove
                                </button>
                            @endif
                        </div>
                    @endforeach

                    <button type="button" wire:click="addGoal"
                            class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        + Add Goal
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Key Performance Indicators (Optional)
                    </label>
                    <p class="text-sm text-gray-500 mb-3">Define measurable metrics to track success</p>

                    @foreach($kpis as $index => $kpi)
                        <div class="flex gap-2 mb-2">
                            <input type="text" wire:model="kpis.{{ $index }}.name"
                                   placeholder="KPI Name"
                                   class="flex-1 border-gray-300 rounded-md shadow-sm">
                            <input type="number" wire:model="kpis.{{ $index }}.target_value"
                                   placeholder="Target"
                                   step="0.01"
                                   class="w-32 border-gray-300 rounded-md shadow-sm">
                            <input type="text" wire:model="kpis.{{ $index }}.unit"
                                   placeholder="Unit"
                                   class="w-32 border-gray-300 rounded-md shadow-sm">
                            <button type="button" wire:click="removeKpi({{ $index }})"
                                    class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                Remove
                            </button>
                        </div>
                    @endforeach

                    <button type="button" wire:click="addKpi"
                            class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        + Add KPI
                    </button>
                </div>
            </div>

        @elseif($currentStep === 3)
            {{-- Step 3: AI Analysis --}}
            <h2 class="text-2xl font-bold mb-6">AI Analysis & Task Generation</h2>

            @if($isAnalyzing)
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Analyzing your project...</p>
                    <p class="text-sm text-gray-500">This may take a few seconds</p>
                </div>
            @elseif($analysisError)
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                    <p class="text-red-800">{{ $analysisError }}</p>
                    <button type="button" wire:click="analyzeProject"
                            class="mt-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Retry Analysis
                    </button>
                </div>
            @elseif($aiAnalysis)
                {{-- Display Analysis Results --}}
                <div class="space-y-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h3 class="font-semibold text-blue-900 mb-2">Domain & Complexity</h3>
                        <p><strong>Domain:</strong> {{ ucwords(str_replace('_', ' ', $aiAnalysis['domain'] ?? 'Unknown')) }}</p>
                        <p><strong>Complexity:</strong> {{ ucfirst(strtolower($aiAnalysis['complexity'] ?? 'Unknown')) }}</p>
                        <p><strong>Estimated Tasks:</strong> {{ $aiAnalysis['estimated_task_count'] ?? 'N/A' }}</p>
                        <p><strong>Confidence:</strong> {{ round(($aiAnalysis['confidence_score'] ?? 0) * 100) }}%</p>
                    </div>

                    @if(!empty($aiAnalysis['key_objectives']))
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <h3 class="font-semibold text-green-900 mb-2">Key Objectives</h3>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($aiAnalysis['key_objectives'] as $objective)
                                    <li class="text-green-800">{{ $objective }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($aiAnalysis['recommendations']))
                        <div class="bg-purple-50 border border-purple-200 rounded-md p-4">
                            <h3 class="font-semibold text-purple-900 mb-2">Recommendations</h3>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($aiAnalysis['recommendations'] as $recommendation)
                                    <li class="text-purple-800">{{ $recommendation }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Generated Tasks --}}
                @if($isGeneratingTasks)
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Generating tasks...</p>
                        <p class="text-sm text-gray-500">This may take 15-30 seconds</p>
                    </div>
                @elseif($taskGenerationError)
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <p class="text-red-800">{{ $taskGenerationError }}</p>
                        <button type="button" wire:click="generateTasks"
                                class="mt-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Retry Generation
                        </button>
                    </div>
                @elseif(count($generatedTasks) > 0)
                    <div>
                        <h3 class="font-semibold text-lg mb-3">
                            Generated Tasks ({{ count($generatedTasks) }})
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Select tasks to include in your project:</p>

                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($generatedTasks as $task)
                                <div class="border rounded-md p-4
                                    {{ in_array($task['id'], $selectedTasks) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start flex-1">
                                            <input type="checkbox" wire:click="toggleTask('{{ $task['id'] }}')"
                                                   {{ in_array($task['id'], $selectedTasks) ? 'checked' : '' }}
                                                   class="mt-1 mr-3">
                                            <div class="flex-1">
                                                <h4 class="font-semibold">{{ $task['name'] }}</h4>
                                                <p class="text-sm text-gray-600 mt-1">{{ $task['description'] }}</p>
                                                <div class="flex gap-4 mt-2 text-xs">
                                                    <span class="px-2 py-1 rounded
                                                        {{ $task['type'] === 'ai' ? 'bg-purple-100 text-purple-800' : '' }}
                                                        {{ $task['type'] === 'human' ? 'bg-blue-100 text-blue-800' : '' }}
                                                        {{ $task['type'] === 'hitl' ? 'bg-orange-100 text-orange-800' : '' }}">
                                                        {{ strtoupper($task['type']) }}
                                                    </span>
                                                    <span class="text-gray-500">⏱️ {{ $task['estimated_hours'] }}h</span>
                                                    @if(isset($task['complexity']))
                                                        <span class="text-gray-500">📊 {{ ucfirst(strtolower($task['complexity'])) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="regenerateTask('{{ $task['id'] }}')"
                                                class="ml-4 px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded">
                                            🔄 Regenerate
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 text-sm text-gray-600">
                            {{ count($selectedTasks) }} of {{ count($generatedTasks) }} tasks selected
                        </div>
                    </div>
                @endif
            @endif

        @elseif($currentStep === 4)
            {{-- Step 4: Final Review --}}
            <h2 class="text-2xl font-bold mb-6">Review & Create Project</h2>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-md p-4">
                    <h3 class="font-semibold mb-2">Project Summary</h3>
                    <p><strong>Name:</strong> {{ $name }}</p>
                    <p><strong>Description:</strong> {{ Str::limit($description, 150) }}</p>
                    <p><strong>Goals:</strong> {{ count(array_filter($goals, fn($g) => !empty($g['description']))) }}</p>
                    <p><strong>KPIs:</strong> {{ count(array_filter($kpis, fn($k) => !empty($k['name']))) }}</p>
                    <p><strong>Selected Tasks:</strong> {{ count($selectedTasks) }}</p>
                </div>

                @if($aiAnalysis)
                    <div class="bg-blue-50 rounded-md p-4">
                        <h3 class="font-semibold mb-2">AI Analysis</h3>
                        <p><strong>Domain:</strong> {{ ucwords(str_replace('_', ' ', $aiAnalysis['domain'])) }}</p>
                        <p><strong>Complexity:</strong> {{ ucfirst(strtolower($aiAnalysis['complexity'])) }}</p>
                        <p><strong>Confidence:</strong> {{ round($aiAnalysis['confidence_score'] * 100) }}%</p>
                    </div>
                @endif

                <div class="flex items-start">
                    <input type="checkbox" wire:model="confirmCreation" id="confirm" class="mt-1 mr-2">
                    <label for="confirm" class="text-sm text-gray-700">
                        I confirm that I want to create this project with {{ count($selectedTasks) }} selected tasks.
                    </label>
                </div>
                @error('confirmCreation')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        @endif
    </div>

    {{-- Navigation Buttons --}}
    <div class="flex justify-between mt-6">
        <button type="button" wire:click="previousStep"
                @if($currentStep === 1) disabled @endif
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed transition">
            ← Previous
        </button>

        @if($currentStep < 4)
            <button type="button" wire:click="nextStep"
                    class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                Next →
            </button>
        @else
            <button type="button" wire:click="createProject"
                    @if(!$confirmCreation) disabled @endif
                    class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                ✓ Create Project
            </button>
        @endif
    </div>
</div>
```

---

## 8. Error Handling & Fallbacks

### 8.1 Python Service Unavailable Strategy

**Strategy**: Graceful degradation with user notification

```php
// In AIEngineService::analyzeContext()
if ($analysis === null) {
    // Log for debugging
    Log::warning('Python service unavailable, using fallback analysis');

    // Notify Livewire component
    return null; // Triggers fallback in component
}

// In Livewire Component
if ($analysis) {
    $this->aiAnalysis = $analysis;
} else {
    // Use rule-based fallback
    $this->aiAnalysis = $this->fallbackAnalysis();

    // Notify user
    $this->dispatch('analysis-used-fallback');
}
```

**User Notification** (via Alpine.js):
```blade
<div x-data="{ showFallbackNotice: false }"
     @analysis-used-fallback.window="showFallbackNotice = true">

    <div x-show="showFallbackNotice" x-transition
         class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
        <p class="text-yellow-800">
            ⚠️ AI service temporarily unavailable. Using basic analysis instead.
            Your project will still be created successfully.
        </p>
    </div>
</div>
```

---

### 8.2 Timeout Handling

**Scenario**: Python service takes too long to respond

```php
// In config/services.php
'python' => [
    'timeout' => env('PYTHON_SERVICE_TIMEOUT', 30), // 30 seconds default
],

// In AIEngineService
$response = Http::timeout($this->timeout)
    ->retry(3, 100) // Retry 3 times with 100ms delay
    ->post(...);
```

**User Feedback**:
```blade
<div wire:loading wire:target="analyzeProject">
    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
        <p class="text-blue-800">
            Analyzing your project... This typically takes 3-8 seconds.
        </p>
        <div class="mt-2">
            <div class="animate-pulse flex space-x-2">
                <div class="h-2 bg-blue-400 rounded w-1/4"></div>
                <div class="h-2 bg-blue-400 rounded w-1/4"></div>
                <div class="h-2 bg-blue-400 rounded w-1/4"></div>
            </div>
        </div>
    </div>
</div>
```

---

### 8.3 Invalid Response Handling

**Scenario**: Python returns unexpected response format

```php
// In AIEngineService::generateTasks()
if (!isset($data['tasks']) || !is_array($data['tasks'])) {
    Log::error('Invalid task generation response', [
        'response' => $data
    ]);

    throw new \RuntimeException('Invalid response from AI service');
}

// Validate each task
foreach ($data['tasks'] as $task) {
    if (!isset($task['name']) || !isset($task['description'])) {
        Log::warning('Skipping invalid task', ['task' => $task]);
        continue; // Skip invalid tasks rather than failing completely
    }
}
```

---

### 8.4 Database Transaction Rollback

**Scenario**: Error during project creation

```php
try {
    DB::beginTransaction();

    // Create project
    $project = Project::create([...]);

    // Create goals
    foreach ($this->goals as $goal) { ... }

    // Create tasks
    foreach ($selectedTaskData as $taskData) { ... }

    DB::commit();

} catch (\Exception $e) {
    DB::rollBack();

    logger()->error('Project creation failed', [
        'error' => $e->getMessage(),
        'user_id' => Auth::id()
    ]);

    session()->flash('error', 'Failed to create project. Please try again.');
}
```

---

## 9. Testing Requirements

### 9.1 Unit Tests

**File**: `tests/Unit/Services/AIEngineServiceTest.php`

```php
<?php

namespace Tests\Unit\Services;

use App\Services\AIEngineService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIEngineServiceTest extends TestCase
{
    public function test_health_check_success()
    {
        Http::fake([
            '*/health' => Http::response(['status' => 'healthy'], 200)
        ]);

        $service = new AIEngineService();
        $this->assertTrue($service->healthCheck());
    }

    public function test_health_check_failure()
    {
        Http::fake([
            '*/health' => Http::response([], 500)
        ]);

        $service = new AIEngineService();
        $this->assertFalse($service->healthCheck());
    }

    public function test_context_analysis_success()
    {
        Http::fake([
            '*/api/context/analyze' => Http::response([
                'analysis' => [
                    'domain' => 'SOFTWARE_DEVELOPMENT',
                    'complexity' => 'HIGH',
                    'estimated_task_count' => 30,
                    'key_objectives' => ['Objective 1'],
                    'challenges' => ['Challenge 1'],
                    'required_skills' => [],
                    'recommendations' => ['Recommendation 1'],
                    'confidence_score' => 0.88
                ],
                'status' => 'success'
            ], 200)
        ]);

        $service = new AIEngineService();
        $result = $service->analyzeContext([
            'details' => ['name' => 'Test', 'description' => 'Test project'],
            'goals' => ['goals' => ['Goal 1']]
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('SOFTWARE_DEVELOPMENT', $result['domain']);
        $this->assertEquals('HIGH', $result['complexity']);
    }

    public function test_task_generation_success()
    {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task_001',
                        'name' => 'Task 1',
                        'description' => 'Description',
                        'assigned_to' => 'HUMAN',
                        'estimated_hours' => 8,
                        'complexity' => 'MEDIUM',
                        'sequence' => 1,
                        'status' => 'PENDING',
                        'dependencies' => [],
                        'subtasks' => [],
                        'metadata' => []
                    ]
                ],
                'dependencies' => [],
                'metadata' => []
            ], 200)
        ]);

        $service = new AIEngineService();
        $result = $service->generateTasks('proj-123', 'user-456', [], []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tasks', $result);
        $this->assertCount(1, $result['tasks']);
        $this->assertEquals('human', $result['tasks'][0]['type']); // Converted to lowercase
    }

    public function test_assignment_type_conversion()
    {
        $service = new \ReflectionClass(AIEngineService::class);
        $method = $service->getMethod('convertAssignmentType');
        $method->setAccessible(true);

        $instance = new AIEngineService();

        $this->assertEquals('ai', $method->invoke($instance, 'AI'));
        $this->assertEquals('human', $method->invoke($instance, 'HUMAN'));
        $this->assertEquals('hitl', $method->invoke($instance, 'HITL'));
        $this->assertEquals('human', $method->invoke($instance, 'UNKNOWN'));
    }
}
```

---

### 9.2 Feature Tests

**File**: `tests/Feature/ProjectCreationTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_with_ai_tasks()
    {
        $user = User::factory()->create();

        Http::fake([
            '*/health' => Http::response(['status' => 'healthy'], 200),
            '*/api/context/analyze' => Http::response([
                'analysis' => [
                    'domain' => 'SOFTWARE_DEVELOPMENT',
                    'complexity' => 'MEDIUM',
                    'estimated_task_count' => 10,
                    'key_objectives' => ['Build app'],
                    'challenges' => [],
                    'required_skills' => [],
                    'recommendations' => [],
                    'confidence_score' => 0.85
                ]
            ], 200),
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task_001',
                        'name' => 'Setup project',
                        'description' => 'Initialize project structure',
                        'assigned_to' => 'HUMAN',
                        'estimated_hours' => 8,
                        'complexity' => 'LOW',
                        'sequence' => 1,
                        'status' => 'PENDING',
                        'dependencies' => [],
                        'subtasks' => [],
                        'metadata' => []
                    ]
                ],
                'dependencies' => [],
                'metadata' => []
            ], 200)
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Projects\CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'A detailed test project description')
            ->set('goals.0.description', 'Goal 1')
            ->call('nextStep') // Step 1 → 2
            ->call('nextStep') // Step 2 → 3 (triggers analysis)
            ->assertSet('aiAnalysis', fn($analysis) => $analysis !== null)
            ->call('nextStep') // Step 3 → 4
            ->set('confirmCreation', true)
            ->call('createProject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', ['name' => 'Test Project']);
        $this->assertDatabaseHas('project_goals', ['description' => 'Goal 1']);
        $this->assertDatabaseHas('tasks', ['name' => 'Setup project', 'type' => 'human']);
    }

    public function test_project_creation_works_without_python_service()
    {
        Http::fake([
            '*/health' => Http::response([], 500),
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Projects\CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'A detailed test project description')
            ->set('goals.0.description', 'Goal 1')
            ->call('nextStep')
            ->call('nextStep') // Triggers fallback analysis
            ->assertSet('aiAnalysis', fn($analysis) => $analysis !== null)
            ->assertSet('aiAnalysis.confidence_score', 0.7); // Fallback confidence

        // Should still be able to create project
    }
}
```

---

### 9.3 Integration Tests

**File**: `tests/Integration/PythonServiceIntegrationTest.php`

```php
<?php

namespace Tests\Integration;

use App\Services\AIEngineService;
use Tests\TestCase;

/**
 * These tests require Python service to be running
 *
 * @group integration
 */
class PythonServiceIntegrationTest extends TestCase
{
    public function test_python_service_is_reachable()
    {
        $service = app(AIEngineService::class);
        $healthy = $service->healthCheck();

        $this->assertTrue($healthy, 'Python service should be reachable at ' . config('services.python.url'));
    }

    public function test_can_analyze_real_project_context()
    {
        $service = app(AIEngineService::class);

        $result = $service->analyzeContext([
            'details' => [
                'name' => 'E-commerce Platform',
                'description' => 'Build a full-stack e-commerce platform with React and Node.js',
                'domain' => 'software_development',
                'timeline' => '3 months',
                'team_size' => 3
            ],
            'goals' => [
                'goals' => ['Launch MVP', 'Get 1000 users']
            ]
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('domain', $result);
        $this->assertArrayHasKey('complexity', $result);
        $this->assertArrayHasKey('estimated_task_count', $result);
        $this->assertGreaterThanOrEqual(0.6, $result['confidence_score']);
    }

    public function test_can_generate_real_tasks()
    {
        $service = app(AIEngineService::class);

        $result = $service->generateTasks(
            'temp-project-123',
            'user-456',
            [
                'name' => 'E-commerce Platform',
                'description' => 'Build a full-stack e-commerce platform',
                'domain' => 'software_development',
                'goals' => ['Launch MVP']
            ],
            [
                'domain' => 'SOFTWARE_DEVELOPMENT',
                'complexity' => 'HIGH',
                'estimated_task_count' => 20
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tasks', $result);
        $this->assertGreaterThanOrEqual(7, count($result['tasks']));
        $this->assertLessThanOrEqual(50, count($result['tasks']));

        // Verify task structure
        foreach ($result['tasks'] as $task) {
            $this->assertArrayHasKey('name', $task);
            $this->assertArrayHasKey('description', $task);
            $this->assertArrayHasKey('type', $task);
            $this->assertContains($task['type'], ['ai', 'human', 'hitl']);
        }
    }
}
```

**Running Integration Tests**:
```bash
# Start Python service first
cd d:/collabflow/python-service
python dev.py

# In another terminal
cd d:/collabflow/laravel-app
php artisan test --filter=PythonServiceIntegrationTest --group=integration
```

---

## 10. Configuration Setup

### 10.1 Laravel Environment Variables

**File**: `.env`

```env
# Application
APP_NAME="CollabFlow"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://laravel-app.test

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=collabflow
DB_USERNAME=collabflow_user
DB_PASSWORD=your_secure_password

# Python AI Engine Service
PYTHON_SERVICE_URL=http://localhost:8001
PYTHON_SERVICE_TIMEOUT=30
PYTHON_SERVICE_ENABLED=true

# Cache & Queue
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

---

### 10.2 Python Service Environment Variables

**File**: `d:/collabflow/python-service/.env`

```env
# Service Configuration
SERVICE_NAME=collabflow-python
DEBUG=false
ENVIRONMENT=development
LOG_LEVEL=INFO

# API Keys (REQUIRED)
ANTHROPIC_API_KEY=sk-ant-your-key-here

# Chroma Configuration
CHROMA_HOST=localhost
CHROMA_PORT=8000
CHROMA_ENABLED=true

# CORS Origins (CRITICAL: Must include Laravel URL)
ALLOWED_ORIGINS=["http://laravel-app.test","http://localhost:8001","http://localhost"]

# LLM Configuration
DEFAULT_MODEL=claude-3-5-sonnet-20241022
HAIKU_MODEL=claude-3-haiku-20240307
MAX_TOKENS=4000
TEMPERATURE=0.7

# Task Generation Limits
MIN_TASKS=7
MAX_TASKS=50
```

---

### 10.3 Service Provider Registration

**File**: `app/Providers/AppServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Services\AIEngineService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register AIEngineService as singleton
        $this->app->singleton(AIEngineService::class, function ($app) {
            return new AIEngineService();
        });
    }

    public function boot(): void
    {
        //
    }
}
```

---

## 11. Ambiguity Resolution Protocol

### 11.1 Escalation Checklist

Before asking the user, ask yourself:

1. **Is this documented?**
   - Check `01_Python_Service_Complete_Design.md`
   - Check `03_CollabFlow_Laravel_Python_Integration_UPDATED.md`
   - Check Laravel documentation
   - If yes: proceed with documented approach

2. **Is this a standard Laravel pattern?**
   - Transaction handling → Yes, use DB::transaction
   - Validation → Yes, use Laravel validation
   - Error logging → Yes, use Log facade
   - If yes: proceed with standard pattern

3. **Will this cause data loss or corruption?**
   - If no: proceed autonomously
   - If yes: STOP and ask

4. **Is this a UI/UX decision?**
   - Colors, spacing, wording → Proceed autonomously
   - Data flow, business logic → May need to ask

5. **Can I implement a safe fallback?**
   - If yes: implement fallback and log decision
   - If no: STOP and ask

---

### 11.2 When to STOP - Decision Matrix

| Scenario | Action | Reason |
|----------|--------|--------|
| Python returns unknown enum value | **STOP** | Data integrity risk |
| Python response missing required field | **PROCEED** | Use fallback default |
| UI button placement unclear | **PROCEED** | UI decision, no data risk |
| Database schema change needed | **STOP** | Structural change required |
| Case conversion needed | **PROCEED** | Documented pattern |
| New table relationship needed | **STOP** | Architecture decision |
| Error message wording | **PROCEED** | UI text, low risk |
| Security/auth logic unclear | **STOP** | Security risk |
| Caching strategy unclear | **PROCEED** | Use documented approach |
| Business rule conflict | **STOP** | Needs user decision |

---

### 11.3 How to Ask for Clarity

**Template for Escalation**:

```
⚠️ IMPLEMENTATION BLOCKED - NEEDS CLARITY

**Issue**: [Describe the problem]

**Context**: [What you were trying to do]

**Question**: [Specific question with options]

**Options Considered**:
A) [Option A with pros/cons]
B) [Option B with pros/cons]
C) [Option C with pros/cons]

**Recommendation**: [Your suggested approach if any]

**Impact if blocked**: [What can't proceed without this decision]

**Documentation consulted**:
- [ ] 01_Python_Service_Complete_Design.md
- [ ] 03_CollabFlow_Laravel_Python_Integration_UPDATED.md
- [ ] Laravel documentation

Please advise which approach to take.
```

**Example**:

```
⚠️ IMPLEMENTATION BLOCKED - NEEDS CLARITY

**Issue**: Python service returns `assigned_to: "COLLABORATIVE"` which is not in Laravel's task type enum.

**Context**: During task generation, Python service returned a task with assignment type "COLLABORATIVE" but Laravel schema only accepts ['ai', 'human', 'hitl'].

**Question**: How should I handle the "COLLABORATIVE" assignment type?

**Options Considered**:
A) Map "COLLABORATIVE" to "hitl" (Human-In-The-Loop)
   - Pros: Closest semantic match
   - Cons: May not accurately represent collaborative work

B) Map "COLLABORATIVE" to "human" as fallback
   - Pros: Safe default
   - Cons: Loses information about collaboration aspect

C) Add "collaborative" to Laravel's task type enum
   - Pros: Preserves original intent
   - Cons: Requires database migration and schema change

D) Reject the task and log error
   - Pros: Ensures data integrity
   - Cons: User loses this task

**Recommendation**: Option A (map to "hitl") seems most appropriate as HITL represents AI+Human collaboration.

**Impact if blocked**: Cannot complete task import, wizard stuck at Step 3.

**Documentation consulted**:
- [x] 01_Python_Service_Complete_Design.md - No mention of "COLLABORATIVE"
- [x] 03_CollabFlow_Laravel_Python_Integration_UPDATED.md - Specifies only ai/human/hitl
- [x] Laravel documentation - Enum validation confirmed

Please advise which approach to take.
```

---

## 12. Implementation Checklist

### 12.1 Pre-Implementation

- [ ] Python service is running on localhost:8001
- [ ] ChromaDB is running on localhost:8000 (optional)
- [ ] Laravel dependencies installed (`composer install`)
- [ ] Frontend dependencies installed (`npm install`)
- [ ] Database migrated (`php artisan migrate`)
- [ ] Environment variables configured

---

### 12.2 Core Implementation

#### AIEngineService
- [ ] Create `app/Services/AIEngineService.php`
- [ ] Implement `healthCheck()` method
- [ ] Implement `analyzeContext()` method with caching
- [ ] Implement `generateTasks()` method
- [ ] Implement `mapPythonTaskToLaravel()` method
- [ ] Implement `convertAssignmentType()` method
- [ ] Implement `validateTask()` method
- [ ] Implement `regenerateTasks()` method
- [ ] Add comprehensive error handling
- [ ] Add logging for all operations

#### Configuration
- [ ] Add Python service config to `config/services.php`
- [ ] Add environment variables to `.env`
- [ ] Register AIEngineService in AppServiceProvider
- [ ] Update Python service CORS origins

#### Livewire Component
- [ ] Create `app/Livewire/Projects/CreateProjectWizard.php`
- [ ] Implement step navigation (nextStep, previousStep)
- [ ] Implement goal/KPI management (add, remove)
- [ ] Implement `analyzeProject()` method
- [ ] Implement `generateTasks()` method
- [ ] Implement `toggleTask()` method
- [ ] Implement `regenerateTask()` method
- [ ] Implement `createProject()` method with transaction
- [ ] Implement `fallbackAnalysis()` method
- [ ] Implement `fallbackTasks()` method
- [ ] Add validation rules for all steps

#### Blade Templates
- [ ] Create `resources/views/livewire/projects/create-project-wizard.blade.php`
- [ ] Implement progress indicator (4 steps)
- [ ] Implement Step 1: Project Details form
- [ ] Implement Step 2: Goals & KPIs form
- [ ] Implement Step 3: AI Analysis display
- [ ] Implement Step 3: Task list with selection
- [ ] Implement Step 4: Review summary
- [ ] Add loading states for all async operations
- [ ] Add error displays for all failure scenarios
- [ ] Add task type badges (ai/human/hitl)
- [ ] Add regenerate button for each task

#### Routes
- [ ] Add wizard route to `routes/web.php`
- [ ] Add authentication middleware
- [ ] Test route accessibility

---

### 12.3 Testing

#### Unit Tests
- [ ] Create `tests/Unit/Services/AIEngineServiceTest.php`
- [ ] Test `healthCheck()` success
- [ ] Test `healthCheck()` failure
- [ ] Test `analyzeContext()` success
- [ ] Test `analyzeContext()` with invalid response
- [ ] Test `generateTasks()` success
- [ ] Test `generateTasks()` with invalid response
- [ ] Test `convertAssignmentType()` for all types
- [ ] Test `mapPythonTaskToLaravel()` mapping
- [ ] Run: `php artisan test --filter=AIEngineServiceTest`

#### Feature Tests
- [ ] Create `tests/Feature/ProjectCreationTest.php`
- [ ] Test complete wizard flow with Python service
- [ ] Test wizard flow with Python service unavailable
- [ ] Test task selection/deselection
- [ ] Test project creation with subtasks
- [ ] Test dependency mapping
- [ ] Run: `php artisan test --filter=ProjectCreationTest`

#### Integration Tests
- [ ] Create `tests/Integration/PythonServiceIntegrationTest.php`
- [ ] Test real Python service health check
- [ ] Test real context analysis
- [ ] Test real task generation
- [ ] Verify task structure matches expectations
- [ ] Run: `php artisan test --group=integration`

#### Manual Testing
- [ ] Navigate to `/projects/create`
- [ ] Fill out Step 1 with valid data
- [ ] Verify Step 2 goal/KPI add/remove works
- [ ] Verify Step 3 triggers AI analysis
- [ ] Verify tasks are displayed correctly
- [ ] Verify task selection works
- [ ] Verify task regeneration works
- [ ] Verify Step 4 review displays correctly
- [ ] Create project and verify database records
- [ ] Test with Python service stopped (fallback)
- [ ] Test with invalid input data
- [ ] Test with very long descriptions
- [ ] Test with special characters in names

---

### 12.4 Deployment Preparation

- [ ] Update README with setup instructions
- [ ] Document environment variables
- [ ] Create deployment script
- [ ] Test on fresh database
- [ ] Verify CORS configuration
- [ ] Check error logging
- [ ] Verify transaction rollbacks work
- [ ] Load test with multiple concurrent users

---

## 13. Compliance Requirements

### 13.1 MUST Requirements

These are non-negotiable requirements that MUST be implemented:

1. **Laravel Database Schema Compliance**
   - ✅ MUST use Laravel database schema as-is
   - ✅ MUST NOT modify schema to match Python output
   - ✅ MUST store extra Python fields in `metadata` JSON column
   - ✅ MUST convert Python enum values to Laravel enum values

2. **Task Type Enum Compliance**
   - ✅ MUST use lowercase: `ai`, `human`, `hitl`
   - ✅ MUST convert Python's uppercase (`AI`, `HUMAN`, `HITL`) to lowercase
   - ✅ MUST map any unknown types to `human` as safe fallback

3. **Graceful Degradation**
   - ✅ MUST handle Python service unavailability gracefully
   - ✅ MUST provide fallback analysis when Python service fails
   - ✅ MUST provide fallback tasks when Python service fails
   - ✅ MUST notify user when fallback is used
   - ✅ MUST NOT block project creation if Python service unavailable

4. **Data Validation**
   - ✅ MUST validate all Python responses before database insertion
   - ✅ MUST reject invalid Python responses
   - ✅ MUST log all validation failures
   - ✅ MUST use Laravel validation for user input

5. **Transaction Handling**
   - ✅ MUST wrap project creation in database transaction
   - ✅ MUST rollback on any error
   - ✅ MUST log transaction failures
   - ✅ MUST NOT leave partial data in database

6. **User Feedback**
   - ✅ MUST provide loading indicators for all async operations
   - ✅ MUST display error messages for all failure scenarios
   - ✅ MUST show success confirmation on project creation
   - ✅ MUST indicate when fallback mode is active

7. **Logging**
   - ✅ MUST log all Python service calls
   - ✅ MUST log all errors with stack traces
   - ✅ MUST log all fallback activations
   - ✅ MUST NOT log sensitive data (API keys, passwords)

8. **Security**
   - ✅ MUST authenticate user before allowing project creation
   - ✅ MUST associate projects with authenticated user
   - ✅ MUST validate user input on server side
   - ✅ MUST sanitize output in Blade templates

---

### 13.2 SHOULD Requirements

These are strongly recommended but not blocking:

1. **Performance**
   - ⚠️ SHOULD cache Python service responses
   - ⚠️ SHOULD use HTTP retry logic for transient failures
   - ⚠️ SHOULD optimize database queries with eager loading

2. **User Experience**
   - ⚠️ SHOULD show real-time progress during task generation
   - ⚠️ SHOULD allow users to edit tasks before creation
   - ⚠️ SHOULD save wizard state for multi-session completion

3. **Monitoring**
   - ⚠️ SHOULD monitor Python service uptime
   - ⚠️ SHOULD track task generation success rates
   - ⚠️ SHOULD alert on high failure rates

---

### 13.3 Compliance Verification

Before considering implementation complete, verify:

```bash
# Run all tests
php artisan test

# Test with Python service running
PYTHON_SERVICE_ENABLED=true php artisan test --group=integration

# Test with Python service disabled
PYTHON_SERVICE_ENABLED=false php artisan test

# Test graceful degradation
# 1. Stop Python service
# 2. Create project through wizard
# 3. Verify fallback is used
# 4. Verify project is created successfully

# Check logs
tail -f storage/logs/laravel.log
```

**Expected Results**:
- All tests pass
- Project creation works with and without Python service
- Fallback mode activates when Python unavailable
- No errors logged during normal operation
- All database constraints satisfied

---

## 14. Troubleshooting Guide

### 14.1 Python Service Not Reachable

**Symptoms**:
- Health check fails
- Context analysis returns null
- Task generation returns null

**Diagnosis**:
```bash
# Check if Python service is running
curl http://localhost:8001/health

# Check Python service logs
cd d:/collabflow/python-service
python dev.py
# Look for startup errors
```

**Solutions**:

1. **Service not running**: Start Python service
   ```bash
   cd d:/collabflow/python-service
   python dev.py
   ```

2. **Port conflict**: Change port in `.env`
   ```env
   # In Laravel .env
   PYTHON_SERVICE_URL=http://localhost:8002

   # In Python .env (update main.py if needed)
   API_PORT=8002
   ```

3. **CORS issues**: Update Python CORS origins
   ```env
   # In Python .env
   ALLOWED_ORIGINS=["http://laravel-app.test"]
   ```

4. **Firewall blocking**: Allow port 8001
   ```bash
   # Windows
   netsh advfirewall firewall add rule name="Python Service" dir=in action=allow protocol=TCP localport=8001
   ```

---

### 14.2 Task Type Mismatch Errors

**Symptoms**:
- Database constraint violation on task creation
- Error: `invalid input value for enum task_type`

**Diagnosis**:
```php
// Check what Python is returning
Log::info('Python task type', ['type' => $pythonTask['assigned_to']]);

// Check what Laravel is trying to insert
Log::info('Laravel task type', ['type' => $taskData['type']]);
```

**Solutions**:

1. **Verify conversion in AIEngineService**:
   ```php
   private function convertAssignmentType(string $pythonType): string
   {
       return match(strtoupper($pythonType)) {
           'AI' => 'ai',
           'HUMAN' => 'human',
           'HITL' => 'hitl',
           default => 'human' // Safe fallback
       };
   }
   ```

2. **Check database enum definition**:
   ```sql
   -- In migration
   $table->enum('type', ['ai', 'human', 'hitl']);
   ```

3. **Update Python service** if it's returning unexpected values

---

### 14.3 Timeout Errors

**Symptoms**:
- Task generation times out
- HTTP 408 Request Timeout

**Diagnosis**:
```php
// Check current timeout
Log::info('Timeout setting', ['timeout' => config('services.python.timeout')]);
```

**Solutions**:

1. **Increase timeout**:
   ```env
   # In Laravel .env
   PYTHON_SERVICE_TIMEOUT=60  # Increase to 60 seconds
   ```

2. **Use streaming endpoint** instead of synchronous:
   ```php
   // TODO: Implement streaming task generation
   // POST /api/tasks/generate/stream
   ```

3. **Reduce task count** if generation is slow:
   ```php
   // In project analysis
   $analysis['estimated_task_count'] = min($analysis['estimated_task_count'], 20);
   ```

---

### 14.4 Database Transaction Failures

**Symptoms**:
- Partial data in database
- Project created but no tasks
- Tasks created without project

**Diagnosis**:
```php
// Check transaction logs
Log::info('Transaction started');
Log::info('Project created', ['project_id' => $project->id]);
Log::info('Tasks created', ['count' => count($tasks)]);
```

**Solutions**:

1. **Ensure proper transaction wrapper**:
   ```php
   try {
       DB::beginTransaction();

       // All database operations here

       DB::commit();
   } catch (\Exception $e) {
       DB::rollBack();
       throw $e; // Re-throw after rollback
   }
   ```

2. **Check foreign key constraints**:
   ```sql
   -- Verify project exists before creating tasks
   SELECT * FROM projects WHERE id = ?;
   ```

3. **Check for deadlocks**:
   ```php
   // Add deadlock retry logic
   DB::transaction(function () {
       // Database operations
   }, 3); // Retry up to 3 times
   ```

---

### 14.5 Validation Errors

**Symptoms**:
- 422 Unprocessable Entity
- Validation messages not displaying

**Diagnosis**:
```php
// Check validation rules
$rules = $this->rules();
Log::info('Validation rules', ['rules' => $rules]);

// Check input data
Log::info('Input data', ['data' => $this->all()]);
```

**Solutions**:

1. **Ensure rules match current step**:
   ```php
   public function rules()
   {
       return match($this->currentStep) {
           1 => [...],
           2 => [...],
           default => []
       };
   }
   ```

2. **Display validation errors in Blade**:
   ```blade
   @error('name')
       <span class="text-red-500 text-sm">{{ $message }}</span>
   @enderror
   ```

3. **Check for array validation**:
   ```php
   'goals' => 'required|array|min:1',
   'goals.*.description' => 'required|string',
   ```

---

### 14.6 Fallback Not Triggering

**Symptoms**:
- Error displayed instead of using fallback
- Project creation blocked

**Diagnosis**:
```php
// Check if Python service enabled flag is correct
Log::info('Python enabled', ['enabled' => config('services.python.enabled')]);

// Check if null response is handled
if ($analysis === null) {
    Log::info('Using fallback analysis');
}
```

**Solutions**:

1. **Verify fallback logic**:
   ```php
   if ($analysis) {
       $this->aiAnalysis = $analysis;
   } else {
       $this->aiAnalysis = $this->fallbackAnalysis();
       $this->dispatch('analysis-used-fallback');
   }
   ```

2. **Check Python service enabled flag**:
   ```env
   PYTHON_SERVICE_ENABLED=true
   ```

3. **Ensure fallback methods exist**:
   ```php
   private function fallbackAnalysis(): array { ... }
   private function fallbackTasks(): array { ... }
   ```

---

### 14.7 Common Error Messages & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| `Connection refused` | Python service not running | Start `python dev.py` |
| `CORS policy` | CORS origins not configured | Add Laravel URL to Python CORS |
| `Enum constraint violation` | Task type not converted to lowercase | Check `convertAssignmentType()` |
| `Foreign key constraint` | Project not created before tasks | Wrap in transaction |
| `Validation failed` | Input doesn't match rules | Check validation rules for current step |
| `Timeout` | Task generation too slow | Increase timeout or reduce task count |
| `Unauthorized` | User not authenticated | Add `auth` middleware |
| `500 Server Error` | Unhandled exception | Check Laravel logs |

---

## Conclusion

This guide provides complete implementation instructions for integrating the Laravel application with the Python AI service. Follow the implementation checklist, adhere to all compliance requirements, and use the ambiguity resolution protocol when encountering unclear requirements.

**Key Success Factors**:
1. Laravel database schema is source of truth
2. Graceful degradation when Python unavailable
3. Comprehensive error handling and logging
4. User feedback for all operations
5. Testing at unit, feature, and integration levels

**Next Steps**:
1. Ensure Python service is running and accessible
2. Implement AIEngineService with all methods
3. Implement Livewire wizard component
4. Create Blade templates with all 4 steps
5. Write comprehensive tests
6. Perform manual testing
7. Deploy to staging environment

---

**Document Version**: 1.0
**Last Updated**: November 9, 2025
**Ready for Implementation**: ✅ Yes
