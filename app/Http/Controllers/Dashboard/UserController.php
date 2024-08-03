<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager as Image;

class UserController extends Controller
{
    // constructor to handling permissions redirect to their functions to prevent fake urls
    public function __construct()
    {
        $this->middleware(['permission:read_users'])->only('index');
        $this->middleware(['permission:create_users'])->only('create');
        $this->middleware(['permission:update_users'])->only('update');
        $this->middleware(['permission:delete_users'])->only('destroy');
    }

    public function index(Request $request)
    {
//        if($request->search){
//            //regular method
//            $users= User::where('first_name', 'like', '%' . $request->search . '%')
//                ->orwhere('last_name', 'like', '%'. $request->search . '%')
//                ->get();
//        }
//        else{
//            $users = User::whereHasRole('admin')->get();
//        }
        //professional method
        /** use request bec func only sees local scope
         * request only in index func not in function ($query)
         * so u must use it
         * first where is to avoid to search super_admin
         *bec without it willbe ORwhen *
         */
        $users = User::whereHasRole('admin')->where(function ($q) use ($request) {

            return $q->when($request->search, function ($query) use ($request) {
                return $query->where('first_name', 'like', '%' . $request->search . '%')
                    ->orwhere('last_name', 'like', '%' . $request->search . '%');
            });
        })->latest()->paginate(5);
        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users',
            'image' => 'image',
            'password' => 'required|confirmed',
            'permissions' => 'required|min:1'
        ]);

        $request_data = $request->except(['password', 'password_confirmation', 'permissions', 'image']);
        $request_data['password'] = bcrypt($request->password);

        if ($request->image) {
            //store photo
            Image::gd()->read($request->image)->resize(300, 300)
                ->save(public_path('uploads/user_images/' . $request->image->hashName())); // to prevent duplicate images

            $request_data['image'] = $request->image->hashName();
        }

        $user = User::create(
            $request_data
        );
        $user->addRole('admin');
        $user->syncPermissions($request->permissions);

        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('dashboard.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', Rule::unique('users')->ignore($user->id)],
            'image' => 'image',
            'permissions' => 'required|min:1'
        ]);
        $request_data = $request->except(['permissions', 'image']);

        if ($request->image) {

            if ($user->image != 'default.png') {

                Storage::disk('public_uploads')->delete('/user_images/' . $user->image);
            }
            // if the user have his own photo delete it then insert the new one
            Image::gd()->read($request->image)->resize(300, 300)
                ->save(public_path('uploads/user_images/' . $request->image->hashName())); // to prevent duplicate images

            $request_data['image'] = $request->image->hashName();
        }

        $user->update($request_data);
        $user->syncPermissions($request->permissions);

        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.users.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(user $user)
    {
        if ($user->image != 'default.png') {
            Storage::disk('public_uploads')->delete('/user_images/' . $user->image);
        }

        $user->delete();

        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.users.index');
    }
}
