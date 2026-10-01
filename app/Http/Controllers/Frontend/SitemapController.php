<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CreatePage;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $baseUrl = config('app.url', url('/'));

        $products = Product::where('status', 1)
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(5000)
            ->get();

        $categories = Category::where('status', 1)
            ->select('slug', 'updated_at')
            ->get();

        $pages = CreatePage::where('status', 1)
            ->select('slug', 'updated_at')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Homepage
        $xml .= '<url>';
        $xml .= '<loc>' . htmlspecialchars($baseUrl) . '</loc>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // 2. Categories
        foreach ($categories as $cat) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($baseUrl . '/category/' . $cat->slug) . '</loc>';
            $xml .= '<lastmod>' . ($cat->updated_at ? $cat->updated_at->toAtomString() : date('c')) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // 3. Products
        foreach ($products as $prod) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($baseUrl . '/product/' . $prod->slug) . '</loc>';
            $xml .= '<lastmod>' . ($prod->updated_at ? $prod->updated_at->toAtomString() : date('c')) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.9</priority>';
            $xml .= '</url>';
        }

        // 4. Custom Pages
        foreach ($pages as $page) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($baseUrl . '/page/' . $page->slug) . '</loc>';
            $xml .= '<lastmod>' . ($page->updated_at ? $page->updated_at->toAtomString() : date('c')) . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.5</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
