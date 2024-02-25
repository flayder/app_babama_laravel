<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Activity;
use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Country;
use App\Models\Service;
use App\Models\ServiceParameter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): Factory|View|Application
    {
        $categories = Category::with('services', 'services.provider')
            ->has('services')
            ->paginate(config('basic.paginate'));

        $apiProviders = ApiProvider::all();
        return view(
            'admin.pages.services.index',
            compact(
                'categories',
                'apiProviders'
            )
        );
    }

    public function search(Request $request): Factory|View|Application
    {
        $categories = Category::with('services')->get();
        $apiProviders = ApiProvider::all();

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

    public function create(): Factory|View|Application
    {
        $categories = Category::with('activities')->whereHas('activities')->where('status', 1)->get();

        $apiProviders = ApiProvider::orderBy('id', 'DESC')->where('status', 1)->get();

        $services = Service::all();

        $countries = Country::all()->pluck('name', 'id');

        return view(
            'admin.pages.services.create',
            compact(
                'categories',
                'apiProviders',
                'services',
                'countries'
            )
        );
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        $activity = Activity::findOrFail($validatedData['activity_id']);

       $service =  Service::create($validatedData + [
                'category_id' => $activity->category_id,
            ]);

        $service->parameters()->createMany($validatedData['service_parameter']);

        return back()->with('success', 'Создан Успешно!!');
    }

    public function serviceActive(): RedirectResponse
    {
        $service = Service::all();
        foreach ($service as $data) {
            $ser = Service::find($data->id);
            $ser->service_status = 1;
            $ser->save();
        }

        return back()->with('success', 'Успешно обновлено');
    }

    public function serviceDeActive(): RedirectResponse
    {
        $service = Service::all();
        foreach ($service as $data) {
            $ser = Service::find($data->id);
            $ser->service_status = 0;
            $ser->save();
        }

        return back()->with('success', 'Успешно обновлено');
    }

    public function edit($id): Factory|View|Application
    {
        $service = Service::with('parameters')->find($id);

        $categories = Category::with('activities')
            ->where('status', 1)->get();

        $countries = Country::all()->pluck('name', 'id');

        $apiProviders = ApiProvider::orderBy('id', 'DESC')->where('status', 1)->get();

        $services = Service::where('id', '!=', $id)->get();

        return view(
            'admin.pages.services.edit',
            compact(
                'service',
                'categories',
                'apiProviders',
                'services',
                'countries'
            )
        );
    }

    public function update(UpdateServiceRequest $request, int $id): RedirectResponse
    {
        $validatedData = $request->validated();

        $activity = Activity::findOrFail($validatedData['activity_id']);

        $service = Service::findOrFail($id);

        $service->update($validatedData + [
                'category_id' => $activity->category_id,
            ]);
        if (isset($validatedData['service_parameter'])) {
            $service->parameters()->delete();

            $service->parameters()->createMany($validatedData['service_parameter']);
        }
        return back()->with('success', 'успешно обновлено');
    }

    public function activeMultiple(Request $request): JsonResponse
    {
        if ($request->strIds) {
            $ids = explode(',', $request->strIds);
            if (\count($ids) > 0) {
                $services = Service::whereIn('id', $ids);
                $services->update([
                    'service_status' => 1,
                ]);
                session()->flash('success', 'Updated Successfully.');

                return response()->json(['success' => 1]);
            }
        }

        session()->flash('error', "You didn't select any row");

        return response()->json(['error' => 1]);
    }

    public function deactiveMultiple(Request $request): JsonResponse
    {
        if ($request->strIds) {
            $ids = explode(',', $request->strIds);

            if (\count($ids) > 0) {
                $services = Service::whereIn('id', $ids);

                $services->update([
                    'service_status' => 0,
                ]);

                session()->flash('success', 'Updated Successfully.');

                return response()->json(['success' => 1]);
            }
        }

        session()->flash('error', "You didn't select any row");

        return response()->json(['error' => 1]);
    }

    public function getService(Request $request): JsonResponse
    {
        $service = Service::where('service_title', 'LIKE', "%{$request->data}%")->get()->pluck('service_title');

        return response()->json($service);
    }

    public function changeStatus(int $id): RedirectResponse
    {
        $service = Service::findOrFail($id);

        $service->update(
            ['service_status' => !$service->service_status]
        );

        return back()->with('success', 'Успешно обновлено!');
    }

    public function destroy(int $id): RedirectResponse
    {
        $service = Service::findOrFail($id);

        $service->delete();

        session()->flash('success', 'Service Deleted Successfully!!');

        return redirect()->back();
    }
}
