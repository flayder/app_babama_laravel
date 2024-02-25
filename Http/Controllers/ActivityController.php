<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\Upload;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    use Upload;

    public function index()
    {
        $activities = Activity::get()->sortBy(function($q) {
            return $q->category->category_title;
        });

        return view('admin.pages.activity.index', compact('activities'));
    }

    public function create()
    {
        $activities =Activity::all(['title', 'id']);

        return view('admin.pages.activity.create', compact('activities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
            'has_comment' => 'required',
            'priority' => 'required',
            'icon' => 'sometimes',
            'slug' => 'sometimes',
            'link_demo' => 'string',
            'activity_description' => 'string',
            'seo_title' => 'sometimes',
            'seo_h1' => 'sometimes',
            'seo_description' => 'sometimes',
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = Storage::putFileAs(
                'public/activities',
                $validated['icon'],
                time() . $validated['icon']->getClientOriginalName(),
            );

            $validated['icon'] = str_replace('public', 'storage', $validated['icon']);
        }


        Activity::create($validated);

        return back()->with('success', 'Успешно создано');
    }

    public function edit(Request $request, int $id)
    {
        $activity = Activity::find($id);
        $categories = Category::all(['category_title', 'id']);

        return view('admin.pages.activity.edit', compact('categories', 'activity'));
    }

    public function show(Request $request, int $id)
    {
        $activity = Activity::find($id);
        $categories = Category::all(['category_title', 'id']);

        return view('admin.pages.activity.edit', compact('categories', 'activity'));
    }

    public function update(int $id, Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
            'has_comment' => 'required',
            'priority' => 'required',
            'icon' => 'sometimes',
            'link_demo' => 'string',
            'activity_description' => 'string',
            'slug' => 'string',
            'seo_title' => 'sometimes',
            'seo_h1' => 'sometimes',
            'seo_description' => 'sometimes',
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = Storage::putFileAs(
                'public/activities',
                $validated['icon'],
                time() . $validated['icon']->getClientOriginalName(),
            );

            $validated['icon'] = str_replace('public', 'storage', $validated['icon']);
        }

        Activity::findOrFail($id)->update(
            $validated
        );

        return back()->with('success', 'Успешно создано');
    }

    public function search(Request $request)
    {
        $categories = Category::with('services')->get();
        $activities = Activity::where();

        $search = $request->all();

        $services = Service::with(['category', 'provider'])
            ->when(isset($search['service']), fn($query) => $query->where('service_title', 'LIKE', "%{$search['service']}%"))
            ->when(isset($search['category']), function ($query) use ($search) {
                if (-1 == $search['category']) {
                    return $query->where('category_id', '!=', '-1');
                }

                return $query->where('category_id', $search['category']);
            })
            ->when(-1 != $search['provider'], fn($query) => $query->where('api_provider_id', $search['provider']))
            ->when(-1 != $search['status'], fn($query) => $query->where('service_status', $search['status']))
            ->orderBy('category_id')
            ->get()
            ->groupBy('category.category_title');
        return view('admin.pages.services.search-service', compact('services', 'categories', 'apiProviders'));
    }

    public function activityActive(): RedirectResponse
    {
        $service = Service::all();
        foreach ($service as $data) {
            $ser = Service::find($data->id);
            $ser->service_status = 1;
            $ser->save();
        }

        return back()->with('success', 'Успешно обновлено');
    }

    public function activityDeActive(): RedirectResponse
    {
        $service = Service::all();
        foreach ($service as $data) {
            $ser = Service::find($data->id);
            $ser->service_status = 0;
            $ser->save();
        }

        return back()->with('success', 'Успешно обновлено');
    }

    public function changeStatus(int $id)
    {
        $activity = Activity::findORFail($id);

        $activity->update(
            ['status' => !$activity->status]
        );

        return back()->with('success', 'Успешно обновлено');
    }

    public function destroy(int $id): RedirectResponse
    {
        $activity = Activity::findOrFail($id);

        $activity->delete();

        session()->flash('success', 'Успешно удалено');

        return redirect()->back();
    }
}
