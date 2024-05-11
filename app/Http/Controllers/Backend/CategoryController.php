<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Exception;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::latest()->get();
        return view('admin.backend.category.index', compact('category'));
    }

    public function create()
    {
        return view('admin.backend.category.create');
    }

    public function store(Request $request)
    {

        $image = $request->file('image');
        if ($image) {
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            // create new image instance
            $imageManager = ImageManager::gd()->read($image);
            $imageManager->resize(370, 246);
            $imageManager->toJpeg(80)->save(('upload/category/' . $name_gen));
            $save_url = 'upload/category/' . $name_gen;

            Category::create([
                'category_name' => $request->category_name,
                'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),
                'image' => $save_url,
            ], ['timestamp' => true]);
        }

        $notification = array(
            'message' => 'Category Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('categories.index')->with($notification);
    }

    public function edit($id)
    {

        $category = Category::find($id);
        return view('admin.backend.category.edit', compact('category'));
    }

    public function update(Request $request)
    {
        $cat_id = $request->id;
        $image = $request->file('image');
        $category = Category::find($cat_id);

        if ($request->file('image')) {
            if (file_exists(public_path($category->image))) {
                @unlink(public_path($category->image));
            }

            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            // create new image instance
            $imageManager = ImageManager::gd()->read($image);
            $imageManager->resize(370, 246);
            $imageManager->toJpeg(80)->save(('upload/category/' . $name_gen));
            $save_url = 'upload/category/' . $name_gen;

            $category->update([
                'category_name' => $request->category_name,
                'category_slug' =>  str()->slug($request->category_name),
                'image' => $save_url,
            ], ['timestamps' => true]);

            $notification = array(
                'message' => 'Category Updated with image Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('categories.index')->with($notification);
        } else {

            Category::find($cat_id)->update([
                'category_name' => $request->category_name,
                'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),

            ]);

            $notification = array(
                'message' => 'Category Updated without image Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('categories.index')->with($notification);
        }
    }

    public function delete($id)
    {

        $item = Category::find($id);
        $img = $item->image;
        @unlink($img);

        Category::find($id)->delete();

        $notification = array(
            'message' => 'Category Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    // SubCategory Methods

    public function allSubCategory()
    {

        $subcategory = SubCategory::latest()->get();
        return view('admin.backend.subcategory.index', compact('subcategory'));
    }


    public function createSubCategory()
    {

        $category = Category::latest()->get();
        return view('admin.backend.subcategory.create', compact('category'));
    }


    public function storeSubCategory(Request $request)
    {

        $category = Category::find($request->category_id);

        if (is_null($category)) {
            $notification = array(
                'message' => 'SubCategory Inserted Fail',
                'alert-type' => 'error'
            );
            return redirect()->route('subcategories.create')->with($notification);
        }
        SubCategory::create([
            'category_id' => $request->category_id,
            'subcategory_name' => $request->subcategory_name,
            'subcategory_slug' => strtolower(str_replace(' ', '-', $request->subcategory_name)),
        ], ['timestamp' => true]);

        $notification = array(
            'message' => 'SubCategory Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('subcategories.index')->with($notification);
    }

    public function editSubCategory($id)
    {

        $category = Category::latest()->get();
        $subcategory = SubCategory::find($id);
        return view('admin.backend.subcategory.edit', compact('category', 'subcategory'));
    }


    public function updateSubCategory(Request $request)
    {

        $subcat_id = $request->id;
        $category = Category::find($request->category_id);

        if (is_null($category)) {
            $notification = array(
                'message' => 'SubCategory Inserted Fail',
                'alert-type' => 'error'
            );
            return redirect()->route('subcategories.edit')->with($notification);
        }

        SubCategory::find($subcat_id)->update([
            'category_id' => $request->category_id,
            'subcategory_name' => $request->subcategory_name,
            'subcategory_slug' => strtolower(str_replace(' ', '-', $request->subcategory_name)),

        ]);

        $notification = array(
            'message' => 'SubCategory Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('subcategories.index')->with($notification);
    }


    public function deleteSubCategory($id)
    {

        SubCategory::find($id)->delete();

        $notification = array(
            'message' => 'SubCategory Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
