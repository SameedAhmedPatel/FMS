<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehiclesAttendance;
use App\Models\Vehicle;
use App\Models\Station;
use App\Models\AttendanceStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehiclesAttendanceController extends Controller
{
    public function index(Request $request){
        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : Carbon::now();
        $station_id = $request->station_id;

        $vehicleAttendances = VehiclesAttendance::where('is_active', 1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['attendanceStatus','vehicle.station','vehicle.ibcCenter','vehicle.shiftHours'])->orderby('id','DESC')
            ->with('vehicle');

        if ($request->filled('station_id')) {
            $vehicleAttendances = $vehicleAttendances->whereHas('vehicle.station', function ($q) use ($request) {
                $q->where('id', $request->station_id);
            });
        }

        if ($request->filled('vehicle_id')) {
            $vehicleAttendances = $vehicleAttendances->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vehicle_id', $request->vehicle_id);
            });
        }

        $vehicleAttendances = $vehicleAttendances->whereBetween('date', [
            $fromDate->toDateString(),
            $toDate->toDateString(),
        ]);
        $vehicleAttendances = $vehicleAttendances->orderBy('id','DESC');
        $vehicleAttendances = $vehicleAttendances->get();

            $stations = Station::orderBy('area', 'asc')->get(); // get list for dropdown

        return view('admin.vehicleAttendances.index', compact('vehicleAttendances','stations'));
    }

    public function create(Request $request){

        $excludeStatuses = ['OFF'];
        $attendanceStatus = AttendanceStatus::where('is_active', 1)->where('id','!=',3)->whereNotIn('name', $excludeStatuses)->orderBy('id')->pluck('name', 'id');

        $vehicles = Vehicle::with(['station','shiftHours','ibcCenter']);
        $poolvehicles = Vehicle::where('pool_vehicle', 1)->get();
        $vehicles = $vehicles->where('is_active', 1);
        if (isset($request->station_id)) {
            $vehicles = $vehicles->where('station_id', $request->station_id);
        }

        if (isset($request->vechicle_id)) {
            $vehicles = $vehicles->where('id', $request->vechicle_id);
        }
        $vehicles = $vehicles->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1));
        $vehicles = $vehicles->orderBy('vehicle_no');
        $vehicles = $vehicles->get();

        $vehicleData = array();

        foreach($vehicles as $vehicle){

            $vehicleData[] = array(
                'vehicle_id'    =>  $vehicle->id,
                'station'       =>  $vehicle->station->area,
                'vehicle_no'    =>  $vehicle->vehicle_no,
                'shift'         =>  $vehicle->shiftHours->name,
                'make'          =>  $vehicle->make,
                'model'         =>  $vehicle->model,
                'ibcCenter'     =>  $vehicle->ibcCenter->name
            );
        }

        $stations = Vehicle::with('station');
        $stations = $stations->where('is_active', 1);
        $stations = $stations->get();
        $stations = $stations->pluck('station.area', 'station_id');
        $stations = $stations->sort();
        $stations = $stations->unique();
        $stations = $stations->toArray();

        $selectedStation = $request->station_id ?? '';

        return view('admin.vehicleAttendances.create', compact('vehicles','stations','selectedStation','vehicleData','attendanceStatus','poolvehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $date = $request->input('date');
        $vehicleIds = $request->input('vehicle_id', []);
        $statuses = $request->input('status', []);
        $poolIds = $request->input('pool_id', []);

        $allowedStatusIds = AttendanceStatus::where('is_active', 1)
            ->where('id', '!=', 3)
            ->pluck('id')
            ->toArray();

        $fieldErrors = [];
        $toInsert = [];
        $selectedCount = 0;
        $firstVehicleId = null;

        foreach ($vehicleIds as $vehicleId) {
            $vehicleId = (int) $vehicleId;
            if ($vehicleId <= 0) {
                continue;
            }
            if ($firstVehicleId === null) {
                $firstVehicleId = $vehicleId;
            }

            $statusId = $statuses[(string) $vehicleId] ?? null;
            $poolId = $poolIds[(string) $vehicleId] ?? null;

            if (empty($statusId)) {
                continue;
            }

            $selectedCount++;

            $vehicleIsActive = Vehicle::where('id', $vehicleId)
                ->where('is_active', 1)
                ->exists();
            if (!$vehicleIsActive) {
                $fieldErrors['status.' . $vehicleId] = 'Invalid or inactive vehicle selected.';
                continue;
            }

            if (!in_array((int) $statusId, $allowedStatusIds, true)) {
                $fieldErrors['status.' . $vehicleId] = 'Invalid attendance status selected.';
                continue;
            }

            $exists = VehiclesAttendance::where('date', $date)
                ->where('vehicle_id', $vehicleId)
                ->where('is_active', 1)
                ->exists();

            if ($exists) {
                $prettyDate = Carbon::parse($date)->format('d-M-Y');
                $fieldErrors['status.' . $vehicleId] = 'Attendance already marked for this vehicle on ' . $prettyDate . '.';
                continue;
            }

            $attendanceData = [
                'vehicle_id' => $vehicleId,
                'date'       => $date,
                'status'     => (int) $statusId,
            ];

            if ($poolId) { // If pool_id is provided, include it in the data
                $attendanceData['pool_id'] = $poolId;
            }

            $toInsert[] = $attendanceData;

        }

        if ($selectedCount === 0) {
            if ($firstVehicleId !== null) {
                $fieldErrors['status.' . $firstVehicleId] = 'Please select attendance for at least one vehicle.';
            } else {
                $fieldErrors['date'] = 'Please select attendance for at least one vehicle.';
            }
        }

        if (!empty($fieldErrors)) {
            return back()
                ->withInput()
                ->withErrors($fieldErrors);
        }

        foreach ($toInsert as $row) {
            VehiclesAttendance::create($row);
        }

        return redirect()->route('admin.vehicleAttendances.index')->with('success', 'Vehicle Attendances created successfully.');
    }

    public function edit(VehiclesAttendance $vehicleAttendance){
        $vehicleAttendance->load(['attendanceStatus','vehicle.station','vehicle.shiftHours']);

        $attendanceStatus = AttendanceStatus::where('is_active', 1)
            ->where('id','!=',3)
            ->orderBy('id')
            ->pluck('name', 'id');

        return view('admin.vehicleAttendances.edit',compact('vehicleAttendance','attendanceStatus'));
    }

    public function update(Request $request, VehiclesAttendance $vehicleAttendance){
        $validated = $request->validate([
            'date'   => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'exists:attendance_status,id'],
        ]);

        $date = $validated['date'];
        $status = $validated['status'];

        $exists = VehiclesAttendance::where('vehicle_id', $vehicleAttendance->vehicle_id)
            ->where('date', $date)
            ->where('is_active',1)
            ->where('id', '!=', $vehicleAttendance->id)
            ->exists();

        if ($exists) {
            $prettyDate = Carbon::parse($date)->format('d-M-Y');
            return back()
                ->withInput()
                ->withErrors(['status' => 'Attendance already marked for this Vehicle on ' . $prettyDate . '.']);
        }

        $vehicleAttendance->update([
            'date'   => $date,
            'status' => $status,
        ]);

        return redirect()->route('admin.vehicleAttendances.index')
            ->with('success', 'Vehicle Attendance updated successfully');
    }

    public function show(VehiclesAttendance $vehicleAttendance){
        $vehicleAttendance->load(['attendanceStatus','vehicle.station','vehicle.shiftHours']);
        return view('admin.vehicleAttendances.show', compact('vehicleAttendance'));
    }

    public function destroy(VehiclesAttendance $vehicleAttendance){
        $vehicleAttendance->is_active = 0;
        $vehicleAttendance->save();

        return redirect()->route('admin.vehicleAttendances.index')->with('delete_msg', 'Vehicle Attendances deleted successfully.');
    }
}
