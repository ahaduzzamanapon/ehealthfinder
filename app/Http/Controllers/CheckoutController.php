<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /** Show the checkout / buy page for a medicine */
    public function buy($id, $slug)
    {
        $brand = Brand::with('generic')->findOrFail($id);
        return view('checkout.buy', compact('brand'));
    }

    /** Handle order form submission */
    public function place(Request $request)
    {
        $request->validate([
            'full_name'      => 'required|string|max:120',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:300',
            'city'           => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'medicine_id'    => 'required|integer',
            'quantity'       => 'required|integer|min:1|max:99',
            'payment_method' => 'required|string',
        ]);

        // Parse unit price from medicine price string (e.g. "৳ 12.50" → 12.50)
        preg_match('/(\d+(\.\d+)?)/', str_replace(',', '', $request->medicine_price ?? '0'), $m);
        $unitPrice = isset($m[1]) ? (float)$m[1] : 0;
        $qty       = (int)$request->quantity;
        $total     = number_format(($unitPrice * $qty) + 60, 2);

        // Store order in session (demo — replace with DB insert for production)
        session([
            'order' => [
                'id'         => strtoupper(Str::random(8)),
                'name'       => $request->full_name,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'address'    => $request->address,
                'city'       => $request->city,
                'district'   => $request->district,
                'medicine'   => $request->medicine_name,
                'qty'        => $qty,
                'unit_price' => number_format($unitPrice, 2),
                'total'      => $total,
                'payment'    => $request->payment_method,
                'note'       => $request->note,
            ],
        ]);

        return redirect()->route('checkout.success');
    }

    /** Show the order success page */
    public function success()
    {
        if (!session('order')) {
            return redirect()->route('medicines.index');
        }
        return view('checkout.success');
    }
}
