<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignReview;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CampaignService
{
    private string $uploadDir;

    public function __construct()
    {
        $this->uploadDir = public_path('uploads/campaign');
        if (!File::isDirectory($this->uploadDir)) {
            File::makeDirectory($this->uploadDir, 0755, true, true);
        }
    }

    /**
     * Store a new campaign landing page.
     */
    public function storeCampaign(array $data, Request $request): Campaign
    {
        return DB::transaction(function () use ($data, $request) {
            $campaign = new Campaign();
            $campaign->name = $data['name'];
            $campaign->offer_title = $data['offer_title'] ?? null;
            $campaign->slug = $this->generateUniqueSlug($data['slug'] ?? $data['name']);
            $campaign->product_id = (int) $data['product_id'];
            $campaign->video_url = $data['video_url'] ?? null;
            $campaign->start_date = !empty($data['start_date']) ? $data['start_date'] : now();
            $campaign->end_date = !empty($data['end_date']) ? $data['end_date'] : null;
            $campaign->special_price = !empty($data['special_price']) ? (float) $data['special_price'] : null;
            $campaign->free_shipping = !empty($data['free_shipping']) ? 1 : 0;
            $campaign->short_description = $data['short_description'] ?? '';
            $campaign->description = $data['description'] ?? '';
            $campaign->review = $data['review'] ?? 'সম্মানিত কাস্টমারদের রিভিউ';
            $campaign->status = (!empty($data['status']) && ($data['status'] == 1 || $data['status'] === 'active')) ? 1 : 0;
            $campaign->meta_title = $data['meta_title'] ?? null;
            $campaign->meta_description = $data['meta_description'] ?? null;
            $campaign->meta_keywords = $data['meta_keywords'] ?? null;

            // Upload banner images if present
            if ($request->hasFile('image_one')) {
                $campaign->image_one = $this->uploadFile($request->file('image_one'), 'hero');
            }
            if ($request->hasFile('image_two')) {
                $campaign->image_two = $this->uploadFile($request->file('image_two'), 'feat1');
            }
            if ($request->hasFile('image_three')) {
                $campaign->image_three = $this->uploadFile($request->file('image_three'), 'feat2');
            }

            $campaign->save();

            // Associate with Product
            if (!empty($campaign->product_id)) {
                Product::where('id', $campaign->product_id)->update(['campaign_id' => $campaign->id]);
            }

            // Upload multiple customer proof review screenshots
            if ($request->hasFile('image')) {
                $this->saveReviewImages($campaign->id, (array) $request->file('image'));
            }

            return $campaign;
        });
    }

    /**
     * Update an existing campaign landing page.
     */
    public function updateCampaign(Campaign $campaign, array $data, Request $request): Campaign
    {
        return DB::transaction(function () use ($campaign, $data, $request) {
            $campaign->name = $data['name'];
            $campaign->offer_title = $data['offer_title'] ?? null;
            $campaign->slug = $this->generateUniqueSlug($data['slug'] ?? $data['name'], $campaign->id);
            $campaign->product_id = (int) $data['product_id'];
            $campaign->video_url = $data['video_url'] ?? null;
            $campaign->start_date = !empty($data['start_date']) ? $data['start_date'] : $campaign->start_date;
            $campaign->end_date = !empty($data['end_date']) ? $data['end_date'] : null;
            $campaign->special_price = !empty($data['special_price']) ? (float) $data['special_price'] : null;
            $campaign->free_shipping = !empty($data['free_shipping']) ? 1 : 0;
            $campaign->short_description = $data['short_description'] ?? '';
            $campaign->description = $data['description'] ?? '';
            $campaign->review = $data['review'] ?? $campaign->review;
            $campaign->status = (!empty($data['status']) && ($data['status'] == 1 || $data['status'] === 'active')) ? 1 : 0;
            $campaign->meta_title = $data['meta_title'] ?? null;
            $campaign->meta_description = $data['meta_description'] ?? null;
            $campaign->meta_keywords = $data['meta_keywords'] ?? null;

            // Handle replacement of image_one
            if ($request->hasFile('image_one')) {
                $this->deleteFile($campaign->image_one);
                $campaign->image_one = $this->uploadFile($request->file('image_one'), 'hero');
            }

            // Handle replacement of image_two
            if ($request->hasFile('image_two')) {
                $this->deleteFile($campaign->image_two);
                $campaign->image_two = $this->uploadFile($request->file('image_two'), 'feat1');
            }

            // Handle replacement of image_three
            if ($request->hasFile('image_three')) {
                $this->deleteFile($campaign->image_three);
                $campaign->image_three = $this->uploadFile($request->file('image_three'), 'feat2');
            }

            $campaign->save();

            // Link product
            if (!empty($campaign->product_id)) {
                Product::where('id', $campaign->product_id)->update(['campaign_id' => $campaign->id]);
            }

            // Save additional review proof screenshots
            if ($request->hasFile('image')) {
                $this->saveReviewImages($campaign->id, (array) $request->file('image'));
            }

            return $campaign;
        });
    }

    /**
     * Toggle campaign active/inactive status.
     */
    public function toggleStatus(Campaign $campaign): bool
    {
        $campaign->status = $campaign->status == 1 ? 0 : 1;
        return $campaign->save();
    }

    /**
     * Delete campaign and clean up all associated media.
     */
    public function deleteCampaign(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $this->deleteFile($campaign->image_one);
            $this->deleteFile($campaign->image_two);
            $this->deleteFile($campaign->image_three);

            // Clean up review images
            foreach ($campaign->images as $review) {
                $this->deleteFile($review->image);
                $review->delete();
            }

            // Unlink product
            Product::where('campaign_id', $campaign->id)->update(['campaign_id' => null]);

            return (bool) $campaign->delete();
        });
    }

    /**
     * Delete a single review proof image.
     */
    public function deleteReviewImage(int $reviewImageId): bool
    {
        $review = CampaignReview::find($reviewImageId);
        if ($review) {
            $this->deleteFile($review->image);
            return (bool) $review->delete();
        }

        return false;
    }

    /**
     * Clone / Duplicate a campaign for rapid A/B testing and marketing.
     */
    public function duplicateCampaign(Campaign $campaign): Campaign
    {
        return DB::transaction(function () use ($campaign) {
            $clone = $campaign->replicate([
                'id',
                'created_at',
                'updated_at',
            ]);

            $clone->name = $campaign->name . ' (Copy)';
            $clone->slug = $this->generateUniqueSlug($campaign->name . '-copy');
            $clone->status = 0; // Set draft by default for safe editing
            $clone->save();

            // Duplicate review images references
            foreach ($campaign->images as $review) {
                CampaignReview::create([
                    'campaign_id' => $clone->id,
                    'image' => $review->image,
                    'name' => $review->name,
                    'description' => $review->description,
                    'status' => $review->status,
                ]);
            }

            return $clone;
        });
    }

    /**
     * Save multiple review proof images.
     */
    private function saveReviewImages(int $campaignId, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->uploadFile($file, 'rev');
                CampaignReview::create([
                    'campaign_id' => $campaignId,
                    'image' => $path,
                ]);
            }
        }
    }

    /**
     * Upload an image and return stored relative path.
     */
    private function uploadFile(UploadedFile $file, string $prefix = 'camp'): string
    {
        $filename = time() . '-' . $prefix . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $filename = strtolower(preg_replace('/\s+/', '-', $filename));
        $file->move($this->uploadDir, $filename);

        return 'uploads/campaign/' . $filename;
    }

    /**
     * Safely delete a file if exists.
     */
    private function deleteFile(?string $path): void
    {
        if (!empty($path)) {
            $fullPath = public_path($path);
            if (File::exists($fullPath) && !File::isDirectory($fullPath)) {
                File::delete($fullPath);
            }
        }
    }

    /**
     * Generate unique slug avoiding collision.
     */
    public function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug ?: 'campaign';
        $counter = 1;

        while (
            Campaign::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return strtolower($slug);
    }
}
