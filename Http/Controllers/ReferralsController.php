<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FreewalletService;
use App\Models\FreewalletPayment;
use App\Models\FreewalletCurrency;
use App\Models\FreewalletPaymentSystem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Ixudra\Curl\Facades\Curl;

class ReferralsController extends Controller
{
    /** @return Application|Factory|View */
    public function index(): View|Factory|Application
    {
        $referrals = FreewalletPayment::orderBy('id', 'DESC')->paginate(15);

        return view('admin.pages.referrals.index', compact('referrals'));
    }

    public function create(): Factory|View|Application
    {
        return view('admin.pages.referrals.add');
    }

    /**
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'api_name' => 'sometimes|required',
            'api_key' => 'sometimes|required',
            'url' => 'sometimes|required',
            'status' => 'required',
            'description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        $apiProviderData = $validator->validated();
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $ApiProvider = new ApiProvider();
        $ApiProvider->api_name = $apiProviderData['api_name'];
        $ApiProvider->api_key = $apiProviderData['api_key'];
        $ApiProvider->url = $apiProviderData['url'];
        $apiLiveData = Curl::to($apiProviderData['url'])->withData(['key' => $apiProviderData['api_key'], 'action' => 'balance'])->post();
        $currencyData = $apiLiveData ? json_decode($apiLiveData) : $apiLiveData;
//        dd($currencyData);
        if (isset($currencyData->balance)) {
            $ApiProvider->balance = $currencyData->balance;
            $ApiProvider->currency = $currencyData->currency;
        } elseif (isset($currencyData->error)) {
            $error = $currencyData->error;
        } else {
            $error = 'Please Check your API URL Or API Key';
        }
        $ApiProvider->status = $apiProviderData['status'];
        $ApiProvider->description = $apiProviderData['description'];
        if (isset($error)) {
            return back()->with('error', $error)->withInput();
        }
        $ApiProvider->save();

        return back()->with('success', 'successfully updated');
    }

    public function activeMultiple(Request $request)
    {
        if (null == $request->strIds) {
            session()->flash('error', 'You do not select Id!');

            return response()->json(['error' => 1]);
        } else {
            $ids = explode(',', $request->strIds);
            $apiProvider = ApiProvider::whereIn('id', $ids);
            $apiProvider->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Updated Successfully!');

            return response()->json(['success' => 1]);
        }
    }

    public function deActiveMultiple(Request $request)
    {
        if (null == $request->strIds) {
            session()->flash('error', 'You do not select Id!');

            return response()->json(['error' => 1]);
        } else {
            $ids = explode(',', $request->strIds);
            $apiProvider = ApiProvider::whereIn('id', $ids);
            $apiProvider->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Updated Successfully.');

            return response()->json(['success' => 1]);
        }
    }

    public function edit($id)
    {
        $paymentSystems = FreewalletPaymentSystem::all();
        $referral = FreewalletPayment::find((int)$id);

        return view('admin.pages.referrals.edit', [
            'referral' => $referral,
            'payment_systems' => $paymentSystems
        ]);
    }

    public function update(Request $request, ApiProvider $apiProvider)
    {
        $rules = [
            'api_name' => 'sometimes|required',
            'api_key' => 'sometimes|required',
            'url' => 'sometimes|required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $provider = ApiProvider::find($apiProvider->id);
        $provider->api_name = $request['api_name'];
        $provider->api_key = $request['api_key'];
        $provider->url = $request['url'];
        $apiLiveData = Curl::to($request['url'])->withData(['key' => $request['api_key'], 'action' => 'balance'])->post();
        $currencyData = json_decode($apiLiveData);
        if (isset($currencyData->balance)) {
            $provider->balance = $currencyData->balance;
            $provider->currency = $currencyData->currency;
        } elseif (isset($currencyData->error)) {
            $error = $currencyData->error;
        } else {
            $error = 'Please Check your API URL Or API Key';
        }
        $provider->status = $request['status'];
        $provider->description = $request['description'];
        if (isset($error)) {
            return back()->with('error', $error)->withInput();
        }
        $provider->save();

        return back()->with('success', 'successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(ApiProvider $apiProvider)
    {
        $apiProvider->delete();

        return back()->with('success', 'Successfully Deleted');
    }

    /*
     ** multiple delete
     */
    public function deleteMultiple(Request $request)
    {
        $ids = $request->strIds;
        ApiProvider::whereIn('id', explode(',', $ids))->delete();

        return back()->with('success', 'Delete Success');
    }

    public function confirm($id)
    {
        $freewalletService = new FreewalletService;
        $message = $freewalletService->confirm((int)$id);
        if(!empty($message))
            return back()->with('error', $message);

        return back()->with('success', 'Запрос на вывод средств успешно подтвержден');
    }

    public function priceUpdate($id)
    {
        $provider = ApiProvider::with('services')->findOrFail($id);
        $apiLiveData = Curl::to($provider->url)->withData(['key' => $provider->api_key, 'action' => 'services'])->post();
        $currencyData = collect(json_decode($apiLiveData));
        foreach ($provider->services as $k => $data) {
            if (isset($data->price)) {
                $data->update([
                    'api_provider_price' => $currencyData->where('service', $data->api_service_id)->pluck('price')[0] ?? $data->api_provider_price ?? $data->price,
                    'price' => $currencyData->where('service', $data->api_service_id)->pluck('price')[0] ?? $data->price,
                ]);
            }
        }

        return back()->with('success', 'Successfully updated');
    }

