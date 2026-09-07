@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Create Order</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
		<li class="breadcrumb-item active">Create Order</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Create Sale -->
                <form method="POST" action="{{ route('sales.store') }}">
                    @csrf
                    <div id="product-container">
                        @foreach (old('products', ['']) as $index => $oldProduct)
                            <div class="row form-row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Product <span class="text-danger">*</span></label>
                                        <select class="select2 form-select form-control" name="products[]">
                                            <option disabled selected>Select Product</option>
                                            @foreach ($products as $product)
                                                @if (!empty($product->purchase) && !($product->purchase->quantity <= 0))
                                                    <option value="{{ $product->id }}" {{ $oldProduct == $product->id ? 'selected' : '' }}>
                                                        {{ $product->purchase->product }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('products.'.$index)
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" value="{{ old('quantities.'.$index, 1) }}" min="1" class="form-control" name="quantities[]">
                                        @error('quantities.'.$index)
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-success" onclick="addProductRow()">Add More</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Submit</button>
                </form>

                <!-- /Create Sale -->
			</div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script>
    function addProductRow() {
        var newRow = `
            <div class="row form-row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Product <span class="text-danger">*</span></label>
                        <select class="select2 form-select form-control" name="products[]">
                            <option disabled selected>Select Product</option>
                            @foreach ($products as $product)
                                @if (!empty($product->purchase) && !($product->purchase->quantity <= 0))
                                    <option value="{{ $product->id }}">{{ $product->purchase->product }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" value="1" class="form-control" name="quantities[]">
                    </div>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger" onclick="removeProductRow(this)">Remove</button>
                </div>
            </div>
        `;
        document.getElementById('product-container').insertAdjacentHTML('beforeend', newRow);
    }

    function removeProductRow(button) {
        button.closest('.row').remove();
    }
</script>
@endpush
