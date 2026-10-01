<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CodeSnippetStoreRequest;
use App\Http\Requests\Admin\CodeSnippetUpdateRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Modules\Setting\Application\DTOs\CodeSnippetDTO;
use Modules\Setting\Application\Services\CodeSnippetService;
use Throwable;

class CodeSnippetController extends Controller
{
    public function __construct(
        private readonly CodeSnippetService $snippetService
    ) {
        $this->middleware('permission:setting-list|setting-create|setting-edit|setting-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:setting-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setting-edit', ['only' => ['edit', 'update', 'active', 'inactive', 'toggle']]);
        $this->middleware('permission:setting-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $snippets = $this->snippetService->getAll();

        $kpis = [
            'total' => count($snippets),
            'active' => count(array_filter($snippets, fn($s) => $s->status)),
            'inactive' => count(array_filter($snippets, fn($s) => !$s->status)),
            'head' => count(array_filter($snippets, fn($s) => $s->location->value === 'head')),
            'footer' => count(array_filter($snippets, fn($s) => $s->location->value === 'footer')),
        ];

        return view('backEnd.settings.snippets.index', compact('snippets', 'kpis'));
    }

    public function create()
    {
        return view('backEnd.settings.snippets.create');
    }

    public function store(CodeSnippetStoreRequest $request)
    {
        try {
            $data = $request->validated();
            $data['status'] = $request->has('status') ? 1 : 0;
            $dto = CodeSnippetDTO::fromArray($data);

            $this->snippetService->create($dto);

            Toastr::success('Code snippet created successfully', 'Success');
            return redirect()->route('snippets.index');
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Snippet save failed: ' . $e->getMessage(), ['exception' => $e]);
            Toastr::error($e->getMessage(), 'Error');
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(int $id)
    {
        $snippet = $this->snippetService->getById($id);
        if (!$snippet) {
            Toastr::error('Snippet not found', 'Error');
            return redirect()->route('snippets.index');
        }

        return view('backEnd.settings.snippets.edit', compact('snippet'));
    }

    public function update(CodeSnippetUpdateRequest $request)
    {
        try {
            $targetId = (int) ($request->hidden_id ?? $request->id);
            $data = $request->validated();
            $data['status'] = $request->has('status') ? 1 : 0;
            $dto = CodeSnippetDTO::fromArray($data, $targetId);

            $this->snippetService->update($targetId, $dto);

            Toastr::success('Code snippet updated successfully', 'Success');
            return redirect()->route('snippets.index');
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Snippet update failed: ' . $e->getMessage(), ['exception' => $e]);
            Toastr::error($e->getMessage(), 'Error');
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function active(Request $request)
    {
        $id = (int) $request->hidden_id;
        $snippet = $this->snippetService->getById($id);
        if ($snippet && !$snippet->status) {
            $this->snippetService->toggleStatus($id);
        }

        Toastr::success('Snippet activated successfully', 'Success');
        return redirect()->back();
    }

    public function inactive(Request $request)
    {
        $id = (int) $request->hidden_id;
        $snippet = $this->snippetService->getById($id);
        if ($snippet && $snippet->status) {
            $this->snippetService->toggleStatus($id);
        }

        Toastr::success('Snippet deactivated successfully', 'Success');
        return redirect()->back();
    }

    public function toggle(Request $request)
    {
        $id = (int) ($request->id ?? $request->hidden_id);
        $success = $this->snippetService->toggleStatus($id);

        if ($request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => 'Status updated successfully',
            ]);
        }

        Toastr::success('Status updated successfully', 'Success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $id = (int) $request->hidden_id;
        $this->snippetService->delete($id);

        Toastr::success('Code snippet deleted successfully', 'Success');
        return redirect()->back();
    }
}
