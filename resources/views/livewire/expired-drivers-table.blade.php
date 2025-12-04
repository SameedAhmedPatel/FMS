<div>
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5><i class="icon-user mr-2"></i>
                    <span class="font-weight-semibold">Drivers with Expired Documents</span>
                </h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge badge-danger mr-2">{{ $expiredDrivers->total() }} Expired</span>
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#allDriversModal">
                        View All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- /page header -->
    <div>
        @if($expiredDrivers->total() > 0 || true)
            <div class="card">
                <div class="card-body">

                    <!-- ✅ Date Filters above the table -->
{{--                    <div class="row mb-3">--}}
{{--                        <div class="col-md-3">--}}
{{--                            <label class="font-size-sm">From Date:</label>--}}
{{--                            <input type="date" class="form-control form-control-sm" wire:model.lazy="fromDate" wire:change="filterDrivers">--}}
{{--                        </div>--}}
{{--                        <div class="col-md-3">--}}
{{--                            <label class="font-size-sm">To Date:</label>--}}
{{--                            <input type="date" class="form-control form-control-sm" wire:model.lazy="toDate" wire:change="filterDrivers">--}}
{{--                        </div>--}}
{{--                        <div class="col-md-3 d-flex align-items-end">--}}
{{--                            <button wire:click="clearFilters" class="btn btn-secondary btn-sm w-100">--}}
{{--                                Clear Filters--}}
{{--                            </button>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    <!-- ✅ Reason Filter above the table -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="font-size-sm">Filter by Reason:</label>
                            <select class="form-control form-control-sm" wire:model="filterReason" wire:change="filterDrivers">
                                <option value="">All Reasons</option>
                                @foreach($reasonList as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button wire:click="clearFilters" class="btn btn-secondary btn-sm w-100">
                                Clear Filters
                            </button>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Search:</label>
                            <input type="text" id="de_search"  class="form-control" placeholder="Search drivers..." wire:model.debounce.500ms="search">
                        </div>
                    </div>


                    <table class="table table-striped table-hover table-sm" id="drivers-table">
                        <thead class="thead-light">
                        <tr>
                            <th class="font-size-sm">Serial No</th>
                            <th class="font-size-sm">Name</th>
                            <th class="font-size-sm">Status</th>
                            <th class="font-size-sm">Reason</th>
                            <th class="text-center font-size-sm">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($expiredDrivers->total() > 0)
                        @foreach($expiredDrivers as $driver)
                            <tr>
                                <td class="font-size-sm">{{ $driver['serial_no'] }}</td>
                                <td class="font-size-sm">{{ $driver['name'] }}</td>
                                <td><span class="badge badge-warning badge-sm">{{ $driver['status'] }}</span></td>
                                <td class="font-size-sm"><span class="text-danger">{{ $driver['reason'] }}</span></td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('admin.drivers.edit', $driver['id']) }}" class="dropdown-item font-size-sm">
                                                    <i class="icon-pencil7"></i> Edit Driver
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @else
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                                    <h4 class="text-muted mt-3">All drivers have valid documents!</h4>
                                    <p class="text-muted">No expired documents found.</p>
                                </div>
                            </div>
                        @endif
                        </tbody>
                    </table>
 
                   
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted small">
                                Showing {{ $expiredDrivers->firstItem() }} to {{ $expiredDrivers->lastItem() }}
                                of {{ $expiredDrivers->total() }} results
                            </div>
                        </div>
                        {{ $expiredDrivers->links('vendor.pagination.custom-short') }}
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">All drivers have valid documents!</h4>
                    <p class="text-muted">No expired documents found.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal (same as before) -->
    <div class="modal fade" id="allDriversModal" tabindex="-1" role="dialog"
         aria-labelledby="allDriversModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allDriversModalLabel">Expired Drivers List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="thead-light">
                        <tr>
                            <th>Serial No</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($expiredDrivers as $driver)
                            <tr>
                                <td>{{ $driver['serial_no'] }}</td>
                                <td>{{ $driver['name'] }}</td>
                                <td><span class="badge badge-warning">{{ $driver['status'] }}</span></td>
                                <td><span class="text-danger">{{ $driver['reason'] }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expiredDrivers->links() }}
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#allDriversModal').on('hidden.bs.modal', function () {
            const cleanUrl = '/admin/dashboard';
            window.history.replaceState({}, document.title, cleanUrl);
            setTimeout(() => {
                window.location.reload();
            }, 100);
        });
    });
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    const input = document.getElementById('de_search');

    input.addEventListener('keyup', function() {
        @this.set('search', input.value); // Livewire will re-render automatically
    });
</script>
