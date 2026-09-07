@extends('admin.layouts.app')


@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Sale</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Sale</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Edit Sale -->
                <form method="POST" action="{{ route('sales.update', $sale) }}">
                    @csrf
                    @method("PUT")
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="form-group">
                                <div style="font-size: 1.5rem; font-weight: bold;">
                                    {{ $sale->product->purchase->product }}
                                </div>
                                <input type="hidden" name="product" value="{{ $sale->product->id }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" class="form-control edit_quantity" value="{{ $sale->quantity ?? '1' }}" name="quantity">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Save Changes</button>
                </form>

                <!--/ Edit Sale -->
			</div>
		</div>
	</div>
</div>
@endsection


@push('page-js')

@endpush
