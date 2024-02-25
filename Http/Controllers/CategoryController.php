<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\Upload;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use Upload;

    public function index()
    {
        $categories = Category::with('activities')->get();

        return view('admin.pages.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.pages.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'category_title' => 'required',
            'category_description' => 'sometimes',
            'seo_title' => 'sometimes',
            'seo_h1' => 'sometimes',
            'seo_description' => 'sometimes',
            'slug' => 'sometimes',
            'image' => 'sometimes',
            'status' => 'required',
            'priority' => 'sometimes|required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = optional($validator->validated());

        $cat = new Category();
        if ($request->hasFile('image')) {
            $validated['image'] = Storage::putFileAs(
                'public/categories',
                $validated['image'],
                time() . $validated['image']->getClientOriginalName(),
            );
            $cat->image = str_replace('public', 'storage', $validated['image']);
        }

        $cat->category_title = $validated['category_title'];
        $cat->seo_title = $validated['seo_title'];
        $cat->seo_h1 = $validated['seo_h1'];
        $cat->seo_description = $validated['seo_description'];
        $cat->category_description = $validated['category_description'];
        $cat->slug = $validated['slug'];
        $cat->status = $validated['status'];
        $cat->priority = $validated['priority'];
        $cat->save();

        return back()->with('success', 'Successfully Updated');
    }

    public function show(Request $request): JsonResponse
    {
        $categories = Category::where('category_title', 'LIKE', "%{$request->data}%")->get()->pluck('category_title');

        return response()->json($categories);
    }

    public function edit($id)
    {
        $category = Category::find($id);

        return view('admin.pages.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Category $category)
    {
        $cat = Category::find($request->id);

        $rules = [
            'category_title' => 'required',
            'category_description' => 'sometimes',
            'seo_title' => 'sometimes',
            'seo_h1' => 'sometimes',
            'seo_description' => 'sometimes',
            'slug' => 'sometimes',
            'image' => 'sometimes',
            'status' => 'required',
            'priority' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validated = $validator->validated();
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if ($request->hasFile('image')) {

            if ($cat->image) {
                $oldImage = str_replace('storage', 'public', $cat->image);
                Storage::exists($oldImage) && Storage::delete($oldImage);
            }

            $validated['image'] = Storage::putFileAs(
                'public/categories',
                $validated['image'],
                time() . $validated['image']->getClientOriginalName(),
            );

            $cat->image = str_replace('public', 'storage', $validated['image']);
        }

        $cat->category_title = $validated['category_title'];
        $cat->seo_title = $validated['seo_title'];
        $cat->seo_h1 = $validated['seo_h1'];
        $cat->seo_description = $validated['seo_description'];
        $cat->category_description = $validated['category_description'];
        $cat->slug = $validated['slug'];
        $cat->status = $validated['status'];
        $cat->priority = $validated['priority'];
        $cat->save();

        return back()->with('success', 'Successfully Updated');
    }

    public function categoryActive(Request $request)
    {
        $category = Category::all();
        foreach ($category as $cate) {
            $re = Category::find($cate->id);
            if (!$re) {
                continue;
            } else {
                $re->status = 1;
                $re->save();
            }
        }

        return back()->with('success', 'Successfully Updated');
    }

    public function categoryDeactive(Request $request)
    {
        $category = Category::all();
        foreach ($category as $cate) {
            $re = Category::find($cate->id);
            if (!$re) {
                continue;
            } else {
                $re->status = 0;
                $re->save();
            }
        }

        return back()->with('success', 'Successfully Updated');
    }

    public function statusChange(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $status = !$category['status'];

        $category->status = $status;

        $categoryActivities = $category->activities();

        $categoryActivities->update([
            'status' => $status,
        ]);

        $category->save();

        return back()->with('success', 'Successfully Updated');
    }

    public function search(Request $request)
    {
        $search = $request->all();
        $categories = Category::when(isset($search['category_title']), fn($query) => $query->where('category_title', 'LIKE', "%{$search['category_title']}%"))->when(isset($search['status']), fn($query) => $query->where('status', $search['status']))->get();
        $categories->append($search);

        return view('admin.pages.categories.index', compact('categories'));
    }

    // multiple active check
    public function activeMultiple(Request $request)
    {
        if (null == $request->strIds) {
            session()->flash('error', 'You do not select User Id!!');

            return response()->json(['error' => 1]);
        } else {
            $ids = explode(',', $request->strIds);
            if (\count($ids) > 0) {
                $categoryes = Category::whereIn('id', $ids);
                $categoryes->update([
                    'status' => 1,
                ]);
            }
            session()->flash('success', 'User Active Updated Successfully!!');

            return response()->json(['success' => 1]);
        }
    }

    // multiple inactive check
    public function deactiveMultiple(Request $request)
    {
        if (null == $request->strIds) {
            session()->flash('error', 'You do not select User Id!!');

            return response()->json(['error' => 1]);
        } else {
            $ids = explode(',', $request->strIds);
            if (\count($ids) > 0) {
                $categoryes = Category::whereIn('id', $ids);
                $categoryes->update([
                    'status' => 0,
                ]);
            }
            session()->flash('success', 'User Active Updated Successfully!!');

            return response()->json(['success' => 1]);
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        Activity::where('category_id', $id)->delete();

        $category->delete();

        session()->flash('success', 'Category Deleted Successfully!!');

        return redirect()->back();
    }
}
