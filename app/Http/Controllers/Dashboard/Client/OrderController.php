<?php

namespace App\Http\Controllers\Dashboard\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:create_orders'])->only('create');
        $this->middleware(['permission:update_orders'])->only('update');
        $this->middleware(['permission:delete_orders'])->only('destroy');
    }


    public function create(Client $client)
    {
        $categories = Category::with('products')->get();
        $orders = $client->orders()->with('products')->paginate(5);
        return view('dashboard.clients.orders.create', compact('client','categories', 'orders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'products' => 'required|array',
        ]);

        $this->attach_order($request, $client);

        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.orders.index');
    }


    public function edit(Client $client, Order $order)
    {

        $categories = Category::with('products')->get();
        $orders = $client->orders()->with('products')->paginate(5);
        return view('dashboard.clients.orders.edit', compact('client', 'order', 'categories', 'orders'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client, Order $order)
    {
        $request->validate([
            'products' => 'required|array',
        ]);

        $this->detach_order($order);
        $this->attach_order($request, $client);

        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.orders.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order, Client $client)
    {
        //
    }

    private function attach_order($request, $client)
    {
        //every order has a client order table have a client id
        $order = $client->orders()->create([]);
//        $order = Order::create([]);
        $total_price = 0;

        // attach product [$id][quantity] key for id value for [q]
        $order->products()->attach($request->products);

        foreach ($request->products as $id => $quantity){

            $product= Product::findOrFail($id);
            $total_price += $product->sale_price * $quantity['quantity'];

            $product->update([
                'stock' => $product->stock - $quantity['quantity'],
            ]);
        }

        $order->update([
            'total_price' => $total_price,
        ]);
    }

    private function detach_order($order)
    {
        // destroy his previous order and make a new one
        foreach ($order->products as $product){

            $product->update([
                'stock'=> $product->stock + $product->pivot->quantity,
            ]);
        }

        $order->delete();
    }
}
