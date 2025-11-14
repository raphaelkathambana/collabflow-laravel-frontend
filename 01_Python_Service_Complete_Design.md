# CollabFlow Python Service - Complete Design Specification

**Version:** 1.0
**Date:** November 9, 2025
**Status:** Production Ready (70% Complete)
**Purpose:** Comprehensive technical specification for the CollabFlow AI-powered task generation and workflow orchestration service

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [System Architecture](#2-system-architecture)
3. [Technology Stack](#3-technology-stack)
4. [API Endpoints Reference](#4-api-endpoints-reference)
5. [Data Models & Schemas](#5-data-models--schemas)
6. [Service Layer Architecture](#6-service-layer-architecture)
7. [Routing Layer](#7-routing-layer)
8. [Middleware & Error Handling](#8-middleware--error-handling)
9. [Configuration Management](#9-configuration-management)
10. [External Dependencies](#10-external-dependencies)
11. [Testing Strategy](#11-testing-strategy)
12. [Deployment & Operations](#12-deployment--operations)
13. [Implementation Status](#13-implementation-status)
14. [Appendices](#14-appendices)

---

## 1. Executive Summary

### 1.1 Service Purpose

The CollabFlow Python Service is a **FastAPI-based AI-powered task generation and workflow orchestration engine** that:

- Analyzes project context using AI (Claude Sonnet 4.5)
- Generates hierarchical task breakdowns with dependencies
- Classifies tasks as AI-suitable, human-required, or hybrid (HITL - Human-In-The-Loop)
- Validates tasks against SMART criteria
- Manages knowledge retrieval using vector database (ChromaDB)
- Provides both synchronous and streaming API endpoints

### 1.2 Key Capabilities

1. **Context Analysis**
   - Domain classification (13 domains supported)
   - Complexity assessment (4 levels: LOW, MEDIUM, HIGH, VERY_HIGH)
   - Expertise identification
   - AI-powered deep analysis with fallback

2. **Task Generation**
   - Multi-tier LLM approach (Sonnet for strategy, Haiku for tactics)
   - Automatic task decomposition for complex tasks
   - Dependency graph generation with cycle detection
   - SMART validation

3. **Knowledge Management**
   - RAG (Retrieval-Augmented Generation) with ChromaDB
   - Semantic search for relevant context
   - Domain-specific knowledge templates
   - Smart document chunking

4. **Integration**
   - RESTful API design
   - Server-Sent Events (SSE) for streaming
   - Comprehensive error handling
   - Graceful fallback when AI unavailable

### 1.3 Current Implementation Status

**Overall Completion: 70%**

| Component | Status | Completion |
|-----------|--------|-----------|
| Context Analysis | ✅ Complete | 100% |
| Task Generation | ✅ Complete | 100% |
| Task Validation | ✅ Complete | 100% |
| Knowledge Management | ✅ Complete | 100% |
| API Endpoints (Context/Tasks) | ✅ Complete | 100% |
| Distribution API | ⚠️ Stub Only | 0% |
| Orchestration API | ⚠️ Stub Only | 0% |
| Testing | 🔄 Partial | 50% |
| Documentation | 🔄 Partial | 60% |

---

## 2. System Architecture

### 2.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   Laravel Application                        │
│                   (HTTP Client)                              │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTP/REST
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              FastAPI Application (Python)                    │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Routers    │  │  Middleware  │  │   Services   │     │
│  │  (API Layer) │←→│   (CORS,     │←→│  (Business   │     │
│  │              │  │   Errors)    │  │   Logic)     │     │
│  └──────────────┘  └──────────────┘  └──────┬───────┘     │
│                                              │              │
└──────────────────────────────────────────────┼──────────────┘
                                               │
                     ┌─────────────────────────┼─────────────────┐
                     │                         │                 │
                     ▼                         ▼                 ▼
           ┌─────────────────┐    ┌──────────────────┐  ┌──────────────┐
           │  Anthropic API  │    │    ChromaDB      │  │ Sentence     │
           │  Claude 4.5     │    │  (Vector Store)  │  │ Transformers │
           │  - Sonnet       │    │                  │  │ (Embeddings) │
           │  - Haiku        │    │                  │  │              │
           └─────────────────┘    └──────────────────┘  └──────────────┘
```

### 2.2 Component Architecture

```
app/
├── main.py                          # FastAPI application entry
├── config.py                        # Configuration management
│
├── models/                          # Data models (Pydantic)
│   ├── context.py                   # ProjectContext, ContextAnalysis
│   ├── task.py                      # Task, Subtask, Dependency
│   └── api.py                       # Request/Response models
│
├── services/                        # Business logic layer
│   ├── context_analyzer.py          # AI context analysis
│   ├── task_generator.py            # Multi-tier task generation
│   ├── validator.py                 # SMART validation
│   └── knowledge_manager.py         # RAG with ChromaDB
│
├── utils/                           # Utility modules
│   ├── llm_orchestrator.py          # LLM client management
│   └── prompts.py                   # Centralized prompts
│
├── routers/                         # API routing layer
│   ├── context.py                   # /api/context/*
│   ├── tasks.py                     # /api/tasks/*
│   ├── distribution.py              # /api/distribution/* (stub)
│   └── orchestration.py             # /api/orchestration/* (stub)
│
└── middleware/                      # Request/response middleware
    ├── cors.py                      # CORS configuration
    └── error_handler.py             # Global error handling
```

### 2.3 Data Flow Diagram

```
User Input (Laravel)
    │
    ▼
[POST /api/context/analyze]
    │
    ├─→ ContextAnalyzerService
    │   ├─→ Domain Classification (rule-based)
    │   ├─→ Complexity Analysis (multi-factor)
    │   ├─→ Deep Analysis (Claude Sonnet) ─→ Anthropic API
    │   ├─→ Expertise Identification
    │   └─→ Task Count Estimation
    │
    ▼
ContextAnalysis Result
    │
    ▼
[POST /api/tasks/generate]
    │
    ├─→ TaskGeneratorService
    │   │
    │   ├─→ KnowledgeManager.query_relevant_context()
    │   │   └─→ ChromaDB (semantic search)
    │   │
    │   ├─→ High-Level Task Generation (Claude Sonnet)
    │   │   └─→ Anthropic API
    │   │
    │   ├─→ Task Decomposition (Claude Haiku)
    │   │   └─→ Anthropic API
    │   │
    │   ├─→ Task Classification (Claude Haiku)
    │   │   └─→ Anthropic API
    │   │
    │   ├─→ TaskValidator.validate_task()
    │   │   └─→ SMART criteria checks
    │   │
    │   └─→ Dependency Generation (Claude Haiku)
    │       └─→ Anthropic API
    │
    ▼
TaskGenerationResponse
    │
    ▼
Laravel Stores in PostgreSQL
```

### 2.4 Three-Tier LLM Strategy

**Tier 1: Strategic Planning (Claude Sonnet 4.5)**
- Context deep analysis
- High-level task generation
- Task regeneration
- Complex reasoning tasks
- Higher cost, higher quality

**Tier 2: Tactical Decomposition (Claude Haiku 4.5)**
- Task decomposition into subtasks
- Quick tactical decisions
- Medium complexity tasks

**Tier 3: Classification (Claude Haiku 4.5)**
- AI/Human/HITL task classification
- Fast categorization
- High-volume operations
- Lower cost, fast response

---

## 3. Technology Stack

### 3.1 Core Framework

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| **Framework** | FastAPI | 0.115.0 | Async web framework |
| **Server** | Uvicorn | 0.32.0 | ASGI server |
| **Validation** | Pydantic | 2.9.0 | Data validation & serialization |
| **Python** | Python | 3.11+ | Runtime |

### 3.2 AI & ML Stack

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| **LLM Provider** | Anthropic Claude | 0.39.0 | AI task generation |
| **Models** | Sonnet 4.5 | claude-3-5-sonnet-20241022 | Strategic tasks |
| **Models** | Haiku 4.5 | claude-3-haiku-20240307 | Tactical tasks |
| **Vector DB** | ChromaDB | 0.5.11 | Knowledge storage |
| **Embeddings** | Sentence Transformers | latest | Text embeddings |
| **HTTP Client** | httpx | 0.27.0+ | Async HTTP |

### 3.3 Development Tools

| Tool | Purpose |
|------|---------|
| **pytest** | Unit testing |
| **pytest-asyncio** | Async test support |
| **black** | Code formatting |
| **mypy** | Type checking |
| **python-dotenv** | Environment management |

---

## 4. API Endpoints Reference

### 4.1 Health & Status Endpoints

#### GET /
**Purpose**: Root endpoint with API information

**Response**:
```json
{
  "service": "CollabFlow AI Engine",
  "version": "0.1.0",
  "status": "operational",
  "environment": "development",
  "endpoints": {
    "health": "/health",
    "docs": "/docs",
    "context_analysis": "/api/context",
    "task_management": "/api/tasks",
    "distribution": "/api/distribution",
    "orchestration": "/api/orchestration"
  }
}
```

#### GET /health
**Purpose**: Health check for monitoring

**Response**:
```json
{
  "status": "healthy",
  "environment": "development",
  "debug": false
}
```

**Status Codes**:
- 200: Service healthy
- 503: Service degraded (missing dependencies)

---

### 4.2 Context Analysis Endpoints

#### POST /api/context/analyze
**Purpose**: Analyze project context and generate insights

**Tags**: Context Analysis

**Request Body**:
```json
{
  "project_details": {
    "name": "E-commerce Platform",
    "description": "Build a full-stack e-commerce platform with React and Node.js",
    "domain": "software_development",
    "team_size": 3,
    "deadline": "2025-12-31T00:00:00Z"
  },
  "goals_context": {
    "goals": [
      "Launch MVP in 3 months",
      "Get 1000 users in first month",
      "Achieve 5% conversion rate"
    ],
    "success_metrics": "User acquisition and retention",
    "constraints": "Limited budget, small team"
  }
}
```

**Response (200 OK)**:
```json
{
  "analysis": {
    "domain": "SOFTWARE_DEVELOPMENT",
    "complexity": "HIGH",
    "estimated_task_count": 30,
    "key_objectives": [
      "Develop core e-commerce functionality",
      "Implement payment processing",
      "Build user authentication system",
      "Create admin dashboard",
      "Set up deployment pipeline"
    ],
    "challenges": [
      "Tight timeline for team size",
      "Multiple integration points required",
      "Security considerations for payments"
    ],
    "required_skills": [
      {
        "skill": "Full-stack Development",
        "importance": 0.95,
        "rationale": "Core requirement for React and Node.js implementation"
      },
      {
        "skill": "Database Design",
        "importance": 0.85,
        "rationale": "Complex product catalog and order management"
      }
    ],
    "recommendations": [
      "Break project into 4-week sprints",
      "Start with authentication and basic CRUD",
      "Use established payment provider (Stripe/PayPal)",
      "Implement CI/CD early for rapid deployment"
    ],
    "confidence_score": 0.88
  },
  "status": "success"
}
```

**Error Response (500)**:
```json
{
  "error": "Internal Server Error",
  "status_code": 500,
  "detail": "Context analysis failed: <error message>",
  "path": "/api/context/analyze"
}
```

**Processing Time**: 3-8 seconds (with Claude Sonnet)

---

### 4.3 Task Generation Endpoints

#### POST /api/tasks/generate
**Purpose**: Generate complete task breakdown (synchronous)

**Tags**: Task Management

**Request Body**:
```json
{
  "project_id": "f336d0bc-b841-465b-8045-024475c079dd",
  "user_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "context": {
    "name": "E-commerce Platform",
    "description": "Build a full-stack e-commerce platform with React and Node.js",
    "domain": "SOFTWARE_DEVELOPMENT",
    "goals": [
      "Launch MVP in 3 months",
      "Get 1000 users in first month"
    ]
  },
  "context_analysis": {
    "domain": "SOFTWARE_DEVELOPMENT",
    "complexity": "HIGH",
    "estimated_task_count": 30,
    "key_objectives": [...],
    "confidence_score": 0.88
  }
}
```

**Response (200 OK)**:
```json
{
  "tasks": [
    {
      "id": "task_001",
      "name": "Set up project infrastructure",
      "description": "Initialize React frontend, Node.js backend, configure build tools (Vite, ESLint, Prettier)",
      "estimated_hours": 16.0,
      "complexity": "MEDIUM",
      "assigned_to": "HUMAN",
      "ai_suitability_score": 0.35,
      "confidence_score": 0.82,
      "sequence": 1,
      "status": "PENDING",
      "subtasks": [],
      "validation": {
        "score": 85,
        "issues": [],
        "passed": true
      },
      "created_at": "2025-11-09T10:30:00Z"
    },
    {
      "id": "task_002",
      "name": "Design database schema",
      "description": "Create ERD for products, users, orders, payments. Define relationships, indexes, and constraints.",
      "estimated_hours": 12.0,
      "complexity": "HIGH",
      "assigned_to": "HUMAN",
      "ai_suitability_score": 0.40,
      "confidence_score": 0.85,
      "sequence": 2,
      "status": "PENDING",
      "subtasks": [],
      "validation": {
        "score": 90,
        "issues": [],
        "passed": true
      }
    },
    {
      "id": "task_003",
      "name": "Implement user authentication API",
      "description": "JWT-based authentication with email/password and social login (Google, Facebook)",
      "estimated_hours": 20.0,
      "complexity": "MEDIUM",
      "assigned_to": "AI",
      "ai_suitability_score": 0.85,
      "confidence_score": 0.88,
      "sequence": 3,
      "status": "PENDING",
      "subtasks": [
        {
          "id": "subtask_003_001",
          "name": "Set up JWT token generation and validation",
          "description": "Implement JWT signing, verification, and refresh token logic",
          "estimated_hours": 6.0,
          "sequence": 1,
          "status": "PENDING"
        },
        {
          "id": "subtask_003_002",
          "name": "Create user registration endpoint",
          "description": "API route for user signup with email validation",
          "estimated_hours": 5.0,
          "sequence": 2,
          "status": "PENDING"
        },
        {
          "id": "subtask_003_003",
          "name": "Implement social OAuth flow",
          "description": "Integrate Google and Facebook OAuth providers",
          "estimated_hours": 9.0,
          "sequence": 3,
          "status": "PENDING"
        }
      ],
      "validation": {
        "score": 92,
        "issues": [],
        "passed": true
      }
    },
    {
      "id": "task_004",
      "name": "Review and approve authentication implementation",
      "description": "Security audit of auth code, test edge cases, verify OWASP Top 10 compliance",
      "estimated_hours": 6.0,
      "complexity": "MEDIUM",
      "assigned_to": "HITL",
      "ai_suitability_score": 0.60,
      "confidence_score": 0.80,
      "sequence": 4,
      "status": "PENDING",
      "subtasks": [],
      "validation": {
        "score": 88,
        "issues": [],
        "passed": true
      }
    }
  ],
  "dependencies": [
    {
      "id": "dep_001",
      "from_task_id": "task_002",
      "to_task_id": "task_001",
      "type": "blocks"
    },
    {
      "id": "dep_002",
      "from_task_id": "task_003",
      "to_task_id": "task_002",
      "type": "blocks"
    },
    {
      "id": "dep_003",
      "from_task_id": "task_004",
      "to_task_id": "task_003",
      "type": "blocks"
    }
  ],
  "metadata": {
    "total_tasks": 30,
    "total_estimated_hours": 720.0,
    "ai_tasks": 18,
    "human_tasks": 8,
    "hitl_tasks": 4,
    "total_dependencies": 35,
    "avg_validation_score": 87.5,
    "has_parallel_branches": true
  },
  "status": "success"
}
```

**Processing Time**: 15-45 seconds (depends on task count)

---

#### POST /api/tasks/generate/stream
**Purpose**: Generate tasks with real-time progress updates (SSE)

**Tags**: Task Management

**Request**: Same as `/api/tasks/generate`

**Response**: Server-Sent Events stream

**Event Types**:

1. **retrieving_context**
```
event: retrieving_context
data: {"message": "Retrieving relevant context from knowledge base..."}
```

2. **generating**
```
event: generating
data: {"message": "Generating high-level tasks...", "progress": 20}
```

3. **decomposing**
```
event: decomposing
data: {"message": "Decomposing complex tasks...", "progress": 40}
```

4. **task_detailed**
```
event: task_detailed
data: {
  "task_id": "task_001",
  "name": "Set up project infrastructure",
  "progress": 50
}
```

5. **dependencies**
```
event: dependencies
data: {"message": "Generating task dependencies...", "progress": 80}
```

6. **complete**
```
event: complete
data: {
  "tasks": [...],
  "dependencies": [...],
  "metadata": {...}
}
```

7. **error**
```
event: error
data: {"error": "Task generation failed", "detail": "..."}
```

**Headers**:
```
Content-Type: text/event-stream
Cache-Control: no-cache
Connection: keep-alive
X-Accel-Buffering: no
```

---

#### POST /api/tasks/validate
**Purpose**: Validate a single task against SMART criteria

**Tags**: Task Management

**Request Body**:
```json
{
  "task": {
    "name": "Build login page",
    "description": "Create React component for user login",
    "estimated_hours": 8.0,
    "dependencies": []
  }
}
```

**Response (200 OK)**:
```json
{
  "validation": {
    "score": 85,
    "passed": true,
    "issues": [
      "Consider adding acceptance criteria",
      "Specify which authentication method"
    ],
    "criteria": {
      "has_clear_name": true,
      "has_description": true,
      "has_time_estimate": true,
      "starts_with_action_verb": true,
      "has_specific_scope": true,
      "avoids_vague_terms": false
    }
  },
  "status": "success"
}
```

**Processing Time**: < 100ms (rule-based)

---

#### POST /api/tasks/regenerate
**Purpose**: Regenerate specific tasks with improved quality

**Tags**: Task Management

**Request Body**:
```json
{
  "task_ids": ["task_003", "task_004"],
  "existing_tasks": [
    {
      "id": "task_003",
      "name": "Implement user authentication API",
      "description": "Basic auth implementation"
    }
  ],
  "context": {
    "name": "E-commerce Platform",
    "description": "Build a full-stack e-commerce platform"
  }
}
```

**Response (200 OK)**:
```json
{
  "regenerated_tasks": [
    {
      "id": "task_003",
      "name": "Implement comprehensive authentication system",
      "description": "JWT-based authentication with email/password, social login (Google, Facebook), 2FA support, password reset flow, and session management",
      "estimated_hours": 24.0,
      "complexity": "HIGH",
      "assigned_to": "AI",
      "ai_suitability_score": 0.87,
      "confidence_score": 0.85
    }
  ],
  "status": "success"
}
```

**Processing Time**: 2-5 seconds per task

---

### 4.4 Distribution Endpoints (STUB - Not Implemented)

#### POST /api/distribution/analyze
**Status**: ⚠️ Stub only - requires implementation

**Planned Purpose**: Analyze task distribution across AI/Human resources

#### POST /api/distribution/assign
**Status**: ⚠️ Stub only - requires implementation

**Planned Purpose**: Assign tasks to specific resources

#### POST /api/distribution/calculate-confidence
**Status**: ⚠️ Stub only - requires implementation

**Planned Purpose**: Calculate confidence scores for assignments

---

### 4.5 Orchestration Endpoints (STUB - Not Implemented)

#### POST /api/orchestration/start
**Status**: ⚠️ Stub only - requires implementation

**Planned Purpose**: Start task execution orchestration session

#### GET /api/orchestration/status/{session_id}
**Status**: ⚠️ Stub only - requires implementation

**Planned Purpose**: Get status of orchestration session

#### POST /api/orchestration/stop/{session_id}
**Status**: ⚠️ Stub only - requires implementation

**Planned Purpose**: Stop orchestration session

---

## 5. Data Models & Schemas

### 5.1 Context Models

#### ProjectContext
**File**: `app/models/context.py`

```python
class ProjectContext(BaseModel):
    """Input model for project context"""
    name: str = Field(..., min_length=3, max_length=100)
    description: str = Field(..., min_length=10, max_length=500)
    domain: Optional[DomainType] = None
    deadline: Optional[datetime] = None
    team_size: Optional[int] = Field(None, ge=1)
    goals: List[str] = Field(..., min_items=1, max_items=10)
    success_metrics: Optional[str] = None
    constraints: Optional[str] = None
    reference_documents: List[str] = Field(default_factory=list)

    @field_validator('deadline')
    @classmethod
    def validate_deadline(cls, v):
        if v and v < datetime.now():
            raise ValueError('Deadline must be in the future')
        return v
```

**Fields**:
- `name`: Project name (3-100 chars)
- `description`: Detailed description (10-500 chars)
- `domain`: Optional domain hint (enum)
- `deadline`: Future datetime
- `team_size`: Number of people (≥1)
- `goals`: 1-10 project goals
- `success_metrics`: How to measure success
- `constraints`: Budget, time, resource limitations
- `reference_documents`: URLs or paths to reference docs

---

#### ContextAnalysis
**File**: `app/models/context.py`

```python
class ContextAnalysis(BaseModel):
    """Output model for context analysis results"""
    domain: DomainType
    complexity: ComplexityLevel
    estimated_task_count: int = Field(..., ge=7, le=50)
    key_objectives: List[str]
    challenges: List[str]
    required_skills: List[Dict[str, Any]]
    recommendations: List[str]
    confidence_score: float = Field(..., ge=0.0, le=1.0)
```

**Fields**:
- `domain`: Classified domain (enum)
- `complexity`: LOW, MEDIUM, HIGH, VERY_HIGH
- `estimated_task_count`: 7-50 tasks
- `key_objectives`: Extracted objectives
- `challenges`: Identified risks/challenges
- `required_skills`: Skills with importance scores
- `recommendations`: Strategic recommendations
- `confidence_score`: 0.0-1.0 (0.7 fallback, 0.85+ Sonnet)

---

#### DomainType Enum

```python
class DomainType(str, Enum):
    SOFTWARE_DEVELOPMENT = "SOFTWARE_DEVELOPMENT"
    RESEARCH_ANALYSIS = "RESEARCH_ANALYSIS"
    MARKETING_CAMPAIGN = "MARKETING_CAMPAIGN"
    CONTENT_CREATION = "CONTENT_CREATION"
    DATA_ANALYSIS = "DATA_ANALYSIS"
    DESIGN_PROJECT = "DESIGN_PROJECT"
    EVENT_PLANNING = "EVENT_PLANNING"
    PRODUCT_LAUNCH = "PRODUCT_LAUNCH"
    INFRASTRUCTURE = "INFRASTRUCTURE"
    TRAINING_EDUCATION = "TRAINING_EDUCATION"
    SALES_OUTREACH = "SALES_OUTREACH"
    LEGAL_COMPLIANCE = "LEGAL_COMPLIANCE"
    FINANCIAL_PLANNING = "FINANCIAL_PLANNING"
    CUSTOM = "CUSTOM"
```

---

#### ComplexityLevel Enum

```python
class ComplexityLevel(str, Enum):
    LOW = "LOW"           # Score: ≤4 points
    MEDIUM = "MEDIUM"     # Score: 5-8 points
    HIGH = "HIGH"         # Score: 9-12 points
    VERY_HIGH = "VERY_HIGH"  # Score: 13+ points
```

**Complexity Scoring Factors**:
1. Number of goals (1-3 points)
2. Description detail (1-3 points)
3. Timeline pressure (1-3 points)
4. Team size efficiency (1-2 points)
5. Technical indicators (1-2 points)

---

### 5.2 Task Models

#### Task
**File**: `app/models/task.py`

```python
class Task(BaseModel):
    """Main task entity"""
    id: str = Field(default_factory=lambda: f"task_{str(uuid.uuid4())[:8]}")
    name: str = Field(..., min_length=3, max_length=100)
    description: str = Field(..., min_length=10, max_length=1000)
    estimated_hours: float = Field(..., gt=0)
    complexity: ComplexityLevel
    assigned_to: AssignmentType = AssignmentType.UNASSIGNED
    ai_suitability_score: Optional[float] = Field(None, ge=0.0, le=1.0)
    confidence_score: Optional[float] = Field(None, ge=0.0, le=1.0)
    sequence: int = Field(default=0, ge=0)
    status: TaskStatus = TaskStatus.PENDING
    subtasks: List[Subtask] = Field(default_factory=list)
    validation: Optional[ValidationResult] = None
    position: Optional[Dict[str, float]] = None  # {x, y} for flowchart
    created_at: Optional[str] = None
    updated_at: Optional[str] = None
```

**Key Fields**:
- `id`: Auto-generated UUID format (task_abcd1234)
- `assigned_to`: AI, HUMAN, HITL, or UNASSIGNED
- `ai_suitability_score`: 0-1 (how suitable for AI)
- `subtasks`: Decomposed sub-tasks for complex tasks
- `position`: Flowchart coordinates

---

#### AssignmentType Enum

```python
class AssignmentType(str, Enum):
    AI = "AI"         # Suitable for AI tools (GitHub Copilot, ChatGPT)
    HUMAN = "HUMAN"   # Requires human expertise
    HITL = "HITL"     # Human-In-The-Loop (AI + Human review)
    UNASSIGNED = "UNASSIGNED"
```

**CRITICAL**: Laravel uses lowercase: `ai`, `human`, `hitl`

---

#### Subtask
**File**: `app/models/task.py`

```python
class Subtask(BaseModel):
    """Subtask decomposed from complex tasks"""
    id: str = Field(default_factory=lambda: f"subtask_{str(uuid.uuid4())[:8]}")
    name: str = Field(..., min_length=3, max_length=100)
    description: Optional[str] = None
    estimated_hours: float = Field(..., gt=0)
    sequence: int = Field(..., ge=1)
    status: TaskStatus = TaskStatus.PENDING
```

**Generation Rules**:
- Only for tasks with HIGH complexity OR >8 estimated hours
- Generate 3-7 subtasks per task
- Subtasks sum to parent estimated_hours

---

#### Dependency
**File**: `app/models/task.py`

```python
class Dependency(BaseModel):
    """Task dependency relationship"""
    id: str = Field(default_factory=lambda: f"dep_{str(uuid.uuid4())[:8]}")
    from_task_id: str
    to_task_id: str
    type: str = "blocks"  # blocks, enables, relates_to
```

**Dependency Types**:
- `blocks`: from_task must complete before to_task can start
- `enables`: from_task enables to_task (soft dependency)
- `relates_to`: informational relationship

**Cycle Detection**: DFS traversal prevents circular dependencies

---

#### ValidationResult
**File**: `app/models/task.py`

```python
class ValidationResult(BaseModel):
    """SMART validation result"""
    score: int = Field(..., ge=0, le=100)
    issues: List[str] = Field(default_factory=list)
    passed: bool
```

**Passing Threshold**: score ≥ 70

---

#### WorkflowMetadata
**File**: `app/models/task.py`

```python
class WorkflowMetadata(BaseModel):
    """Summary metadata for generated workflow"""
    total_tasks: int
    total_estimated_hours: float
    ai_tasks: int
    human_tasks: int
    hitl_tasks: int
    total_dependencies: int
    avg_validation_score: float
    has_parallel_branches: bool
```

---

### 5.3 API Request/Response Models

#### ContextAnalysisRequest
**File**: `app/models/api.py`

```python
class ContextAnalysisRequest(BaseModel):
    project_details: Dict[str, Any]
    goals_context: Dict[str, Any]
```

---

#### ContextAnalysisResponse
**File**: `app/models/api.py`

```python
class ContextAnalysisResponse(BaseModel):
    analysis: ContextAnalysis
    status: str = "success"
```

---

#### TaskGenerationRequest
**File**: `app/models/api.py`

```python
class TaskGenerationRequest(BaseModel):
    project_id: str
    user_id: str
    context: Dict[str, Any]
    context_analysis: Dict[str, Any]
```

---

#### TaskGenerationResponse
**File**: `app/models/api.py`

```python
class TaskGenerationResponse(BaseModel):
    tasks: List[Task]
    dependencies: List[Dependency]
    metadata: WorkflowMetadata
    status: str = "success"
```

---

#### ErrorResponse
**File**: `app/models/api.py`

```python
class ErrorResponse(BaseModel):
    error: str
    status_code: int
    detail: str
    path: str
    type: Optional[str] = None  # Exception type (debug mode only)
```

---

## 6. Service Layer Architecture

### 6.1 ContextAnalyzerService

**File**: `app/services/context_analyzer.py`

**Purpose**: Analyzes project context to extract domain, complexity, objectives, and recommendations

#### Main Method

```python
async def analyze(self, context: ProjectContext) -> ContextAnalysis:
    """
    Main analysis pipeline

    Steps:
    1. Classify domain (rule-based)
    2. Analyze complexity (multi-factor scoring)
    3. Deep analysis (Claude Sonnet or fallback)
    4. Identify required expertise
    5. Estimate task count

    Returns:
        ContextAnalysis with all fields populated
    """
```

**Processing Flow**:
```
ProjectContext
    │
    ├─→ _classify_domain()        # Rule-based keyword matching
    │   └─→ DomainType
    │
    ├─→ _analyze_complexity()     # Multi-factor scoring (1-15 points)
    │   └─→ ComplexityLevel
    │
    ├─→ _deep_analysis()          # Claude Sonnet (or fallback)
    │   ├─→ LLMOrchestrator.call_sonnet()
    │   └─→ {objectives, challenges, recommendations, confidence}
    │
    ├─→ _identify_expertise()     # Domain-specific skill mapping
    │   └─→ List[{skill, importance, rationale}]
    │
    └─→ _estimate_task_count()    # Base count × complexity multiplier
        └─→ int (7-50)
```

---

#### Method: _classify_domain()

```python
def _classify_domain(self, context: ProjectContext) -> DomainType:
    """
    Classify project domain using keyword matching

    Strategy:
    - Match description against domain-specific keywords
    - Score each domain (1 point per keyword match)
    - Select domain with highest score (≥2 threshold)
    - Respect user-provided domain override
    - Default to CUSTOM if no strong match

    Returns:
        DomainType enum value
    """
```

**Domain Keywords**:
```python
DOMAIN_KEYWORDS = {
    "SOFTWARE_DEVELOPMENT": ["code", "software", "api", "database", "app", ...],
    "RESEARCH_ANALYSIS": ["research", "study", "analysis", "data", "survey", ...],
    "MARKETING_CAMPAIGN": ["marketing", "campaign", "advertising", "brand", ...],
    # ... 13 domains total
}
```

**Threshold**: 2+ keyword matches required

---

#### Method: _analyze_complexity()

```python
def _analyze_complexity(self, context: ProjectContext) -> ComplexityLevel:
    """
    Analyze project complexity using multi-factor scoring

    Scoring Factors:
    1. Goals count (1-3 points)
       - 1-2 goals: 1 point
       - 3-5 goals: 2 points
       - 6+ goals: 3 points

    2. Description detail (1-3 points)
       - <50 words: 1 point
       - 50-150 words: 2 points
       - >150 words: 3 points

    3. Timeline pressure (1-3 points)
       - >6 months: 1 point
       - 3-6 months: 2 points
       - <3 months: 3 points

    4. Team size efficiency (1-2 points)
       - Large team (5+): 1 point
       - Small team (1-2): 2 points

    5. Technical indicators (1-2 points)
       - Keywords: integration, api, system, architecture, scalability

    Mapping:
    - ≤4 points: LOW
    - 5-8 points: MEDIUM
    - 9-12 points: HIGH
    - 13+ points: VERY_HIGH
    """
```

---

#### Method: _deep_analysis()

```python
async def _deep_analysis(self, context: ProjectContext) -> Dict:
    """
    Perform AI-powered deep analysis using Claude Sonnet

    Fallback Strategy:
    - If Anthropic API available: Use Claude Sonnet (confidence 0.85)
    - If unavailable: Use rule-based analysis (confidence 0.70)

    Sonnet Prompt:
    - Extract true objectives beyond stated goals
    - Identify potential challenges/risks
    - Provide strategic recommendations
    - Return JSON with objectives, challenges, recommendations, confidence

    Fallback Analysis:
    - Objectives = first 3 goals
    - Challenges = domain-specific templates
    - Recommendations = generic best practices

    Returns:
        {
            "objectives": List[str],
            "challenges": List[str],
            "recommendations": List[str],
            "confidence": float
        }
    """
```

**Sonnet Prompt Template**: See `app/utils/prompts.py::ContextAnalysisPrompts.deep_analysis()`

---

#### Method: _identify_expertise()

```python
def _identify_expertise(
    self,
    context: ProjectContext,
    domain: DomainType
) -> List[Dict]:
    """
    Identify required skills based on domain

    Domain Templates:
    - SOFTWARE_DEVELOPMENT: Full-stack Dev, DevOps, Testing, Security
    - RESEARCH_ANALYSIS: Data Analysis, Statistics, Research Design
    - MARKETING_CAMPAIGN: Digital Marketing, SEO/SEM, Content Strategy

    Returns:
        [
            {
                "skill": "Full-stack Development",
                "importance": 0.95,
                "rationale": "Core requirement..."
            },
            ...
        ]
    """
```

---

#### Method: _estimate_task_count()

```python
def _estimate_task_count(
    self,
    context: ProjectContext,
    complexity: ComplexityLevel
) -> int:
    """
    Estimate number of tasks to generate

    Formula:
    - Base count = min(goals × 2, 20)
    - Complexity multiplier:
        * LOW: 0.7x
        * MEDIUM: 1.0x
        * HIGH: 1.3x
        * VERY_HIGH: 1.5x
    - Final count clamped to [7, 50]

    Returns:
        int (7-50)
    """
```

---

### 6.2 TaskGeneratorService

**File**: `app/services/task_generator.py`

**Purpose**: Multi-tier LLM-based task generation with decomposition, classification, and validation

#### Main Method

```python
async def generate_tasks(
    self,
    project_id: str,
    user_id: str,
    context: ProjectContext,
    context_analysis: ContextAnalysis
) -> Tuple[List[Task], List[Dependency], WorkflowMetadata]:
    """
    Generate complete task workflow

    Pipeline:
    1. Retrieve relevant context from knowledge base
    2. Load domain template
    3. Generate high-level tasks (Sonnet)
    4. Decompose complex tasks (Haiku)
    5. Classify each task (Haiku)
    6. Validate all tasks
    7. Generate dependencies (Haiku)
    8. Calculate metadata

    Returns:
        (tasks, dependencies, metadata)
    """
```

**Processing Flow**:
```
ProjectContext + ContextAnalysis
    │
    ├─→ KnowledgeManager.query_relevant_context()
    │   └─→ relevant_docs (ChromaDB semantic search)
    │
    ├─→ get_domain_template()
    │   └─→ {best_practices, typical_tasks}
    │
    ├─→ _generate_high_level_tasks() [TIER 1: Sonnet]
    │   ├─→ Prompt with context + template + relevant docs
    │   └─→ 7-20 Task objects
    │
    ├─→ _decompose_tasks() [TIER 2: Haiku]
    │   ├─→ For each task: HIGH complexity OR >8 hours
    │   └─→ Add 3-7 Subtasks per complex task
    │
    ├─→ _classify_tasks() [TIER 3: Haiku]
    │   ├─→ For each task: analyze AI suitability
    │   └─→ Set assigned_to, ai_suitability_score, confidence
    │
    ├─→ TaskValidator.validate_task()
    │   └─→ Add ValidationResult to each task
    │
    ├─→ _generate_dependencies() [TIER 3: Haiku]
    │   ├─→ Analyze task relationships
    │   ├─→ Cycle detection (DFS)
    │   └─→ Fallback to linear if cycles detected
    │
    └─→ _calculate_metadata()
        └─→ WorkflowMetadata
```

---

#### Method: _generate_high_level_tasks() [Tier 1]

```python
async def _generate_high_level_tasks(
    self,
    context: ProjectContext,
    analysis: ContextAnalysis,
    relevant_context: List[str],
    domain_template: Dict
) -> List[Task]:
    """
    Generate strategic high-level tasks using Claude Sonnet

    Prompt Includes:
    - Project name, description, domain
    - Goals and key objectives
    - Domain best practices
    - Relevant context from knowledge base
    - Complexity level

    Requirements:
    - Generate analysis.estimated_task_count tasks
    - Each task starts with action verb
    - Specific and actionable
    - Logical sequence

    Returns:
        List of Task objects (7-20 tasks)

    Model: Claude Sonnet (strategic reasoning)
    Temperature: 0.7
    Max Tokens: 4000
    """
```

**Prompt Template**: `TaskGenerationPrompts.high_level_tasks()`

**Action Verbs Required**: create, design, implement, build, test, deploy, analyze, write, develop, configure, optimize, refactor, document, review, validate

---

#### Method: _decompose_tasks() [Tier 2]

```python
async def _decompose_tasks(
    self,
    tasks: List[Task],
    context: ProjectContext
) -> List[Task]:
    """
    Decompose complex tasks into subtasks using Claude Haiku

    Decomposition Criteria:
    - Task has HIGH complexity, OR
    - Task has >8 estimated hours

    For each qualifying task:
    - Generate 3-7 subtasks
    - Subtasks sum to parent estimated_hours
    - Each subtask independently executable

    Returns:
        Updated tasks with subtasks populated

    Model: Claude Haiku (fast tactical decomposition)
    Temperature: 0.7
    """
```

**Prompt Template**: `TaskGenerationPrompts.task_decomposition()`

---

#### Method: _classify_tasks() [Tier 3]

```python
async def _classify_tasks(self, tasks: List[Task]) -> List[Task]:
    """
    Classify each task as AI/Human/HITL using Claude Haiku

    Classification Factors:
    - AI suitable: code generation, data processing, testing, drafting
    - Human required: strategy, creativity, stakeholder communication, judgment
    - HITL: AI can assist but human review needed (security, compliance)

    For each task, sets:
    - assigned_to: AI, HUMAN, or HITL
    - ai_suitability_score: 0.0-1.0
    - confidence_score: 0.0-1.0

    Returns:
        Updated tasks with classification

    Model: Claude Haiku (fast classification)
    Temperature: 0.5 (more deterministic)
    """
```

**Prompt Template**: `TaskGenerationPrompts.task_classification()`

---

#### Method: _generate_dependencies()

```python
async def _generate_dependencies(self, tasks: List[Task]) -> List[Dependency]:
    """
    Generate task dependencies using Claude Haiku

    Process:
    1. Haiku analyzes task relationships
    2. Generates dependency list
    3. Cycle detection via DFS traversal
    4. If cycles found: fallback to linear sequence

    Dependency Patterns:
    - Linear: Task1 → Task2 → Task3
    - Parallel: Task1 → [Task2, Task3] → Task4
    - Complex DAG: Multiple branches merging

    Returns:
        List[Dependency] with no cycles

    Model: Claude Haiku
    """
```

**Cycle Detection Algorithm**:
```python
def _has_cycle(dependencies: List[Dependency]) -> bool:
    """DFS-based cycle detection in directed graph"""
    # Build adjacency list
    # DFS with visited/recursion stack
    # Return True if back edge found
```

**Fallback Strategy**: Create linear dependencies (task_i → task_i+1)

---

#### Method: regenerate_tasks()

```python
async def regenerate_tasks(
    self,
    task_ids: List[str],
    existing_tasks: List[Task],
    context: ProjectContext
) -> List[Task]:
    """
    Regenerate specific tasks with improved detail

    Strategy:
    - Use Claude Sonnet for higher quality
    - Higher temperature (0.8) for more variety
    - Keep original task IDs and sequence
    - Preserve dependencies

    Returns:
        List of regenerated Task objects

    Model: Claude Sonnet
    Temperature: 0.8
    """
```

---

### 6.3 TaskValidator

**File**: `app/services/validator.py`

**Purpose**: Validate tasks against SMART criteria using rule-based checks

#### Main Method

```python
def validate_task(self, task: Task) -> ValidationResult:
    """
    Validate task against 7 criteria

    Criteria:
    1. Specificity (S) - min 10 chars, no generic terms
    2. Description completeness - min 10 chars
    3. Time estimate (T) - must be present and >0
    4. Action verb - must start with action verb
    5. Vague term avoidance - avoid "setup", "handle", "manage"
    6. Scope management - warn if >40 hours
    7. Name length - max 100 chars

    Scoring:
    - Start at 100 points
    - Deduct for each violation:
        * No specificity: -20
        * No description: -25
        * No time estimate: -15
        * No action verb: -10
        * Vague terms: -15
        * Large scope: -10
        * Long name: -5

    Passing: score ≥ 70

    Returns:
        ValidationResult(score, issues, passed)
    """
```

**Validation Rules**:

1. **Specificity Check**:
```python
# Reject generic terms
GENERIC_TERMS = ['something', 'anything', 'everything', 'stuff', 'things']
if any(term in task.name.lower() for term in GENERIC_TERMS):
    score -= 20
```

2. **Action Verb Check**:
```python
ACTION_VERBS = [
    'create', 'design', 'implement', 'build', 'test', 'deploy',
    'analyze', 'write', 'develop', 'configure', 'optimize',
    'refactor', 'document', 'review', 'validate'
]
if not any(task.name.lower().startswith(verb) for verb in ACTION_VERBS):
    score -= 10
```

3. **Vague Term Check**:
```python
VAGUE_TERMS = [
    'setup', 'handle', 'manage', 'deal with', 'work on',
    'sort out', 'take care of', 'look into'
]
if any(term in task.name.lower() for term in VAGUE_TERMS):
    score -= 15
```

---

### 6.4 KnowledgeManager

**File**: `app/services/knowledge_manager.py`

**Purpose**: RAG (Retrieval-Augmented Generation) using ChromaDB vector database

#### Architecture

```
KnowledgeManager
    │
    ├─→ ChromaDB HTTP Client
    │   └─→ http://{CHROMA_HOST}:{CHROMA_PORT}
    │
    ├─→ SentenceTransformer (Embeddings)
    │   └─→ Model: "all-MiniLM-L6-v2"
    │
    └─→ Collections
        ├─→ project_{project_id}  # Per-project documents
        └─→ domain_{domain_name}  # Domain templates
```

---

#### Method: ingest_project_documents()

```python
async def ingest_project_documents(
    self,
    project_id: str,
    documents: List[Dict[str, str]]
) -> bool:
    """
    Ingest project documents into ChromaDB

    Process:
    1. Create/get collection for project
    2. Chunk each document (smart chunking)
    3. Generate embeddings for each chunk
    4. Store with metadata

    Document Format:
    {
        "id": "doc_001",
        "content": "...",
        "type": "requirement|reference|template",
        "source": "filename or URL"
    }

    Chunking Strategy:
    - Use semantic chunking (paragraph boundaries)
    - Fallback to fixed-size if paragraphs too large
    - Chunk size: 512 words
    - Overlap: 50 words

    Returns:
        True if successful, False otherwise
    """
```

---

#### Method: query_relevant_context()

```python
async def query_relevant_context(
    self,
    project_id: str,
    query: str,
    top_k: int = 5
) -> List[str]:
    """
    Semantic search for relevant context

    Process:
    1. Encode query with SentenceTransformer
    2. Query ChromaDB collection
    3. Return top-k most similar chunks

    Parameters:
        project_id: Project identifier
        query: Natural language query
        top_k: Number of results (default: 5)

    Returns:
        List of relevant document chunks
        Empty list if no collection or no results
    """
```

**Embedding Model**: `all-MiniLM-L6-v2` (384 dimensions, open-source)

---

#### Method: smart_chunk()

```python
def smart_chunk(
    self,
    text: str,
    method: str = "semantic",
    chunk_size: int = 512,
    chunk_overlap: int = 50
) -> List[str]:
    """
    Smart document chunking with multiple strategies

    Methods:
    1. semantic: Split by paragraph boundaries (\\n\\n)
       - Merge short paragraphs (<50 words)
       - Split long paragraphs (>chunk_size)

    2. fixed: Fixed-size chunks with overlap
       - Split by words
       - Overlap between chunks

    3. hybrid: Semantic first, then fixed for oversized chunks

    Parameters:
        text: Document content
        method: "semantic"|"fixed"|"hybrid"
        chunk_size: Words per chunk (default: 512)
        chunk_overlap: Overlap words (default: 50)

    Returns:
        List of text chunks
    """
```

---

#### Method: ingest_domain_template()

```python
async def ingest_domain_template(
    self,
    domain: str,
    template: Dict[str, Any]
) -> bool:
    """
    Seed domain-specific knowledge

    Template Format:
    {
        "name": "software_development",
        "description": "...",
        "typical_tasks": ["Task 1", "Task 2", ...],
        "best_practices": ["BP 1", "BP 2", ...],
        "common_challenges": ["Challenge 1", ...]
    }

    Process:
    1. Convert template to documents
    2. Chunk and embed
    3. Store in domain_{domain} collection

    Returns:
        True if successful
    """
```

---

### 6.5 LLMOrchestrator

**File**: `app/utils/llm_orchestrator.py`

**Purpose**: Manage multi-model LLM clients for Anthropic Claude

#### Architecture

```python
class LLMOrchestrator:
    """
    Manages Claude Sonnet and Haiku clients

    Clients:
    - claude_sonnet: AsyncAnthropic(default_model=claude-3-5-sonnet-20241022)
    - claude_haiku: AsyncAnthropic(default_model=claude-3-haiku-20240307)

    HTTP Configuration:
    - Connection pooling: 5-10 max connections
    - Timeout: 60s request, 10s connection
    - Async httpx client
    """
```

---

#### Method: call_sonnet()

```python
async def call_sonnet(
    self,
    prompt: str,
    max_tokens: Optional[int] = None,
    temperature: Optional[float] = None
) -> str:
    """
    Call Claude Sonnet for strategic tasks

    Default Parameters:
    - max_tokens: 4000
    - temperature: 0.7

    Use Cases:
    - Context deep analysis
    - High-level task generation
    - Task regeneration
    - Complex reasoning

    Returns:
        Model response text

    Raises:
        Exception if API call fails
    """
```

---

#### Method: call_haiku()

```python
async def call_haiku(
    self,
    prompt: str,
    max_tokens: Optional[int] = None,
    temperature: Optional[float] = None
) -> str:
    """
    Call Claude Haiku for tactical tasks

    Default Parameters:
    - max_tokens: 2000
    - temperature: 0.7

    Use Cases:
    - Task decomposition
    - Task classification
    - Dependency generation
    - Fast categorization

    Returns:
        Model response text

    Raises:
        Exception if API call fails
    """
```

**Cost Optimization**: Use Haiku for high-volume, simple tasks

---

## 7. Routing Layer

### 7.1 Context Router

**File**: `app/routers/context.py`

```python
router = APIRouter()

@router.post("/analyze", response_model=ContextAnalysisResponse)
async def analyze_context(request: ContextAnalysisRequest):
    """
    Analyze project context

    Process:
    1. Parse project_details and goals_context
    2. Create ProjectContext model
    3. Call ContextAnalyzerService.analyze()
    4. Return ContextAnalysisResponse

    Error Handling:
    - 422: Validation error (Pydantic)
    - 500: Internal server error
    """
```

---

### 7.2 Tasks Router

**File**: `app/routers/tasks.py`

```python
router = APIRouter()

@router.post("/generate", response_model=TaskGenerationResponse)
async def generate_tasks(request: TaskGenerationRequest):
    """Synchronous task generation"""

@router.post("/generate/stream")
async def generate_tasks_stream(request: TaskGenerationRequest):
    """
    Streaming task generation with SSE

    Event Types:
    - retrieving_context
    - generating
    - decomposing
    - task_detailed
    - dependencies
    - complete
    - error

    Headers:
    - Content-Type: text/event-stream
    - Cache-Control: no-cache
    - X-Accel-Buffering: no
    """

@router.post("/validate", response_model=TaskValidationResponse)
async def validate_task(request: TaskValidationRequest):
    """Validate single task"""

@router.post("/regenerate", response_model=TaskRegenerationResponse)
async def regenerate_tasks(request: TaskRegenerationRequest):
    """Regenerate specific tasks"""
```

---

### 7.3 Distribution Router (STUB)

**File**: `app/routers/distribution.py`

**Status**: ⚠️ Not implemented - placeholders only

---

### 7.4 Orchestration Router (STUB)

**File**: `app/routers/orchestration.py`

**Status**: ⚠️ Not implemented - placeholders only

---

## 8. Middleware & Error Handling

### 8.1 CORS Middleware

**File**: `app/middleware/cors.py`

```python
def setup_cors(app: FastAPI):
    """
    Configure CORS middleware

    Configuration:
    - allow_origins: From settings.allowed_origins_list
    - allow_credentials: True
    - allow_methods: ["*"]
    - allow_headers: ["*"]
    - expose_headers: ["*"]

    Default Origins:
    - http://laravel-app.test
    - http://localhost
    - http://localhost:8001
    """
```

---

### 8.2 Error Handler Middleware

**File**: `app/middleware/error_handler.py`

**Handlers**:

1. **HTTP Exceptions** (StarletteHTTPException)
```python
@app.exception_handler(StarletteHTTPException)
async def http_exception_handler(request, exc):
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "error": "HTTP Error",
            "status_code": exc.status_code,
            "detail": exc.detail,
            "path": str(request.url.path)
        }
    )
```

2. **Request Validation Errors** (RequestValidationError)
```python
@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request, exc):
    return JSONResponse(
        status_code=422,
        content={
            "error": "Validation Error",
            "status_code": 422,
            "detail": str(exc),
            "path": str(request.url.path)
        }
    )
```

3. **Pydantic Validation Errors**
4. **ValueError Exceptions**
5. **Global Exception Handler**

---

## 9. Configuration Management

### 9.1 Settings Class

**File**: `app/config.py`

```python
class Settings(BaseSettings):
    """Pydantic BaseSettings for environment configuration"""

    # Service
    service_name: str = "collabflow-python"
    debug: bool = False
    environment: str = "development"
    log_level: str = "INFO"

    # API Keys
    anthropic_api_key: Optional[str] = None
    openai_api_key: Optional[str] = None

    # Chroma
    chroma_host: str = "localhost"
    chroma_port: int = 8000
    chroma_enabled: bool = True

    # CORS
    allowed_origins: str = '[...]'  # JSON string

    # LLM
    default_model: str = "claude-3-5-sonnet-20241022"
    haiku_model: str = "claude-3-haiku-20240307"
    max_tokens: int = 4000
    temperature: float = 0.7

    # Task Generation
    min_tasks: int = 7
    max_tasks: int = 50

    # Confidence
    min_confidence_threshold: float = 0.6
    high_confidence_threshold: float = 0.8

    class Config:
        env_file = ".env"
        case_sensitive = False
        extra = "allow"
```

---

### 9.2 Environment Variables

**Required**:
```bash
ANTHROPIC_API_KEY=sk-ant-your-key-here
```

**Optional**:
```bash
# Service
SERVICE_NAME=collabflow-python
DEBUG=false
ENVIRONMENT=development
LOG_LEVEL=INFO

# Chroma
CHROMA_HOST=localhost
CHROMA_PORT=8000
CHROMA_ENABLED=true

# CORS
ALLOWED_ORIGINS=["http://laravel-app.test","http://localhost:8001"]

# LLM
DEFAULT_MODEL=claude-3-5-sonnet-20241022
HAIKU_MODEL=claude-3-haiku-20240307
MAX_TOKENS=4000
TEMPERATURE=0.7

# Task Generation
MIN_TASKS=7
MAX_TASKS=50
```

---

## 10. External Dependencies

### 10.1 Anthropic Claude API

**Models Used**:
- **Sonnet 4.5**: `claude-3-5-sonnet-20241022`
  - Context: 200K tokens
  - Output: Up to 8K tokens
  - Use: Strategic planning, analysis

- **Haiku 4.5**: `claude-3-haiku-20240307`
  - Context: 200K tokens
  - Output: Up to 4K tokens
  - Use: Fast classification, decomposition

**Authentication**: API key via `ANTHROPIC_API_KEY`

**Rate Limits**: Handled by client retry logic

---

### 10.2 ChromaDB

**Version**: 0.5.11+

**API Version**: v2

**Endpoints Used**:
- `GET /api/v2/heartbeat` - Health check
- `POST /api/v2/collections` - Create collection
- `POST /api/v2/collections/{name}/query` - Semantic search
- `POST /api/v2/collections/{name}/add` - Add documents

**Embedding Model**: `all-MiniLM-L6-v2` (SentenceTransformers)

**Collection Naming**:
- Project documents: `project_{project_id}`
- Domain templates: `domain_{domain_name}`

---

### 10.3 Sentence Transformers

**Model**: `all-MiniLM-L6-v2`

**Dimensions**: 384

**Purpose**: Generate embeddings for document chunks

**Advantages**:
- Open-source
- No API key required
- Fast inference
- Good quality for general-purpose search

---

## 11. Testing Strategy

### 11.1 Unit Tests

**File**: `tests/test_context_analyzer.py`

**Coverage**: 85% (40 tests)

**Test Categories**:
1. Domain classification (5 tests)
2. Complexity analysis (5 tests)
3. Expertise identification (3 tests)
4. Task count estimation (2 tests)
5. Integration tests (2 tests)

**Mocking Strategy**:
```python
@pytest.fixture
def mock_llm_orchestrator():
    with patch('app.services.context_analyzer.LLMOrchestrator') as mock:
        mock.return_value.call_sonnet.return_value = json.dumps({
            "objectives": [...],
            "challenges": [...],
            "recommendations": [...]
        })
        yield mock
```

---

### 11.2 Integration Tests

**File**: `tests/test_task_generator.py`

**Coverage**: 20% (partial)

**Mocking**:
- ChromaDB client
- SentenceTransformer model
- Anthropic API calls

---

### 11.3 Test Execution

```bash
# Run all tests
pytest tests/ -v

# Run with coverage
pytest tests/ --cov=app --cov-report=html

# Run specific test file
pytest tests/test_context_analyzer.py -v

# Run async tests
pytest tests/ -v --asyncio-mode=auto
```

---

## 12. Deployment & Operations

### 12.1 Running the Service

**Development Mode**:
```bash
python dev.py
```

**Production Mode**:
```bash
uvicorn app.main:app --host 0.0.0.0 --port 8001 --workers 4
```

---

### 12.2 Docker Deployment

**Dockerfile**: (needs to be created)

```dockerfile
FROM python:3.11-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app/ ./app/
COPY .env .

EXPOSE 8001

CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]
```

---

### 12.3 Health Monitoring

**Endpoints**:
- `GET /health` - Basic health check
- `GET /` - Service info

**Monitoring Recommendations**:
1. **Uptime**: Ping `/health` every 30s
2. **Response Times**: Monitor API endpoint latencies
3. **Error Rates**: Track 5xx responses
4. **LLM Costs**: Log Anthropic API usage
5. **ChromaDB**: Monitor connection status

---

### 12.4 Logging

**Configuration**:
```python
logging.basicConfig(
    level=settings.log_level,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
```

**Log Levels**:
- `INFO`: Startup, shutdown, API calls
- `WARNING`: Degraded performance, fallbacks
- `ERROR`: API failures, exceptions
- `DEBUG`: Detailed execution flow

---

## 13. Implementation Status

### 13.1 Completed Features

| Feature | Status | Quality |
|---------|--------|---------|
| Context Analysis | ✅ Complete | Production-ready |
| Task Generation (Tier 1-3) | ✅ Complete | Production-ready |
| Task Validation | ✅ Complete | Production-ready |
| Knowledge Management | ✅ Complete | Production-ready |
| API Endpoints (Context/Tasks) | ✅ Complete | Production-ready |
| Error Handling | ✅ Complete | Production-ready |
| Configuration | ✅ Complete | Production-ready |
| LLM Orchestration | ✅ Complete | Production-ready |
| CORS Middleware | ✅ Complete | Production-ready |

---

### 13.2 Incomplete Features

| Feature | Status | Priority |
|---------|--------|----------|
| Distribution API | ⚠️ Stub | Medium |
| Orchestration API | ⚠️ Stub | Low |
| Comprehensive Tests | 🔄 50% | High |
| Data Persistence | ❌ Missing | High |
| Monitoring/Metrics | ❌ Missing | Medium |
| API Documentation | 🔄 Partial | Medium |

---

### 13.3 Missing Components

1. **Data Persistence Layer**
   - No database for task storage
   - No project history
   - No audit logs

2. **Task Execution Engine**
   - No orchestration implementation
   - No session management
   - No progress tracking

3. **Advanced Features**
   - No critical path analysis
   - No resource optimization
   - No schedule generation

---

## 14. Appendices

### 14.1 Domain Keywords Reference

```python
DOMAIN_KEYWORDS = {
    "SOFTWARE_DEVELOPMENT": [
        "code", "software", "api", "database", "app", "web",
        "frontend", "backend", "mobile", "development", "programming"
    ],
    "RESEARCH_ANALYSIS": [
        "research", "study", "analysis", "data", "survey",
        "investigation", "findings", "hypothesis", "methodology"
    ],
    "MARKETING_CAMPAIGN": [
        "marketing", "campaign", "advertising", "brand", "promotion",
        "social media", "content", "engagement", "conversion"
    ],
    # ... 10 more domains
}
```

---

### 14.2 Complexity Scoring Matrix

| Factor | Range | Points |
|--------|-------|--------|
| Goals count | 1-2 | 1 |
| Goals count | 3-5 | 2 |
| Goals count | 6+ | 3 |
| Description | <50 words | 1 |
| Description | 50-150 words | 2 |
| Description | >150 words | 3 |
| Timeline | >6 months | 1 |
| Timeline | 3-6 months | 2 |
| Timeline | <3 months | 3 |
| Team size | 5+ people | 1 |
| Team size | 1-2 people | 2 |
| Technical keywords | Present | 1-2 |

**Total Complexity Mapping**:
- 0-4 points: LOW
- 5-8 points: MEDIUM
- 9-12 points: HIGH
- 13+ points: VERY_HIGH

---

### 14.3 Validation Scoring Rules

| Criterion | Penalty | Threshold |
|-----------|---------|-----------|
| No specificity | -20 | Generic terms present |
| No description | -25 | <10 characters |
| No time estimate | -15 | Missing or ≤0 |
| No action verb | -10 | Doesn't start with verb |
| Vague terms | -15 | Contains vague keywords |
| Large scope | -10 | >40 estimated hours |
| Long name | -5 | >100 characters |

**Passing Score**: ≥70 points

---

### 14.4 API Response Time Expectations

| Endpoint | Typical Time | Max Time |
|----------|-------------|----------|
| GET /health | <50ms | 100ms |
| POST /api/context/analyze | 3-8s | 15s |
| POST /api/tasks/generate | 15-45s | 120s |
| POST /api/tasks/validate | <100ms | 500ms |
| POST /api/tasks/regenerate | 2-5s/task | 30s |

---

### 14.5 Error Response Formats

**Validation Error (422)**:
```json
{
  "error": "Validation Error",
  "status_code": 422,
  "detail": "Field 'name' is required",
  "path": "/api/context/analyze"
}
```

**Server Error (500)**:
```json
{
  "error": "Internal Server Error",
  "status_code": 500,
  "detail": "Context analysis failed: API timeout",
  "path": "/api/context/analyze"
}
```

---

## Conclusion

This document provides comprehensive technical specifications for the CollabFlow Python Service. The service is **70% complete** with strong foundations in context analysis, task generation, validation, and knowledge management.

**Key Integration Points for Laravel**:
1. Task type enum: Use `ai`, `human`, `hitl` (lowercase)
2. All API responses include `status` field
3. Graceful fallbacks when service unavailable
4. JSON response format compatible with Laravel's expectations

**Next Steps**:
1. Implement data persistence layer
2. Complete test coverage
3. Implement distribution and orchestration routers
4. Add monitoring and metrics
5. Production deployment configuration

---

**Document Version**: 1.0
**Last Updated**: November 9, 2025
**Maintained By**: CollabFlow Development Team
