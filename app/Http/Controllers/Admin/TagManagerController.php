<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TagManagerStoreRequest;
use App\Http\Requests\Admin\TagManagerUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Setting\Application\Services\TagManagerService;
use Modules\Setting\Application\ViewModels\TagManagerIndexViewModel;
use Toastr;

use Modules\Setting\Domain\Enums\TrackingEventEnum;

class TagManagerController extends Controller
{
    public function __construct(
        private readonly TagManagerService $service
    ) {
        $this->middleware('permission:setting-list|setting-create|setting-edit|setting-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:setting-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setting-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:setting-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $viewModel = new TagManagerIndexViewModel($this->service->getAll());

        return view('backEnd.tagmanager.index', $viewModel->toViewData());
    }

    public function create(): View
    {
        $standardEvents = TrackingEventEnum::cases();

        return view('backEnd.tagmanager.create', compact('standardEvents'));
    }

    public function show($id): View
    {
        $edit_data = $this->service->getById((int) $id);
        $standardEvents = TrackingEventEnum::cases();

        return view('backEnd.tagmanager.edit', compact('edit_data', 'standardEvents'));
    }

    public function store(TagManagerStoreRequest $request): RedirectResponse
    {
        $this->service->create($request->toDTO());

        Toastr::success('Success', 'Tag Manager created successfully');

        return redirect()->route('tagmanagers.index');
    }

    public function edit($id): View
    {
        $edit_data = $this->service->getById((int) $id);
        $standardEvents = TrackingEventEnum::cases();

        return view('backEnd.tagmanager.edit', compact('edit_data', 'standardEvents'));
    }

    public function update(TagManagerUpdateRequest $request): RedirectResponse
    {
        $tagId = (int) ($request->id ?? $request->hidden_id);
        $this->service->update($tagId, $request->toDTO());

        Toastr::success('Success', 'Tag Manager updated successfully');

        return redirect()->route('tagmanagers.index');
    }

    public function inactive(Request $request): RedirectResponse
    {
        $request->validate(['hidden_id' => 'required|integer|exists:google_tag_managers,id']);

        $this->service->setStatus((int) $request->hidden_id, 0);

        Toastr::success('Success', 'Tag Manager deactivated successfully');

        return redirect()->back();
    }

    public function active(Request $request): RedirectResponse
    {
        $request->validate(['hidden_id' => 'required|integer|exists:google_tag_managers,id']);

        $this->service->setStatus((int) $request->hidden_id, 1);

        Toastr::success('Success', 'Tag Manager activated successfully');

        return redirect()->back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['hidden_id' => 'required|integer|exists:google_tag_managers,id']);

        $this->service->delete((int) $request->hidden_id);

        Toastr::success('Success', 'Tag Manager deleted successfully');

        return redirect()->back();
    }
}

