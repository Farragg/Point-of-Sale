<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read_clients'])->only('index');
        $this->middleware(['permission:create_clients'])->only('create');
        $this->middleware(['permission:update_clients'])->only('update');
        $this->middleware(['permission:delete_clients'])->only('destroy');
    }

    public function index(Request $request)
    {
        $clients= Client::all();
        $clients = Client::when($request->search, function ($q) use ($request){
            return $q->where('name' , '%'. $request->search . '%')
                ->orwhere('phone' , '%'. $request->search . '%')
                ->orwhere('address' , '%'. $request->search . '%');
        })->latest()->paginate(5);
        return view('dashboard.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' =>  'required',
            'phone' =>  'required|array|min:1',
            'phone.0' => 'required',
            'address' =>  'required',
        ]);

        $request_data= $request->all();
        $request_data['phone'] = array_filter($request->phone);

        Client::create($request_data);
        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.clients.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('dashboard.clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' =>  'required',
            'phone' =>  'required|array|min:1',
            'phone.0' => 'required',
            'address' =>  'required',
        ]);

        $request_data= $request->all();
        $request_data['phone'] = array_filter($request->phone);

        $client->update($request_data);
        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.clients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.clients.index');
    }
}
