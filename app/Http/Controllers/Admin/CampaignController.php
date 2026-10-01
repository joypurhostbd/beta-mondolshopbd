<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignStoreRequest;
use App\Http\Requests\Admin\CampaignUpdateRequest;
use App\Models\Campaign;
use App\Models\CampaignReview;
use App\Models\Product;
use App\Services\CampaignService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService
    ) {
        $this->middleware('permission:campaign-list|campaign-create|campaign-edit|campaign-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:campaign-create', ['only' => ['create', 'store', 'clone']]);
        $this->middleware('permission:campaign-edit', ['only' => ['edit', 'update', 'active', 'inactive', 'toggleStatus']]);
        $this->middleware('permission:campaign-delete', ['only' => ['destroy', 'imgdestroy']]);
    }

    /**
     * Display a listing of campaigns.
     */
    public function index(Request $request): View
    {
        $show_data = Campaign::with(['product', 'images'])
            ->withCount('images')
            ->orderBy('id', 'DESC')
            ->get();

        $metrics = [
            'total' => Campaign::count(),
            'active' => Campaign::where('status', 1)->count(),
            'inactive' => Campaign::where('status', 0)->count(),
            'reviews_count' => CampaignReview::count(),
        ];

        return view('backEnd.campaign.index', compact('show_data', 'metrics'));
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create(): View
    {
        $products = Product::where('status', 1)
            ->select('id', 'name', 'product_code', 'new_price', 'old_price', 'stock', 'status')
            ->orderBy('name', 'ASC')
            ->get();

        return view('backEnd.campaign.create', compact('products'));
    }

    /**
     * Store a newly created campaign.
     */
    public function store(CampaignStoreRequest $request): RedirectResponse
    {
        $this->campaignService->storeCampaign($request->validated(), $request);

        Toastr::success('Success', 'Landing page created successfully');
        return redirect()->route('campaign.index');
    }

    /**
     * Display campaign details.
     */
    public function show(int $id): View|RedirectResponse
    {
        $campaign = Campaign::with(['product', 'images'])->find($id);
        if (!$campaign) {
            Toastr::error('Error', 'Landing page not found');
            return redirect()->route('campaign.index');
        }

        return view('backEnd.campaign.show', compact('campaign'));
    }

    /**
     * Show the form for editing an existing campaign.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $edit_data = Campaign::with('images')->find($id);

        if (!$edit_data) {
            Toastr::error('Error', 'Landing page not found');
            return redirect()->route('campaign.index');
        }

        $products = Product::where('status', 1)
            ->select('id', 'name', 'product_code', 'new_price', 'old_price', 'stock', 'status')
            ->orderBy('name', 'ASC')
            ->get();

        return view('backEnd.campaign.edit', compact('edit_data', 'products'));
    }

    /**
     * Update an existing campaign.
     */
    public function update(CampaignUpdateRequest $request): RedirectResponse
    {
        $campaign = Campaign::find($request->hidden_id);

        if (!$campaign) {
            Toastr::error('Error', 'Landing page not found');
            return redirect()->route('campaign.index');
        }

        $this->campaignService->updateCampaign($campaign, $request->validated(), $request);

        Toastr::success('Success', 'Landing page updated successfully');
        return redirect()->route('campaign.index');
    }

    /**
     * Toggle campaign active/inactive status via AJAX or standard POST.
     */
    public function toggleStatus(Request $request): JsonResponse|RedirectResponse
    {
        $id = $request->input('id') ?? $request->input('hidden_id');
        $campaign = Campaign::find($id);

        if (!$campaign) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
            }
            Toastr::error('Error', 'Campaign not found');
            return redirect()->back();
        }

        $this->campaignService->toggleStatus($campaign);
        $statusText = $campaign->status == 1 ? 'Activated' : 'Deactivated';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $campaign->status,
                'message' => "Campaign {$statusText} successfully",
            ]);
        }

        Toastr::success('Success', "Landing page {$statusText} successfully");
        return redirect()->back();
    }

    /**
     * Legacy active action.
     */
    public function active(Request $request): RedirectResponse
    {
        $campaign = Campaign::find($request->hidden_id);
        if ($campaign && $campaign->status != 1) {
            $this->campaignService->toggleStatus($campaign);
            Toastr::success('Success', 'Landing page activated successfully');
        }
        return redirect()->back();
    }

    /**
     * Legacy inactive action.
     */
    public function inactive(Request $request): RedirectResponse
    {
        $campaign = Campaign::find($request->hidden_id);
        if ($campaign && $campaign->status != 0) {
            $this->campaignService->toggleStatus($campaign);
            Toastr::success('Success', 'Landing page marked inactive');
        }
        return redirect()->back();
    }

    /**
     * Clone / Duplicate a campaign.
     */
    public function clone(int $id): RedirectResponse
    {
        $campaign = Campaign::with('images')->find($id);
        if (!$campaign) {
            Toastr::error('Error', 'Campaign not found');
            return redirect()->route('campaign.index');
        }

        $cloned = $this->campaignService->duplicateCampaign($campaign);

        Toastr::success('Success', "Campaign '{$campaign->name}' duplicated as draft!");
        return redirect()->route('campaign.edit', $cloned->id);
    }

    /**
     * Destroy a campaign.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $campaign = Campaign::with('images')->find($request->hidden_id);
        if ($campaign) {
            $this->campaignService->deleteCampaign($campaign);
            Toastr::success('Success', 'Landing page deleted successfully');
        }
        return redirect()->back();
    }

    /**
     * Delete a single review proof image.
     */
    public function imgdestroy(Request $request): RedirectResponse
    {
        $deleted = $this->campaignService->deleteReviewImage((int) $request->id);
        if ($deleted) {
            Toastr::success('Success', 'Review image deleted successfully');
        }
        return redirect()->back();
    }
}
