<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Vehicle;
use Carbon\Carbon;

class ExpiredVehiclesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Static reasons
    public $search = '';
    public $reason = '';
    public $reasonList = [
        'next_inspection_date'     => "Next Inspection Expiry",
        'next_fitness_date'        => "Next Fitness Expiry",
        'insurance_expiry_date'    => "Insurance Expiry",
        'route_permit_expiry_date' => "Route Permit Expiry",
        'next_tax_date'            => "Next Tax Expiry",
    ];

    /**
     * Reset pagination when reason changes (safe to call even if main table has no pagination)
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingdReason()
    {
        $this->resetPage();
    }

    public function filterVechile()
    {
        $this->resetPage();  // Reset pagination on filter change
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->reason = '';
        $this->resetPage();
    }

    /**
     * Shared query for expired/expiring vehicles up to next month end.
     * This returns an Eloquent\Builder which we can use get() or paginate() on.
     */
    protected function expiredBaseQuery()
    {
        return Vehicle::query()
            ->where('is_active', 1)
            ->with(['vehicleType', 'station']);
    }
    public function getFilteredVehicles()
{
    $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

    $query = $this->expiredBaseQuery();

    // Apply reason filter
    if (!empty($this->reason) && array_key_exists($this->reason, $this->reasonList)) {
        $query->where($this->reason, '<=', $nextMonthEnd);
    } else {
        $query->where(function ($q) use ($nextMonthEnd) {
            $q->where('next_inspection_date', '<=', $nextMonthEnd)
              ->orWhere('next_fitness_date', '<=', $nextMonthEnd)
              ->orWhere('insurance_expiry_date', '<=', $nextMonthEnd)
              ->orWhere('route_permit_expiry_date', '<=', $nextMonthEnd)
              ->orWhere('next_tax_date', '<=', $nextMonthEnd);
        });
    }

    // Apply search filter
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('vehicle_no', 'like', '%' . $this->search . '%')
            ->orWhere('model', 'like', '%' . $this->search . '%')   ;
             
        });
    }

    return $query;
}


    /**
     * Get the main table vehicles with pagination
     */
    public function getMainTableVehicles()
    {
        $query = $this->getFilteredVehicles();
        return $query->paginate(10);  // Apply pagination directly
    }

    /**
     * Get the modal vehicles with pagination
     */
    public function getModalVehicles()
    {
        $query = $this->getFilteredVehicles();
        return $query->paginate(10);  // Apply pagination directly
    }

    /**
     * Helper used by Blade to show a human readable reason label(s) for a vehicle.
     * Blade calls $this->getVehicleReasonLabel($vehicle) — so keep this public.
     */
    public function getVehicleReasonLabel($v)
    {
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();
        $labels = [];

        // If a specific reason filter is active → return ONLY that reason
        if (!empty($this->reason) && array_key_exists($this->reason, $this->reasonList)) {

            $field = $this->reason;
            $date  = $v->$field;

            if (!empty($date) && Carbon::parse($date)->lte($nextMonthEnd)) {
                return $this->reasonList[$field] . " (" . Carbon::parse($date)->format('d-M-Y') . ")";
            }

            return "-";  // if for some reason doesn't match
        }

        // Otherwise → return ALL expired reasons
        if (!empty($v->next_inspection_date) && Carbon::parse($v->next_inspection_date)->lte($nextMonthEnd)) {
            $labels[] = "Next Inspection (" . Carbon::parse($v->next_inspection_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->next_fitness_date) && Carbon::parse($v->next_fitness_date)->lte($nextMonthEnd)) {
            $labels[] = "Next Fitness (" . Carbon::parse($v->next_fitness_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->insurance_expiry_date) && Carbon::parse($v->insurance_expiry_date)->lte($nextMonthEnd)) {
            $labels[] = "Insurance (" . Carbon::parse($v->insurance_expiry_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->route_permit_expiry_date) && Carbon::parse($v->route_permit_expiry_date)->lte($nextMonthEnd)) {
            $labels[] = "Route Permit (" . Carbon::parse($v->route_permit_expiry_date)->format('d-M-Y') . ")";
        }
        if (!empty($v->next_tax_date) && Carbon::parse($v->next_tax_date)->lte($nextMonthEnd)) {
            $labels[] = "Next Tax (" . Carbon::parse($v->next_tax_date)->format('d-M-Y') . ")";
        }

        return count($labels) ? implode(', ', $labels) : '-';
    }


    /**
     * Render the view
     */
    public function render()
    {
        return view('livewire.expired-vehicles-table', [
            'mainVehicles'  => $this->getMainTableVehicles(),
            'modalVehicles' => $this->getModalVehicles(),
            'reasonList'    => $this->reasonList,
        ]);
    }
}
