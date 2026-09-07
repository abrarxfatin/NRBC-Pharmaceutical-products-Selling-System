@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Sales</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ul>
</div>

@if(session('receipt_name'))
<div class="alert alert-success alert-dismissible fade show" role="alert" id="receipt-alert">
    <p>Sale created successfully. Receipt generated.</p>
    <a href="{{ route('sales.receipt.download', session('receipt_name')) }}" class="btn btn-primary">Download Receipt</a>
    <button type="button" class="close" aria-label="Close" onclick="closeAlert()">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@can('create-sale')
<div class="col-sm-5 col">
    <a href="{{route('sales.create')}}" class="btn btn-success float-right mt-2">Add Sale</a>
</div>
@endcan
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Sales -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sales-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Sold By</th>
                                <th>Date</th>
                                <th class="action-btn">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- / Sales -->
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('sales.index')}}",
            columns: [
                {data: 'product', name: 'product', searchable: true},
                {data: 'quantity', name: 'quantity', searchable: true},
                {data: 'total_price', name: 'total_price', searchable: true},
                {data: 'sold_by', name: 'sold_by', searchable: true},
                {data: 'date', name: 'date', searchable: true},
                {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
        });

        // Manually close alert after 30 seconds (30000 milliseconds)
        setTimeout(function() {
            $('#receipt-alert').fadeOut('slow', function() {
                $(this).alert('close'); // Fully remove it from the DOM after fadeout
            });
        }, 600000); // 60 seconds
    });

    // Manually close the alert when the close button is clicked
    function closeAlert() {
        $('#receipt-alert').fadeOut('slow', function() {
            $(this).alert('close'); // Fully remove it from the DOM
        });
    }
</script>
@endpush
