@extends('layouts.admin')

@section('title', 'Dashboard')

@livewireStyles

<style>
    .content-wrapper {
        overflow-y: auto;
        height: calc(100vh - 60px);
    }
    
    .page-content {
        min-height: 100%;
        overflow-y: auto;
    }
    
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .dataTables_wrapper {
        overflow-x: auto;
    }
    
    .dataTables_paginate {
        margin-top: 10px;
    }
    
    .table td, .table th {
        white-space: nowrap;
        word-wrap: break-word;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .table td:hover, .table th:hover {
        white-space: normal;
        word-wrap: break-word;
        max-width: none;
    }
    
    .navbar-collapse {
        display: block !important;
    }
    
    .navbar-nav {
        flex-direction: row;
    }
</style>

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Home</span> - Dashboard</h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <!-- Main Content -->
    <div class="content" style="overflow-y: auto; max-height: calc(100vh - 200px);">
        <!-- Summary Cards -->
        <div class="row">
            <!-- Card 1 -->
            <div class="col-lg-4">
                <div class="card bg-teal text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0">3,450</h3>
                            <span class="badge badge-dark badge-pill align-self-center ml-auto">+53.6%</span>
                        </div>
                        <div>Total Vehicles</div>
                        <div class="font-size-sm opacity-75">489 avg</div>
                    </div>
                    <div class="container-fluid page-header-light py-3"><div id="members-online"></div></div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-lg-4">
                <div class="card bg-pink text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0">49.4%</h3>
                            <div class="list-icons ml-auto">
                                <a href="#" class="list-icons-item"><i class="icon-cog3"></i></a>
                            </div>
                        </div>
                        <div>Total Drivers</div>
                        <div class="font-size-sm opacity-75">34.6% avg</div>
                    </div>
                    <div id="server-load"></div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-lg-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0">$18,390</h3>
                            <div class="list-icons ml-auto">
                                <a class="list-icons-item" data-action="reload"></a>
                            </div>
                        </div>
                        <div>This week maintenance</div>
                        <div class="font-size-sm opacity-75">$37,578 avg</div>
                    </div>
                    <div id="today-revenue"></div>
                </div>
            </div>
        </div>

        <!-- Live Data Tables -->
        <div class="row mt-4">
            <!-- Drivers Table -->
            <div class="col-lg-6">
                @livewire('expired-drivers-table')
            </div>

            <!-- Vehicles Table -->
            <div class="col-lg-6">
                @livewire('expired-vehicles-table')
            </div>
        </div>
    </div>
    <!-- /Main Content -->
</div>

@livewireScripts

       <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>

<script>
    let dataTablesInitialized = false;

    function initializeDataTables() {
        // Only initialize if not already initialized
        if (dataTablesInitialized) {
            return;
        }

        // Check if tables exist
        if ($('#drivers-table').length === 0 || $('#vehicles-table').length === 0) {
            return;
        }

        // Initialize DataTables for drivers table
        $('#drivers-table').DataTable({
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            "responsive": true,
            "autoWidth": false,
            "order": [[0, "asc"]],
            "paging": true,
            "searching": true,
            "info": true,
            "columnDefs": [
                { "width": "10%", "targets": 0 },
                { "width": "25%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "30%", "targets": 3 },
                { "width": "20%", "targets": 4 }
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });

        // Initialize DataTables for vehicles table
        $('#vehicles-table').DataTable({
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            "responsive": true,
            "autoWidth": false,
            "order": [[0, "asc"]],
            "paging": true,
            "searching": true,
            "info": true,
            "columnDefs": [
                { "width": "10%", "targets": 0 },
                { "width": "20%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "15%", "targets": 3 },
                { "width": "15%", "targets": 4 },
                { "width": "15%", "targets": 5 },
                { "width": "10%", "targets": 6 }
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });

        dataTablesInitialized = true;
    }

    $(document).ready(function () {
        // Initialize DataTables after a short delay to ensure Livewire components are loaded
        // setTimeout(function() {
        //     initializeDataTables();
        // }, 2000);
    });

    // Only initialize once when Livewire loads
    document.addEventListener('livewire:load', function () {
        // setTimeout(function() {
        //     initializeDataTables();
        // }, 1000);
    });
</script>
@endsection
