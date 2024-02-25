<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaticPageRequest;
use App\Http\Traits\Upload;
use App\Models\StaticPage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class StaticPageController extends Controller
{
    use Upload;

    public function index(): Factory|View|Application
    {
        $pages = StaticPage::all();

        return view('admin.static-pages.index', compact('pages'));
    }

    public function create(): Factory|View|Application
    {
        return view('admin.static-pages.create');
    }

    public function store(StoreStaticPageRequest $request)
    {
        StaticPage::create($request->validated());

        return redirect()->back()->with([
            'success' => 'Ok'
        ]);
    }

    public function update(int $id,StoreStaticPageRequest $request): RedirectResponse
    {
        StaticPage::find($id)->update($request->validated());

        return redirect()->back()->with([
            'success' => 'Ok'
        ]);
    }

    public function show(int $id)
    {
        $staticPage = StaticPage::find($id);

        return view('admin.static-pages.edit',compact('staticPage'));
    }

    public function destroy(int $id){
        $staticPage = StaticPage::find($id);

        $staticPage->delete();

        return redirect()->back()->with([
            'success' => 'Ok'
        ]);
    }
}
