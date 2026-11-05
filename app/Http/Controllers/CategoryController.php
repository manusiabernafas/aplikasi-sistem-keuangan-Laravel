<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\CategoryType;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $activeType = $request->string('type')->toString() ?: 'INCOME';
        $categoryTypes = CategoryType::orderBy('id')->get();
        $tree = $this->categoryService->treeByType($activeType);

        return view('categories.index', compact('categoryTypes', 'tree', 'activeType'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Category::class);

        $categoryTypes = CategoryType::orderBy('id')->get();
        $selectedType = $request->integer('category_type_id') ?: $categoryTypes->first()?->id;
        $parents = Category::where('category_type_id', $selectedType)->topLevel()->orderBy('name')->get();

        return view('categories.create', compact('categoryTypes', 'parents', 'selectedType'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            $this->categoryService->create($request->validated());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        $categoryTypes = CategoryType::orderBy('id')->get();
        $parents = Category::where('category_type_id', $category->category_type_id)
            ->topLevel()
            ->whereKeyNot($category->id)
            ->orderBy('name')
            ->get();

        return view('categories.edit', compact('category', 'categoryTypes', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $this->categoryService->update($category, $request->validated());
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->categoryService->delete($category);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
