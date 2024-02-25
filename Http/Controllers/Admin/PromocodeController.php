<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromocodeRequest;
use App\Models\Category;
use App\Models\Promocode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

use function view;

class PromocodeController extends Controller
{
    public function index(): Factory|View|Application
    {
        $promoCodes = Promocode::all();

        return view(
            'admin.pages.promo-codes.index',
            compact('promoCodes')
        );
    }

    public function create(): Factory|View|Application
    {
        $categories = Category::with('activities')
            ->whereHas('activities')
            ->where('status', 1)
            ->get();

        return view(
            'admin.pages.promo-codes.create',
            compact('categories')
        );
    }

    public function store(StorePromocodeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (!$validated['count_enabled'] || !isset($validated['count_remains'])) {
            $validated['count_remains'] = null;
        } else {
            $validated['count'] = $validated['count_remains'];
        }

        Promocode::create(
            $validated
        );

        return back()->with(
            'success',
            'Successfully Added'
        );
    }

    public function show($id): void
    {
    }

    public function edit(int $id): Factory|View|Application
    {
        $promoCode = Promocode::findOrFail($id);

        $categories = Category::with('activities')
            ->whereHas('activities')
            ->where('status', 1)
            ->get();

        return view(
            'admin.pages.promo-codes.edit',
            compact(
                'promoCode',
                'categories'
            )
        );
    }

    public function update(StorePromocodeRequest $request, $id): RedirectResponse
    {
        $promocode = Promocode::findOrFail($id);

        $validated = $request->validated();

        if (!$validated['count_enabled']) {
            $validated['count_remains'] = null;
        } else {
            $validated['count'] = $validated['count_remains'];
        }

        $promocode->update($validated);

        return back()->with(
            'success',
            'Обновлено успешно'
        );
    }

    public function destroy(int $id): RedirectResponse
    {
        Promocode::findOrFail($id)->delete();

        return back()->with(
            'success',
            'Удалено успешно'
        );
    }

    public function changeStatus(int $id): RedirectResponse
    {
        $promoCode = Promocode::findOrFail($id);

        $promoCode->update(
            ['is_active' => !$promoCode->is_active]
        );

        return back()->with('success', 'Успешно обновлено!');
    }
}
