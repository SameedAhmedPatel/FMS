<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Driver;
use Carbon\Carbon;

class ExpiredDriversTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $queryString = [];

    public $filterReason = '';
    public $reasonList = [];
    public $search = ''; // 🔍 added search

    /**
     * Build the base query
     */
    public function loadExpiredDriversQuery()
    {
        $today = Carbon::today();
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $query = Driver::where('is_active', 1)
            ->where(function ($query) use ($nextMonthEnd) {
                $query->where('cnic_expiry_date', '<=', $nextMonthEnd)
                      ->orWhere('license_expiry_date', '<=', $nextMonthEnd);
            })
            ->with(['driverStatus', 'vehicle'])
            ->whereHas('driverStatus', function ($query) {
                $query->where('name', '!=', 'Left');
            });

        /** Apply Reason Filter */
        $this->applyReasonFilter($query);

        /** Apply Search Filter */
        $this->applySearch($query);

        return $query;
    }

    /**
     * Apply search filter
     */
    public function applySearch($query)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_no', 'like', '%' . $this->search . '%')
                  ->orWhere('cnic_no', 'like', '%' . $this->search . '%');
            });
        }
    }

    /**
     * Apply reason filter
     */
    public function applyReasonFilter($query)
    {
        if (!empty($this->filterReason)) {
            $query->where(function ($q) {
                if ($this->filterReason === "CNIC Expiry") {
                    $q->where('cnic_expiry_date', '<=', Carbon::now()->addMonth()->endOfMonth());
                }

                if ($this->filterReason === "License Expiry") {
                    $q->where('license_expiry_date', '<=', Carbon::now()->addMonth()->endOfMonth());
                }
            });
        }
    }

    /**
     * Build dynamic reason list
     */
    public function getDynamicReasons()
    {
        $today = Carbon::today();
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        $allDrivers = $this->loadExpiredDriversQuery()->get();
        $dynamicReasons = [];

        foreach ($allDrivers as $driver) {

            // CNIC
            if ($driver->cnic_expiry_date) {
                $d = Carbon::parse($driver->cnic_expiry_date);
                if ($d->isPast() || $d->between($today, $nextMonthEnd)) {
                    $dynamicReasons[] = "CNIC Expiry";
                }
            }

            // LICENSE
            if ($driver->license_expiry_date) {
                $d = Carbon::parse($driver->license_expiry_date);
                if ($d->isPast() || $d->between($today, $nextMonthEnd)) {
                    $dynamicReasons[] = "License Expiry";
                }
            }
        }

        return array_unique($dynamicReasons);
    }

    /**
     * Format driver results
     */
    public function formatDrivers($drivers)
    {
        return $drivers->through(function ($driver) {

            $reasons = [];

            $today = Carbon::today();
            $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

            // CNIC
            if ($driver->cnic_expiry_date) {
                $cnic = Carbon::parse($driver->cnic_expiry_date);
                $formatted = $cnic->format('d-M-Y');

                if ($cnic->isPast()) {
                    $reasons[] = "CNIC Expired ({$formatted})";
                } elseif ($cnic->between($today, $nextMonthEnd)) {
                    $reasons[] = "CNIC Expiring Soon ({$formatted})";
                }
            }

            // LICENSE
            if ($driver->license_expiry_date) {
                $lic = Carbon::parse($driver->license_expiry_date);
                $formatted = $lic->format('d-M-Y');

                if ($lic->isPast()) {
                    $reasons[] = "License Expired ({$formatted})";
                } elseif ($lic->between($today, $nextMonthEnd)) {
                    $reasons[] = "License Expiring Soon ({$formatted})";
                }
            }

            return [
                'id' => $driver->id,
                'serial_no' => $driver->serial_no,
                'name' => $driver->full_name,
                'status' => $driver->driverStatus ? $driver->driverStatus->name : 'N/A',
                'reason' => implode(', ', $reasons),
            ];
        });
    }

    /** Pagination resets */
    public function updatingSearch() { $this->resetPage(); }
    public function filterDrivers() { $this->resetPage(); }
    public function clearFilters()
    {
        $this->filterReason = '';
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Clean Render Function
     */
    public function render()
    {
        $this->reasonList = $this->getDynamicReasons();

        $drivers = $this->loadExpiredDriversQuery()->paginate(10);

        $expiredDrivers = $this->formatDrivers($drivers);

        return view('livewire.expired-drivers-table', [
            'expiredDrivers' => $expiredDrivers,
            'reasonList'     => $this->reasonList,
        ]);
    }
}
