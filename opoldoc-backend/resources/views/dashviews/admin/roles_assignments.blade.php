<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-1">
        <h2 class="text-sm font-semibold text-slate-900">Roles & assignments</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Structure</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Snapshot of how many users are assigned to each role in the system.
    </p>

    {{-- Role Snapshot: simple metric cards --}}
    @if (!empty($adminUserRoleCounts) && count($adminUserRoleCounts))
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 mb-6">
            @foreach ($adminUserRoleCounts as $role)
                <div class="bg-slate-50 rounded-xl px-3.5 py-3">
                    <p class="text-[0.72rem] text-slate-500 mb-1">{{ ucfirst($role->role_name) }}</p>
                    <p class="text-[1.6rem] font-semibold text-slate-900 leading-none">{{ $role->users_count }}</p>
                </div>
            @endforeach
        </div>
    @else
        <div class="mb-6 text-[0.78rem] text-slate-400">No roles defined yet.</div>
    @endif

    {{-- Divider --}}
    <div class="border-t border-slate-100 mb-4"></div>

    <p class="text-[0.78rem] text-slate-500">
        Detailed role assignment is now managed directly when creating or editing users.
        Use the Manage users section to change a user&apos;s role.
    </p>
</div>
