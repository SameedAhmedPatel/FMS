@extends('layouts.admin')

@section('title', 'Add Driver Attendance')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Driver Attendance Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-primary">
            <span>View Driver Attendance <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.driverAttendances.filter') }}" method="POST">
          @csrf
          <div class="row">
            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <label class="form-label"><strong>Driver Status</strong></label>
                <select class="custom-select select2" name="driver_status_id" id="driver_status_id">
                  <option value="" {{ empty($selected_driver_status_id) ? 'selected' : '' }}>ALL</option>
                  @foreach($driver_status as $key => $value)
                    <option value="{{ $key }}" {{ (isset($selected_driver_status_id) && (string)$selected_driver_status_id === (string)$key) ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
              </div>
            </div>

              <div class="col-md-3">
                  <div class="form-group">
                      <label class="form-label"><strong>Station</strong></label>
                      <select class="custom-select select2" name="station_id" id="station_id">
                          <option value="" {{ empty($selected_driver_status_id) ? 'selected' : '' }}>ALL</option>
                          @foreach($stations as $station)
                              <option value="{{ $station->id }}" {{ old('station_id') == $station->id ? 'selected' : '' }}>
                                  {{ $station->area }}
                              </option>
                          @endforeach
                      </select>
                  </div>
              </div>


              <div class="col-md-3 mt-4">
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.driverAttendances.create') }}" class="btn btn-primary">Reset</a>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.driverAttendances.store') }}" method="POST" enctype="multipart/form-data">
          @csrf


          <div class="row mb-3">
            <!-- Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Date </strong>
                <input type="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ old('date') }}" max="{{ date('Y-m-d') }}">
                @error('date')
                  <label class="text-danger">{{ $message }}</label>
                @enderror
              </div>
            </div>

            <!-- Bulk Actions -->
            <div class="col-md-10">
              <div class="form-group">
                <div class="mb-2">
                  <strong>Bulk Actions:</strong>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                  @foreach($driver_attendance_status as $id => $status)
                    <button type="button" class="btn btn-sm btn-outline-primary status-btn" data-status-id="{{ $id }}">
                      {{ $status }}
                    </button>
                  @endforeach
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="selectAll">
                  <label class="form-check-label font-weight-bold" for="selectAll">Select All Drivers</label>
                </div>
              </div>
            </div>
          </div>

          @foreach($drivers as $i => $driver)
    <div class="row align-items-center mb-3">
        <!-- Checkbox -->
        <div class="col-auto pr-0 d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" class="form-check-input driver-checkbox" data-driver-id="{{ $driver->id }}" style="margin-top: 0;">
            </div>
        </div>
        <input type="hidden" class="form-control" name="driver_id[]" value="{{ $driver->id }}">

        <!-- Driver Name -->
        <div class="col-md-2">
            <div class="form-group">
                <strong>Driver</strong>
                <input type="text" class="form-control" name="full_name[]" value="{{ $driver->full_name }}" readonly>
            </div>
        </div>

        <!-- CNIC -->
        <div class="col-md-2">
            <div class="form-group">
                <strong>CNIC No</strong>
                <input type="text" class="form-control" name="cnicno[]" value="{{ $driver->cnic_no }}" readonly>
            </div>
        </div>

        <!-- Account No -->
        <div class="col-md-2">
            <div class="form-group">
                <strong>Account No</strong>
                <input type="text" class="form-control" name="accountno[]" value="{{ $driver->account_no }}" readonly>
            </div>
        </div>

        <!-- Shift -->
        <div class="col-md-1">
            <div class="form-group">
                <strong>Shift</strong>
                <input type="text" class="form-control" name="shift[]" value="{{ $driver->shiftTiming ? $driver->shiftTiming->name . ' (' . \Carbon\Carbon::parse($driver->shiftTiming->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($driver->shiftTiming->end_time)->format('h:i A') . ')' : 'N/A' }}" readonly>
            </div>
        </div>

        <!-- Status -->
        <div class="col-md-1">
            <div class="form-group">
                <strong>Status</strong>
                <input type="text" class="form-control" name="driverStatus[]" value="{{ $driver->driverStatus->name ?? 'N/A' }}" readonly>
            </div>
        </div>

        <!-- Station (optional, if needed) -->
        {{-- 
        <div class="col-md-1">
            <div class="form-group">
                <strong>Station</strong>
                <input type="text" class="form-control" name="station[]" value="{{ $driver->vehicle->station->area ?? '' }}" readonly>
            </div>
        </div> 
        --}}

        <!-- Attendance -->
        <div class="col-md-2">
            <div class="form-group">
                <strong>Attendance</strong>
                <select class="custom-select @error('status.' . $i) is-invalid @enderror" name="status[]" data-driver-idx="{{ $i }}">
                    <option value="">Select</option>
                    @foreach($driver_attendance_status as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" {{ old('status.' . $i) == (string)$statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                @error("status.$i")
                    <label class="text-danger">{{ $message }}</label>
                @enderror
            </div>
        </div>
    </div>
@endforeach


          <div class="row">
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.driverAttendances.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle status button clicks
    $('.status-btn').on('click', function() {
        const statusId = $(this).data('status-id');
        const statusName = $(this).text().trim();

        // Find all checked checkboxes
        const $checkedBoxes = $('.driver-checkbox:checked');

        if ($checkedBoxes.length === 0) {
            // Show error if no drivers are selected
            new Noty({
                type: 'error',
                text: 'Please select at least one driver',
                timeout: 3000
            }).show();
            return;
        }

        // Update status for each selected driver
        $checkedBoxes.each(function() {
            const driverIdx = $(this).data('driver-id');
            $(`select[name='status[]'][data-driver-idx='${driverIdx}']`).val(statusId);
        });

        // Show success message
        new Noty({
            type: 'success',
            text: `Updated attendance to ${statusName} for ${$checkedBoxes.length} driver(s)`,
            timeout: 3000
        }).show();
    });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.driver-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Uncheck "Select All" if any checkbox is unchecked
    $('.driver-checkbox').on('change', function() {
        if (!$(this).prop('checked')) {
            $('#selectAll').prop('checked', false);
        } else {
            // If all checkboxes are checked, check "Select All"
            if ($('.driver-checkbox:not(:checked)').length === 0) {
                $('#selectAll').prop('checked', true);
            }
        }
    });
});
</script>
@endpush
