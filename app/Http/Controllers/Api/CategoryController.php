<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        return Category::all();
    }

    public function store(CategoryRequest $request)
    {
        return Category::create($request->all());
    }

    public function show(Category $category) //route model binding
    {
        return $category;
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete ();
        return response ()->noContent (); // 204 - no content
    }
}
