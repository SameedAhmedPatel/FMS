
<div>
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h5>
                    <i class="icon-truck mr-2"></i>
                    <span class="font-weight-semibold">Vehicles with Expired Dates</span>
                </h5>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>

            <div class="header-elements d-none">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="badge badge-danger mr-2">{{ $mainVehicles->count() }} Expired</span>

                    <!-- OPEN MODAL -->
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#allVehiclesModal">
                        View All
                    </button>
                </div>
            </div>

        </div>
    </div>
    <!-- /page header -->


    <div>
        @if($mainVehicles->count() > 0 || true)
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Filter by Reason:</label>
                            <select class="form-control form-control-sm" wire:model="reason" wire:change="filterVechile">
                                <option value="">All Reasons</option>
                                @foreach ($reasonList as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-12 d-flex align-items-end mt-2 mt-md-0">
                            <button wire:click="clearFilters" class="btn btn-secondary btn-sm w-100">
                                Clear Filters
                            </button>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="font-size-sm">Search:</label>
                            <input type="text" id="ve_search" class="form-control"
       wire:model.debounce.500ms="search"
       placeholder="Search vehicles...">
                        </div>
                    </div>
                    <!-- MAIN TABLE (WITH PAGINATION) -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm" id="vehicles-table">
                            <thead class="thead-light">
                            <tr>
                                <th class="font-size-sm">Serial No</th>
                                <th class="font-size-sm">Vehicle No</th>
                                <th class="font-size-sm">Model</th>
                                <th class="font-size-sm">Type</th>
                                <th class="font-size-sm">Station</th>
                                <th class="font-size-sm">Reason</th>
                                <th class="text-center font-size-sm">Actions</th>
                            </tr>
                            </thead>

                            <tbody>
                            @if($mainVehicles->count() > 0)
                            @foreach($mainVehicles as $vehicle)
                                <tr>
                                    <td class="font-size-sm" data-label="Serial No">{{ str_pad($vehicle->id, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td class="font-size-sm" data-label="Vehicle No">{{ $vehicle->vehicle_no }}</td>
                                    <td class="font-size-sm" data-label="Model">{{ $vehicle->model }}</td>
                                    <td class="font-size-sm" data-label="Type">{{ $vehicle->vehicleType->name ?? 'N/A' }}</td>
                                    <td class="font-size-sm" data-label="Station">{{ $vehicle->station->area ?? 'N/A' }}</td>
                                    <td class="font-size-sm" data-label="Reason">
                                    <span class="text-danger">
                                        {{ $this->getVehicleReasonLabel($vehicle) }}
                                    </span>
                                    </td>
                                    <td class="text-center" data-label="Actions">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}"
                                                       class="dropdown-item font-size-sm">
                                                        <i class="icon-pencil7"></i> Edit Vehicle
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @else
                            <tr><td colspan="7">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                                        <h4 class="text-muted mt-3">All vehicles have valid dates!</h4>
                                        <p class="text-muted">No expired dates found.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Show Pagination Only if Filter is Applied -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small">
                            Showing {{ $mainVehicles->firstItem() }} to {{ $mainVehicles->lastItem() }}
                            of {{ $mainVehicles->total() }} results
                        </div>
                    </div>
                    {{ $mainVehicles->links('vendor.pagination.custom-short') }}

                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="icon-checkmark-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">All vehicles have valid dates!</h4>
                    <p class="text-muted">No expired dates found.</p>
                </div>
            </div>
        @endif
    </div>




    <!-- ============================= MODAL ============================== -->
    <div class="modal fade" id="allVehiclesModal" tabindex="-1" role="dialog"
         aria-labelledby="allVehiclesModalLabel" aria-hidden="true" wire:ignore.self>

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="allVehiclesModalLabel">Expired Vehicles List</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0" style="overflow-x: auto; max-height: 70vh;">
                    <div class="table-responsive">

                        <!-- MODAL TABLE WITH PAGINATION -->
                        <table class="table table-striped table-hover table-sm mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>Serial No</th>
                                <th>Vehicle No</th>
                                <th>Model</th>
                                <th>Type</th>
                                <th>Station</th>
                                <th>Reason</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($modalVehicles as $vehicle)
                                <tr>
                                    <td>{{ str_pad($vehicle->id, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $vehicle->vehicle_no }}</td>
                                    <td>{{ $vehicle->model }}</td>
                                    <td>{{ $vehicle->vehicleType->name ?? 'N/A' }}</td>
                                    <td>{{ $vehicle->station->area ?? 'N/A' }}</td>
                                    <td class="text-danger">
                                        {{ $this->getVehicleReasonLabel($vehicle) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>

                    </div>

                    <!-- PAGINATION ONLY IN MODAL -->
                    <div class="d-flex justify-content-center my-3">
                        {{ $modalVehicles->links() }}
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
    document.addEventListener('livewire:load', function () {
        Livewire.on('filterUpdated', (reason) => {
            console.log('Selected reason:', reason);  // Log the selected filter value
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        $('#allVehiclesModal').on('hidden.bs.modal', function () {
            const cleanUrl = '/admin/dashboard';
            window.history.replaceState({}, document.title, cleanUrl);
        });
    });
  
   
    const input1 = document.getElementById('ve_search');
    input1.addEventListener('keyup', function() {
        @this.set('search', input1.value);   // set the property manually
        @this.getFilteredVehicles();        // optionally call a function
    });
 
</script>
