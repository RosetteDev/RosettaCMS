@extends('admin.layouts.app')

@push('styles')
<style>
    .bulk-actions {
        margin-bottom: 1rem;
        display: none;
    }
    
    .bulk-actions.visible {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        padding: 0.75rem;
        background-color: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    
    .bulk-actions select {
        padding: 0.5rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        min-width: 150px;
    }
    
    .bulk-actions .btn {
        padding: 0.5rem 1rem;
    }
    
    .bulk-actions .btn-apply {
        background-color: var(--primary-color);
        color: white;
    }
    
    .bulk-actions .btn-cancel {
        background-color: #6c757d;
        color: white;
    }
    
    .table-checkbox {
        width: 40px;
        text-align: center;
    }
    
    .post-item {
        transition: background-color 0.2s;
    }
    
    .post-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .post-item.selected {
        background-color: rgba(59, 130, 246, 0.1);
    }
</style>
@endpush

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Posts</h1>
            <p>Manage your content and articles</p>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActions" class="bulk-actions">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-right: 1rem;">
            <input type="checkbox" id="selectAll">
            <label for="selectAll" style="margin: 0;">Select All</label>
        </div>
        
        <select id="bulkActionSelect">
            <option value="">Select Action...</option>
            <option value="publish">Publish Selected</option>
            <option value="unpublish">Unpublish Selected</option>
            <option value="trash">Move to Trash</option>
            <option value="restore">Restore from Trash</option>
            <option value="delete">Delete Permanently</option>
            <option value="assign">Assign to author…</option>
        </select>

        <select id="bulkAssignUser" class="form-select form-select-sm" style="display:none; max-width: 220px;">
            <option value="">Choose author…</option>
            @foreach($users ?? [] as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>

        <button id="applyBulkAction" class="btn btn-primary btn-apply">Apply</button>
        <button id="cancelBulkAction" class="btn btn-secondary btn-cancel">Cancel</button>
        
        <span id="bulkCount" style="margin-left: 1rem; color: var(--text-color);">0 posts selected</span>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ url('/admin/posts/create') }}" class="btn btn-primary">+ New Post</a>
        </div>
        <div>
            <form style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..." style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; width: 200px;">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <!-- Posts Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($posts) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th class="table-checkbox" style="padding: 1rem; text-align: center;">
                            <input type="checkbox" id="headerSelectAll">
                        </th>
                        <th style="padding: 1rem; text-align: left;">Title</th>
                        <th style="padding: 1rem; text-align: left;">Author</th>
                        <th style="padding: 1rem; text-align: left;">Status</th>
                        <th style="padding: 1rem; text-align: left;">Published</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr class="post-item" data-post-id="{{ $post->id }}" style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem; text-align: center;">
                                <input type="checkbox" class="post-checkbox" value="{{ $post->id }}">
                            </td>
                            <td style="padding: 1rem;">
                                <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ Str::limit($post->title, 50) }}
                                </a>
                                <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                    {{ Str::limit($post->slug, 30) }}
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $post->user->name ?? 'Unknown' }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($post->isScheduled())
                                    <span class="status-badge" style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: var(--secondary-color); color: white;">
                                        Scheduled
                                    </span>
                                @else
                                    <span class="status-badge" style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;
                                        @if($post->status === 'published') background-color: var(--success-color); color: white;
                                        @elseif($post->status === 'draft') background-color: var(--warning-color); color: white;
                                        @elseif($post->status === 'trash') background-color: #dc3545; color: white;
                                        @else background-color: var(--secondary-color); color: white; @endif">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $post->published_at ? $post->published_at->diffForHumans() : 'Not published' }}
                                @if($post->expires_at)
                                    <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                        Expires {{ $post->expires_at->diffForHumans() }}
                                    </div>
                                @endif
                                @if($post->isRecurring())
                                    <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                        Repeats {{ $post->repeat_frequency }}@if($post->repeat_until) until {{ $post->repeat_until->toDateString() }}@endif
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit</a>
                                <a href="{{ url('/admin/posts/' . $post->id . '/revisions') }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem;" title="View Revisions">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $posts->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No posts found. <a href="{{ url('/admin/posts/create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const postCheckboxes = document.querySelectorAll('.post-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            const headerSelectAllCheckbox = document.getElementById('headerSelectAll');
            const bulkActions = document.getElementById('bulkActions');
            const bulkActionSelect = document.getElementById('bulkActionSelect');
            const applyBulkActionBtn = document.getElementById('applyBulkAction');
            const cancelBulkActionBtn = document.getElementById('cancelBulkAction');
            const bulkCount = document.getElementById('bulkCount');
            const bulkActionUrl = '{{ route("admin.posts.bulk") }}';

            // Select all functionality
            function updateSelectAll() {
                const checkedCount = document.querySelectorAll('.post-checkbox:checked').length;
                const totalCount = postCheckboxes.length;
                
                selectAllCheckbox.checked = checkedCount === totalCount && totalCount > 0;
                headerSelectAllCheckbox.checked = checkedCount === totalCount && totalCount > 0;
                
                bulkCount.textContent = `${checkedCount} posts selected`;
                bulkActions.classList.toggle('visible', checkedCount > 0);
            }

            // Select all checkboxes
            function toggleAll(checked) {
                postCheckboxes.forEach(checkbox => {
                    checkbox.checked = checked;
                    const row = checkbox.closest('.post-item');
                    if (checked) {
                        row.classList.add('selected');
                    } else {
                        row.classList.remove('selected');
                    }
                });
                updateSelectAll();
            }

            // Individual checkbox handling
            postCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const row = this.closest('.post-item');
                    row.classList.toggle('selected', this.checked);
                    updateSelectAll();
                });
            });

            // Header select all
            headerSelectAllCheckbox.addEventListener('change', function() {
                toggleAll(this.checked);
            });

            // Select all in bulk actions bar
            selectAllCheckbox.addEventListener('change', function() {
                toggleAll(this.checked);
            });

            // Show bulk actions bar on any selection
            postCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        bulkActions.classList.add('visible');
                    }
                });
            });

            // Cancel bulk actions
            cancelBulkActionBtn.addEventListener('click', function() {
                postCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.closest('.post-item').classList.remove('selected');
                });
                bulkActions.classList.remove('visible');
                bulkActionSelect.value = '';
                updateSelectAll();
            });

            // Toggle author selector when "assign" is chosen
            const bulkAssignUser = document.getElementById('bulkAssignUser');
            bulkActionSelect.addEventListener('change', function() {
                if (bulkAssignUser) {
                    bulkAssignUser.style.display = this.value === 'assign' ? '' : 'none';
                }
            });

            // Apply bulk action
            applyBulkActionBtn.addEventListener('click', async function() {
                const action = bulkActionSelect.value;
                if (!action) {
                    alert('Please select an action');
                    return;
                }

                const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    alert('Please select posts to apply the action to');
                    return;
                }

                if (action === 'assign' && (!bulkAssignUser || !bulkAssignUser.value)) {
                    alert('Please choose an author to assign to');
                    return;
                }

                const ids = Array.from(checkedBoxes).map(checkbox => checkbox.value);

                try {
                    const response = await fetch(bulkActionUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        },
                        body: JSON.stringify({
                            ids: ids,
                            action: action,
                            assign_user_id: action === 'assign' ? parseInt(bulkAssignUser.value, 10) : null
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        // Refresh the page to show updated state
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to perform bulk action'));
                    }
                } catch (error) {
                    console.error('Bulk action error:', error);
                    alert('An error occurred while performing the bulk action');
                }
            });
        });
    </script>
    @endpush
@endsection