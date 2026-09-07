@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Sales Reports</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Generate Sales Reports</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-success float-right mt-2">Generate Report</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">

        @isset($sales)
        <!-- Download Report Form -->
        <form method="GET" action="{{ route('sales.generatePDFReport') }}" class="d-inline mb-3">
            <input type="hidden" name="from_date" value="{{ request()->get('from_date') }}">
            <input type="hidden" name="to_date" value="{{ request()->get('to_date') }}">
            <button type="submit" class="btn btn-primary">Download Report</button>
        </form>
        <!-- Sales Report -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sales-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Purchase Cost</th>
                                <th>Sold Price (Unit)</th>
                                <th>Profit (Unit)</th>
                                <th>Sold By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                @if ($sale->product->purchase)
                                <tr>
                                    <td>
                                        {{$sale->product->purchase->product}}
                                        @if (!empty($sale->product->purchase->image))
                                            <span class="avatar avatar-sm mr-2">
                                                <img class="avatar-img" src="{{asset('storage/purchases/'.$sale->product->purchase->image)}}" alt="image">
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $sale->product->purchase->supplier->name }}</td>
                                    <td>{{$sale->quantity}}</td>
                                    <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($sale->total_price, 2) }}</td>
                                    <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format($sale->product->purchase->cost_price, 2) }}</td>
                                    <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format(($sale->total_price / $sale->quantity), 2) }}</td>
                                    <td>{{ AppSettings::get('app_currency', 'BDT') }} {{ number_format(($sale->total_price - ($sale->product->purchase->cost_price * $sale->quantity)), 2) }}</td>
                                    <td>{{$sale->sold_by}}</td>
                                    <td>{{date_format(date_create($sale->created_at), "d M, Y")}}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- / Sales Report -->
        @endisset

    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('sales.report') }}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>From</label>
                                        <input type="date" name="from_date" class="form-control from_date">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>To</label>
                                        <input type="date" name="to_date" class="form-control to_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block submit_report">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Generate Modal -->
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        $('#sales-table').DataTable(); // Removed export buttons
    });
</script>
@endpush