    public function balanceUpdate($id)
    {
        $provider = ApiProvider::findOrFail($id);
        $apiLiveData = Curl::to($provider->url)->withData(['key' => $provider->api_key, 'action' => 'balance'])->post();
        $providerBalance = json_decode($apiLiveData);
        $provider->update([
            'balance' => $providerBalance->balance ?? $provider->balance,
            'currency' => $providerBalance->currency ?? $provider->currency,
        ]);

        return back()->with('success', 'Successfully updated');
    }

    public function getApiServices(Request $request)
    {
        $rules = [
            'api_provider_id' => 'required|string|max:150',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $provider = ApiProvider::find($request->api_provider_id);

        $apiLiveData = Curl::to($provider['url'])->withData(['key' => $provider['api_key'], 'action' => 'services'])->get();
        $apiServiceLists = json_decode($apiLiveData);

        return view('admin.pages.services.show-api-services', compact('apiServiceLists', 'provider'));
    }

    public function import(Request $request)
    {
        $req = $request->all();

        $activity = new Activity();
        $activity->title = $req['category'];
        $activity->category_id = $req['category_id'];
        $activity->status = 1;
        $activity->save();

        $existService = Service::where('api_provider_id', $req['id'])->exists();

        if ($existService) {
            return redirect()->route('admin.service.show')
                ->with('success', 'Already Have this service');
        }

        $service = new Service();
        $service->service_title = $req['name'];
        $service->category_id = $req['category_id'];
        $service->min_amount = $req['min'];
        $service->max_amount = $req['max'];
        $increased_price = ($req['rate'] * $req['price_percentage_increase']) / 100;
        $service->price = $req['rate'] + $increased_price;
        $service->service_status = 1;
        $service->api_provider_id = $req['provider'];
        $service->api_service_id = @$req['id'];
        $service->drip_feed = @$req['dripfeed'];
        $service->api_provider_price = @$req['rate'];
        $service->save();

        return redirect()->route('admin.service.show');
    }

    public function importMulti(Request $request)
    {
        $req = $request->all();

        $provider = ApiProvider::find($req['provider']);
        $apiLiveData = Curl::to($provider['url'])
            ->withData(['key' => $provider['api_key'], 'action' => 'services'])->post();
        $apiServicesData = json_decode($apiLiveData);
        $count = 0;
        foreach ($apiServicesData as $apiService) {
            $all_category = Category::all();
            $services = Service::all();
            $insertCat = 1;
            $existService = 0;
            foreach ($all_category as $categories) {
                if ($categories->category_title == $apiService->category) {
                    $insertCat = 0;
                }
            }
            if (1 == $insertCat) {
                $cat = new Category();
                $cat->category_title = $apiService->category;
                $cat->status = 1;
                $cat->save();
            }
            foreach ($services as $service) {
                if ($service->api_service_id == $apiService->service) {
                    $existService = 1;
                }
            }
            if (1 != $existService) {
                $service = new Service();
                $idCat = Category::where('category_title', $apiService->category)->first()->id ?? null;
                $service->service_title = $apiService->name;
                $service->category_id = $idCat;
                $service->min_amount = $apiService->min;
                $service->max_amount = $apiService->max;
                $increased_price = ($apiService->rate * 10) / 100;

                $increased_price = ($apiService->rate * $req['price_percentage_increase']) / 100;

                $service->price = $apiService->rate + $increased_price;
//                $service->price = $apiService->rate;

                $service->service_status = 1;
                $service->api_provider_id = $req['provider'];
                $service->api_service_id = $apiService->service;
                $service->drip_feed = @$apiService->dripfeed;
                $service->api_provider_price = $apiService->rate;

                if (isset($apiService->desc)) {
                    $service->description = @$apiService->desc;
                } else {
                    $service->description = @$apiService->description;
                }

                $service->save();
            }
            ++$count;
            if ('all' == $req['import_quantity']) {
                continue;
            } elseif ($req['import_quantity'] == $count) {
                break;
            }
        }

        return redirect()->route('admin.service.show');
    }

    public function providerShow(Request $request)
    {
        $provider = ApiProvider::where('api_name', 'LIKE', "%{$request->data}%")->get()->pluck('api_name');

        return response()->json($provider);
    }

    public function search(Request $request)
    {
        $search = $request->all();
        $referrals = FreewalletPayment::select('freewallet_payments.*')
            ->join('users', 'freewallet_payments.user_id', 'users.id')
            ->when(isset($search['referral']), function ($query) use ($search) {
                $query->where('freewallet_payments.account', 'LIKE', "%{$search['referral']}%")
                    ->orWhere('freewallet_payments.id', 'LIKE', "%{$search['referral']}%")
                    ->orWhere('users.username', 'LIKE', "%{$search['referral']}%")
                    ->orWhere('users.id', 'LIKE', "%{$search['referral']}%")
                    ->orWhere('users.firstname', 'LIKE', "%{$search['referral']}%")
                    ->orWhere('users.lastname', 'LIKE', "%{$search['referral']}%")
                    ->orWhere('users.email', 'LIKE', "%{$search['referral']}%");
            })
            ->when(isset($search['status']), fn ($query) => $query->where('freewallet_payments.status', $search['status']))
            ->distinct()
            ->paginate(15);
        $referrals->append($search);

        return view('admin.pages.referrals.index', compact('referrals'));
    }
}
