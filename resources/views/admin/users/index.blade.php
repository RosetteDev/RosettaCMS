@php
    $statusLabels = [
        'active' => ['label' => 'Active', 'color' => '#10b981'],
        'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
        'disabled' => ['label' => 'Disabled', 'color' => '#ef4444'],
    ];
    
    $getUserStatus = function($user) use ($statusLabels) {
        if ($user->email_verified_at) {
            return $statusLabels['active'];
        }
        return $statusLabels['pending'];
    };
@endphp

@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Users</h1>
            <p>Manage your site users and their permissions</p>
        </div>
    </div>

    <!-- Filters -->
    <div style="background-color: var(--light-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <form method="GET" action="{{ url('/admin/users') }}" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="search" style="margin-bottom: 0.25rem; font-size: 0.85rem;">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or email" style="padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color);">
            </div>
            
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label for="role_id" style="margin-bottom: 0.25rem; font-size: 0.85rem;">Role</label>
                <select id="role_id" name="role_id" style="padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color);">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label for="status" style="margin-bottom: 0.25rem; font-size: 0.85rem;">Status</label>
                <select id="status" name="status" style="padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color);">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter</button>
            @if(request()->hasAny(['search', 'role_id', 'status']))
                <a href="{{ url('/admin/users') }}" class="btn btn-outline" style="padding: 0.5rem 1rem;">Clear</a>
            @endif
        </form>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ url('/admin/users/create') }}" class="btn btn-primary">+ New User</a>
            <a href="{{ url('/admin/users/pending') }}" class="btn btn-outline">
                Pending Verification ({{ $pendingUsersCount ?? $users->where('email_verified_at', null)->count() }})
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($users) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">User</th>
                        <th style="padding: 1rem; text-align: left;">Email</th>
                        <th style="padding: 1rem; text-align: left;">Roles</th>
                        <th style="padding: 1rem; text-align: left;">Status</th>
                        <th style="padding: 1rem; text-align: left;">Joined</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <?php $status = $getUserStatus($user); ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ $user->name }}
                                </a>
                                <div style="font-size: 0.85rem; color: #9ca3af;">ID: {{ $user->id }}</div>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $user->email }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($user->roles->count() > 0)
                                    @foreach($user->roles->take(3) as $role)
                                        <span style="display: inline-block; background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin-right: 0.25rem; margin-bottom: 0.25rem;">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                    @if($user->roles->count() > 3)
                                        <span style="display: inline-block; background-color: var(--secondary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                            +{{ $user->roles->count() - 3 }}
                                        </span>
                                    @endif
                                @else
                                    <span style="color: #9ca3af;">No roles</span>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <span style="display: inline-block; background-color: {{ $status['color'] }}20; color: {{ $status['color'] }}; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; border: 1px solid {{ $status['color'] }}40;">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color); font-size: 0.85rem;">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Edit</a>
                                    @if($status['label'] === 'Pending')
                                        <a href="{{ url('/admin/users/' . $user->id . '/verify') }}" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="return confirm('Verify this user?')">Verify</a>
                                    @else
                                        <a href="{{ url('/admin/users/' . $user->id . '/deactivate') }}" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="return confirm('Deactivate this user?')">Deactivate</a>
                                    @endif
                                    @if($user->id !== auth()->id())
                                        <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to delete this user?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No users found. <a href="{{ url('/admin/users/create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    <div style="padding: 1.5rem 0; display: flex; justify-content: center;">
        {{ $users->links() }}
    </div>

    <!-- Bulk Actions Modal -->
    <div id="bulkActionsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background-color: white; padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <h2 style="margin-bottom: 1.5rem;">Bulk Actions</h2>
            
            <form id="bulkActionsForm" method="POST" action="{{ url('/admin/users/bulk') }}">
                @csrf
                <input type="hidden" name="user_ids" id="bulkUserIds">
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="bulkAction" style="font-weight: 500;">Select Action</label>
                    <select id="bulkAction" name="action" onchange="toggleRoleField()" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                        <option value="">-- Select Action --</option>
                        <option value="delete">Delete Selected</option>
                        <option value="verify">Verify Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="add_role">Add Role to Selected</option>
                        <option value="remove_role">Remove Role from Selected</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;" id="roleField" style="display: none;">
                    <label for="bulkRoleId" style="font-weight: 500;">Select Role</label>
                    <select id="bulkRoleId" name="role_id" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeBulkModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="bulkConfirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Open bulk actions modal
    function openBulkModal(userIds) {
        document.getElementById('bulkUserIds').value = JSON.stringify(userIds);
        document.getElementById('bulkActionsModal').style.display = 'flex';
    }
    
    // Close bulk actions modal
    function closeBulkModal() {
        document.getElementById('bulkActionsModal').style.display = 'none';
        document.getElementById('bulkAction').value = '';
        document.getElementById('bulkRoleId').value = '';
        document.getElementById('roleField').style.display = 'none';
    }
    
    // Toggle role field based on action
    function toggleRoleField() {
        const action = document.getElementById('bulkAction').value;
        const roleField = document.getElementById('roleField');
        
        if (action === 'add_role' || action === 'remove_role') {
            roleField.style.display = 'block';
            document.getElementById('bulkConfirmBtn').textContent = 'Confirm';
            document.getElementById('bulkConfirmBtn').className = 'btn btn-danger';
        } else {
            roleField.style.display = 'none';
            document.getElementById('bulkConfirmBtn').textContent = action === 'delete' ? 'Delete Users' : 'Confirm';
            document.getElementById('bulkConfirmBtn').className = action === 'delete' ? 'btn btn-danger' : 'btn btn-primary';
        }
    }
    
    // Close modal when clicking outside
    document.getElementById('bulkActionsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeBulkModal();
        }
    });
    
    // Handle Enter key in search
    document.getElementById('search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });
</script>
@endpush
