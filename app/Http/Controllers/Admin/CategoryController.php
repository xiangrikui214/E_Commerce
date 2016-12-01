<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Entity\Category;
use App\Models\M3Result;
use Illuminate\Http\Request;



class CategoryController extends Controller
{
    public function toCategory(){
        $categories = Category::all();
        foreach ($categories as $category) {
            if($category->parent_id != null && $category->parent_id != ''){
                $category->parent = Category::where('id', $category->parent_id)->first();
            }
        }
    	return view('admin.category')->with('categories', $categories);
    }
    public function toAddCategory(){
        $categories = Category::whereNull('parent_id')->get();

        return view('admin/category_add')->with('categories', $categories);
    }

    public function toEditCategory(Request $request){
        $category_id = $request->input('id', '');
        $category = Category::find($category_id);
        $categories = Category::whereNull('parent_id')->get();
        return view('admin/category_edit')->with('category', $category)->with('categories', $categories);
    }

    /***************service*****************/
    public function categoryAdd(Request $request){
        $name = $request->input('name', '');
        $category_no = $request->input('category_no', '');
        $parent_id = $request->input('parent_id', '');
        $preview = $request->input('preview', '');

        $category = new Category;
        $category->name = $name;
        $category->category_no = $category_no;
        $category->preview = $preview;
        if($parent_id != '') {
          $category->parent_id = $parent_id;
        }
        $category->save();

        $m3_result = new M3Result;
        $m3_result->status = 0;
        $m3_result->message = '添加成功';

        return $m3_result->toJson();
    }
    public function categoryDel(Request $request){
        $category_id = $request->input('id', '');
        Category::find($category_id)->delete();
        $m3_result = new M3Result;
        $m3_result->status = 0;
        $m3_result->message = '删除成功';

        return $m3_result->toJson();
    }
    public function categoryEdit(Request $request){
        $category_id = $request->input('id', '');
        $category = Category::find($category_id);

        $name = $request->input('name', '');
        $category_no = $request->input('category_no', '');
        $parent_id = $request->input('parent_id', '');
        $preview = $request->input('preview', '');

        $category->name = $name;
        $category->category_no = $category_no;
        $category->preview = $preview;
        if($parent_id != '') {
          $category->parent_id = $parent_id;
        }
        $category->save();

        $m3_result = new M3Result;
        $m3_result->status = 0;
        $m3_result->message = '添加成功';

        return $m3_result->toJson();
    }

}