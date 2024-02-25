<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $categories = Category::with(['services' => function ($query): void {
            $query->userRate()->where('service_status', 1);
        }])
            ->where('status', 1)
            ->get();

        return view('user.pages.services.show-service', compact('categories'));
    }

    public function search(Request $request)
    {
        $categories = Category::with('services')->where('status', 1)->get();
        $search = $request->all();
        $services = Service::where('service_status', 1)
            ->userRate()
            ->when(isset($search['service']), fn ($query) => $query->where('service_title', 'LIKE', "%{$search['service']}%"))
            ->when(isset($search['category']), fn ($query) => $query->where('category_id', $search['category']))
            ->with(['category'])
            ->get()
            ->groupBy('category.category_title');

        return view('user.pages.services.search-service', compact('services', 'categories'));
    }
}
