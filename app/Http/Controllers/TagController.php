<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class TagController extends Controller
{
    public function __construct(private readonly TagService $tagService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Tag::class);

        $tags = Tag::withCount('transactions')->orderBy('name')->paginate(20);

        return view('tags.index', compact('tags'));
    }

    public function create(): View
    {
        $this->authorize('create', Tag::class);

        return view('tags.create');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->tagService->create($request->validated());

        return redirect()->route('tags.index')->with('success', 'Tag berhasil ditambahkan.');
    }

    public function edit(Tag $tag): View
    {
        $this->authorize('update', $tag);

        return view('tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->tagService->update($tag, $request->validated());

        return redirect()->route('tags.index')->with('success', 'Tag berhasil diperbarui.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        try {
            $this->tagService->delete($tag);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('tags.index')->with('success', 'Tag berhasil dihapus.');
    }
}
