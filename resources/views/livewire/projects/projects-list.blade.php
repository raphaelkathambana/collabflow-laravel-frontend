<div>
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-text-900);">Projects</h1>
        <p style="color: var(--color-text-600);">Manage your projects and track progress</p>
    </div>

    <!-- Filters and Search Bar -->
    <div class="mb-6 space-y-4">
        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap gap-2 pb-2" style="border-bottom: 1px solid var(--color-background-300);">
            <button
                wire:click="$set('statusFilter', 'all')"
                class="px-4 py-2 rounded-t-lg transition-all"
                style="{{ $statusFilter === 'all' ? 'background-color: var(--color-glaucous); color: white;' : 'color: var(--color-text-600); background-color: transparent;' }}"
                onmouseover="if(!this.classList.contains('active-filter')) this.style.backgroundColor='var(--color-background-200)'"
                onmouseout="if(!this.classList.contains('active-filter')) this.style.backgroundColor='transparent'">
                All <span class="ml-1 text-sm">({{ $stats['all'] }})</span>
            </button>
            <button
                wire:click="$set('statusFilter', 'active')"
                class="px-4 py-2 rounded-t-lg transition-all {{ $statusFilter === 'active' ? 'active-filter' : '' }}"
                style="{{ $statusFilter === 'active' ? 'background-color: var(--color-glaucous); color: white;' : 'color: var(--color-text-600); background-color: transparent;' }}"
                onmouseover="if(!this.classList.contains('active-filter')) this.style.backgroundColor='var(--color-background-200)'"
                onmouseout="if(!this.classList.contains('active-filter')) this.style.backgroundColor='transparent'">
                Active <span class="ml-1 text-sm">({{ $stats['active'] }})</span>
            </button>
            <button
                wire:click="$set('statusFilter', 'in_progress')"
                class="px-4 py-2 rounded-t-lg transition-all {{ $statusFilter === 'in_progress' ? 'active-filter' : '' }}"
                style="{{ $statusFilter === 'in_progress' ? 'background-color: var(--color-glaucous); color: white;' : 'color: var(--color-text-600); background-color: transparent;' }}"
                onmouseover="if(!this.classList.contains('active-filter')) this.style.backgroundColor='var(--color-background-200)'"
                onmouseout="if(!this.classList.contains('active-filter')) this.style.backgroundColor='transparent'">
                In Progress <span class="ml-1 text-sm">({{ $stats['in_progress'] }})</span>
            </button>
            <button
                wire:click="$set('statusFilter', 'completed')"
                class="px-4 py-2 rounded-t-lg transition-all {{ $statusFilter === 'completed' ? 'active-filter' : '' }}"
                style="{{ $statusFilter === 'completed' ? 'background-color: var(--color-glaucous); color: white;' : 'color: var(--color-text-600); background-color: transparent;' }}"
                onmouseover="if(!this.classList.contains('active-filter')) this.style.backgroundColor='var(--color-background-200)'"
                onmouseout="if(!this.classList.contains('active-filter')) this.style.backgroundColor='transparent'">
                Completed <span class="ml-1 text-sm">({{ $stats['completed'] }})</span>
            </button>
            <button
                wire:click="$set('statusFilter', 'on_hold')"
                class="px-4 py-2 rounded-t-lg transition-all {{ $statusFilter === 'on_hold' ? 'active-filter' : '' }}"
                style="{{ $statusFilter === 'on_hold' ? 'background-color: var(--color-glaucous); color: white;' : 'color: var(--color-text-600); background-color: transparent;' }}"
                onmouseover="if(!this.classList.contains('active-filter')) this.style.backgroundColor='var(--color-background-200)'"
                onmouseout="if(!this.classList.contains('active-filter')) this.style.backgroundColor='transparent'">
                On Hold <span class="ml-1 text-sm">({{ $stats['on_hold'] }})</span>
            </button>
            <button
                wire:click="$set('statusFilter', 'archived')"
                class="px-4 py-2 rounded-t-lg transition-all {{ $statusFilter === 'archived' ? 'active-filter' : '' }}"
                style="{{ $statusFilter === 'archived' ? 'background-color: var(--color-glaucous); color: white;' : 'color: var(--color-text-600); background-color: transparent;' }}"
                onmouseover="if(!this.classList.contains('active-filter')) this.style.backgroundColor='var(--color-background-200)'"
                onmouseout="if(!this.classList.contains('active-filter')) this.style.backgroundColor='transparent'">
                Archived <span class="ml-1 text-sm">({{ $stats['archived'] }})</span>
            </button>
        </div>

        <!-- Search and Sort Controls -->
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
            <!-- Search Input -->
            <div class="flex-1 max-w-md relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search projects by name or description..."
                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:border-transparent transition-all"
                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);">
                @if($search)
                    <button
                        wire:click="$set('search', '')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Sort Dropdown -->
            <div class="flex gap-2">
                <select
                    wire:model.live="sortBy"
                    class="px-4 py-2 rounded-lg border focus:ring-2 focus:border-transparent transition-all"
                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);">
                    <option value="updated_at">Last Updated</option>
                    <option value="created_at">Created Date</option>
                    <option value="name">Name</option>
                    <option value="status">Status</option>
                </select>

                <button
                    wire:click="sortByColumn('{{ $sortBy }}')"
                    class="px-4 py-2 rounded-lg border transition-colors"
                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-700);">
                    @if($sortDirection === 'asc')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    @endif
                </button>

                <a
                    href="{{ route('projects.create') }}"
                    class="px-6 py-2 rounded-lg transition-colors font-medium"
                    style="background-color: var(--color-bittersweet); color: white;"
                    onmouseover="this.style.opacity='0.9'"
                    onmouseout="this.style.opacity='1'">
                    + New Project
                </a>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div>
        <!-- Loading Overlay -->
        <div wire:loading class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2" style="border-color: var(--color-glaucous);"></div>
            <p class="mt-2" style="color: var(--color-text-600);">Loading projects...</p>
        </div>

        <div wire:loading.class="opacity-50 pointer-events-none">
        @if($projects->isEmpty())
            <div class="text-center py-16">
                <x-icon.folder class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-400);" />
                <h3 class="text-xl font-semibold mb-2" style="color: var(--color-text-800);">No projects found</h3>
                <p class="mb-4" style="color: var(--color-text-600);">
                    @if($search || $statusFilter !== 'all')
                        No projects match your criteria. Try adjusting your filters.
                    @else
                        Get started by creating your first project!
                    @endif
                </p>
                @if(!$search && $statusFilter === 'all')
                    <a
                        href="{{ route('projects.create') }}"
                        class="inline-block px-6 py-3 rounded-lg transition-colors font-medium"
                        style="background-color: var(--color-bittersweet); color: white;"
                        onmouseover="this.style.opacity='0.9'"
                        onmouseout="this.style.opacity='1'">
                        Create Your First Project
                    </a>
                @endif
            </div>
        @else
            <!-- Results Count -->
            <div class="mb-4 flex items-center justify-between">
                <p style="color: var(--color-text-600);">
                    Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} projects
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($projects as $project)
                    <x-dashboard.project-card :project="$project" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $projects->links() }}
            </div>
        @endif
        </div>
    </div>
</div>
