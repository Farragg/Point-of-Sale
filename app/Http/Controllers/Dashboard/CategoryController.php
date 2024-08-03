<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read_categories'])->only('index');
        $this->middleware(['permission:create_categories'])->only('create');
        $this->middleware(['permission:update_categories'])->only('update');
        $this->middleware(['permission:delete_categories'])->only('destroy');
    }

    public function index(Request $request)
    {
        $categories = Category::when($request->search, function ($q) use ($request){
            return $q->whereTranslationLike('name', '%'. $request->search.'%' );
        })->latest()->paginate(5);
        return view ('dashboard.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view ('dashboard.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules=[];

        foreach (config('translatable.locales') as $locale){
            // ar.* ar.anything
            $rules += [$locale . '.name' => ['required', Rule::unique('category_translations', 'name')]];
        }
        $request->validate($rules);

        Category::create($request->all());

        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.categories.index');
    }


    public function edit(Category $category)
    {
        return view('dashboard.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $rules=[];

        foreach (config('translatable.locales') as $locale){
            // ar.* ar.anything
            $rules += [$locale . '.name' => ['required', Rule::unique('category_translations', 'name')
                ->ignore($category->id, 'category_id')]];
        }
        $request->validate($rules);

        $category->update($request->all());
        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.categories.index');
    }


    public function destroy(Category $category)
    {
        $category->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.categories.index');
    }
}
