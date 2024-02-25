<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceParameter;
use App\Http\Requests\StoreServiceParameterRequest;
use App\Http\Requests\UpdateServiceParameterRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ServiceParameterController extends Controller
{

    public function index(): Factory|View|Application
    {
        $serviceParameters =[];

        return view('admin.pages.service-parameter.index',[
            'serviceParameters' => $serviceParameters
        ]);
    }


    public function create()
    {
        $services = Service::with(['category' => function ($query) {
            $query->whereHas('activities');
            $query->where('status', 1);
        }])->whereHas('category')
            ->get();
        $apiProviders = ApiProvider::orderBy('id', 'DESC')->where('status', 1)->get();


        return view('admin.pages.service-parameter.create',compact('services','apiProviders'));
    }


    public function store(StoreServiceParameterRequest $request)
    {
        //
    }


    public function show(ServiceParameter $serviceParameter)
    {
        //
    }


    public function edit(ServiceParameter $serviceParameter)
    {
        //
    }


    public function update(UpdateServiceParameterRequest $request, ServiceParameter $serviceParameter)
    {
        //
    }


    public function destroy(ServiceParameter $serviceParameter)
    {
        //
    }
}
