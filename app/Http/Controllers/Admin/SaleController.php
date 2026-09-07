<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;


class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'products';
        if ($request->ajax()) {
            // Get current date for comparison
            $currentDate = now();

            $products = Product::latest()->whereHas('purchase', function ($query) use ($currentDate) {
                $query->where('expiry_date', '>', $currentDate);
            });

            return DataTables::of($products)
                ->addColumn('product', function ($product) {
                    $image = '';
                    if (!empty($product->purchase)) {
                        $image = null;
                        if (!empty($product->purchase->image)) {
                            $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="' . asset("storage/purchases/" . $product->purchase->image) . '" alt="image">
                            </span>';
                        }
                        return $product->purchase->product . ' ' . $image;
                    }
                })
                ->addColumn('category', function ($product) {
                    $category = null;
                    if (!empty($product->purchase->category)) {
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price', function ($product) {
                    return settings('app_currency', 'BDT') . ' ' . $product->price;
                })
                ->addColumn('quantity', function ($product) {
                    if (!empty($product->purchase)) {
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('expiry_date', function ($product) {
                    if (!empty($product->purchase)) {
                        return date_format(date_create($product->purchase->expiry_date), 'd M, Y');
                    }
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="' . route("products.edit", $row->id) . '" class="editbtn"><button class="btn btn-info"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="' . $row->id . '" data-route="' . route('products.destroy', $row->id) . '" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn . ' ' . $deletebtn;
                    return $btn;
                })
                ->rawColumns(['product', 'action'])
                ->make(true);
        }
        return view('admin.products.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        $products = Product::get();
        return view('admin.sales.create',compact(
            'title','products'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'quantities' => 'required|array',
        ]);

        $salesData = [];
        $totalAmount = 0;
        $user = auth()->user(); // Get the logged-in user

        foreach ($request->products as $index => $productId) {
            $product = Product::find($productId);
            $quantity = $request->quantities[$index];

            // Fetch the purchase record associated with the product
            $purchase = Purchase::find($product->purchase_id);

            // Check if the purchase record exists and if there is enough stock
            if (!$purchase || $purchase->quantity < $quantity) {
                return redirect()->back()->withErrors(['error' => 'Insufficient stock for product ' . $product->name . '.']);
            }

            $totalPrice = $product->price * $quantity;

            // Create the sale entry
            $sale = Sale::create([
                'product_id'   => $product->id,
                'quantity'     => $quantity,
                'total_price'  => $totalPrice,
                'sold_by'      => $user->name, // Store the seller's username
            ]);

            // Reduce the stock in the purchase record
            $purchase->update([
                'quantity' => $purchase->quantity - $quantity,
            ]);

            $salesData[] = $sale;
            $totalAmount += $totalPrice;
        }

        $pdfName = $this->generateReceipt($salesData, $totalAmount);
        session()->flash('receipt_name', $pdfName);

        return redirect()->route('sales.index')->with('success', 'Sale created successfully. Receipt generated.');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        $products = Product::get();
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request, [
            'product' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $sold_product = Product::find($request->product);
        $purchased_item = Purchase::find($sold_product->purchase->id);

        // Calculate new quantity
        $new_quantity = $purchased_item->quantity + $sale->quantity - $request->quantity;

        $notification = '';

        if ($new_quantity >= 0) {
            // Update purchase quantity
            $purchased_item->update(['quantity' => $new_quantity]);

            // Calculate total price
            $total_price = $request->quantity * $sold_product->price;

            // Update sale record
            $sale->update([
                'product_id' => $request->product,
                'quantity' => $request->quantity,
                'total_price' => $total_price,
            ]);

            $notification = notify("Product has been updated");

            // Notify if stock is running low
            if ($new_quantity <= 1 && $new_quantity != 0) {
                event(new PurchaseOutStock($purchased_item));
                $notification .= notify("Product is running out of stock!!!");
            }
        } else {
            $notification = notify("Insufficient stock available.");
        }

        return redirect()->route('sales.index')->with($notification);
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request, [
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        // Retrieve sales data based on date range
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), [$request->from_date, $request->to_date])->with(['product.purchase'])->get();

        // Calculate totals for summary
        $totalQuantity = $sales->sum('quantity');
        $totalCost = $sales->sum(function ($sale) {
            return $sale->product->purchase ? $sale->product->purchase->cost_price * $sale->quantity : 0;
        });
        $totalSoldPrice = $sales->sum('total_price');
        $totalProfit = $totalSoldPrice - $totalCost;

        return view('admin.sales.reports', compact('sales', 'totalQuantity', 'totalCost', 'totalSoldPrice', 'totalProfit'));
    }

    public function generatePDFReport(Request $request)
    {
        // Validate the request
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        // Retrieve the sales data based on the date range
        $sales = Sale::whereBetween('created_at', [$request->from_date, $request->to_date])
            ->with(['product.purchase'])
            ->get();

        // Calculate totals for summary
        $totalProducts = $sales->sum('quantity');
        $totalCost = $sales->sum(function ($sale) {
            return $sale->product->purchase ? $sale->product->purchase->cost_price * $sale->quantity : 0;
        });
        $totalSoldPrice = $sales->sum('total_price');
        $totalProfit = $totalSoldPrice - $totalCost;

        // Prepare the view for PDF
        $view = view('admin.sales.pdf_report', compact('sales', 'totalProducts', 'totalCost', 'totalSoldPrice', 'totalProfit'))->render();

        // Initialize Dompdf options
        $options = new Options();
        $options->set('defaultFont', 'Arial'); // Set the default font
        $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parser
        $options->set('isRemoteEnabled', true); // Enable loading of external resources (e.g., images)

        // Create a new Dompdf instance with options
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($view);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape'); // Choose 'landscape' or 'portrait'

        // Render the PDF
        $dompdf->render();
        $fromDate = date('Y-m-d', strtotime($request->from_date));
        $toDate = date('Y-m-d', strtotime($request->to_date));
        $fileName = "sales_report_{$fromDate}_to_{$toDate}.pdf";
        // Output the generated PDF to the browser
        return $dompdf->stream($fileName, ['Attachment' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Sale::findOrFail($request->id)->delete();
    }

    protected function generateReceipt($salesData, $totalAmount)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('admin.sales.receipt', compact('salesData', 'totalAmount'))->render());

        // (Optional) Setup paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Save the generated PDF temporarily before downloading
        $pdfOutput = $dompdf->output();
        $pdfName = 'receipt_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        $path = storage_path("app/public/receipts/{$pdfName}");
        file_put_contents($path, $pdfOutput);

        return $pdfName; // Return just the name of the PDF
    }

    public function downloadReceipt($pdfName)
    {
        $path = storage_path("app/public/receipts/{$pdfName}");
        return response()->download($path);
    }


}
