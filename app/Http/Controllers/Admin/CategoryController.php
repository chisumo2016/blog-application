<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $categories = Category::latest()->get();

        //return $categories ;

        return view('admin.category.index', compact('categories'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
       $this->validate($request,[
           'name' => 'required|unique:categories',
           'image' => 'required|mimes:jpeg,png,jpg,bmp'
       ]);

       // Get form image-
        $image = $request->file('image');
        $slug  = str_slug($request->name);

        if(isset($image))
        {
            //Make unique name for image
            $current_date  = Carbon::now()->toDateString();
            $image_name    = $slug.'-'.$current_date.'-'.uniqid().'.'.$image->getClientOriginalExtension();


            //check category dir is exists
            if(!Storage::disk('public')->exists('category'))
            {
                Storage::disk('public')->makeDirectory('category');
            }

            //Resize image for category and upload

            $categoryImage = Image::make($image)->resize(1600,479)->stream();


            Storage::disk('public')->put('category/'.$image_name,$categoryImage );

            //Check if category dir slider is exists

            if (!Storage::disk('public')->exists('category/slider'))
            {
                Storage::disk('public')->makeDirectory('category/slider');
            }

            //Resize image for category  slider and upload

            $sliderImage = Image::make($image)->resize(500,333)->stream();

            Storage::disk('public')->put('category/slider/'.$image_name,$sliderImage);
        }else{

            $image_name = "default.png";
        }

        //Create category

        $category = new Category();
        $category ->name = $request->name;
        $category->slug = $slug;

        $category->image = $image_name;
        $category->save();

        Toastr::success('Category Successfully Created', 'Success');

        return redirect()->route('admin.category.index');


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $category = Category::find($id);

        return view('admin.category.edit' ,compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //
        $this->validate($request,[
            'name' => 'required',
            'image' => 'mimes:jpeg,png,jpg,bmp'
        ]);

        // Get form image-
        $image = $request->file('image');
        $slug  = str_slug($request->name);

        // Find the category u wish to edit
        $category = Category::find($id);


        if(isset($image))
        {
            //Make unique name for image
            $current_date  = Carbon::now()->toDateString();
            $image_name    = $slug.'-'.$current_date.'-'.uniqid().'.'.$image->getClientOriginalExtension();


            //check category dir is exists
            if(!Storage::disk('public')->exists('category'))
            {
                Storage::disk('public')->makeDirectory('category');
            }

            //Delete the old image

            if(Storage::disk('public')->exists('category/'.$category->image))
            {
                Storage::disk('public')->delete('category/'.$category->image);
            }



            //Resize image for category and upload

            $categoryImage = Image::make($image)->resize(1600,479)->stream();


            Storage::disk('public')->put('category/'.$image_name,$categoryImage );

            //Check if category dir slider is exists

            if (!Storage::disk('public')->exists('category/slider'))
            {
                Storage::disk('public')->makeDirectory('category/slider');
            }

            //Delete the old slider image

            if(Storage::disk('public')->exists('category/slider/'.$category->image))
            {
                Storage::disk('public')->delete('category/slider/'.$category->image);
            }

            //Resize image for category  slider and upload

            $sliderImage = Image::make($image)->resize(500,333)->stream();

            Storage::disk('public')->put('category/slider/'.$image_name,$sliderImage);
        }else{

            $image_name = $category->name;
        }

        //Create category

        //$category = new Category();
        $category ->name = $request->name;
        $category->slug = $slug;

        $category->image = $image_name;
        $category->save();

        Toastr::success('Category Successfully Updated', 'Success');

        return redirect()->route('admin.category.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        $category =Category::find($id);

        if(Storage::disk('public')->exists('category/'.$category->image))
        {
            Storage::disk('public')->delete('category/'.$category->image);
        }

        if(Storage::disk('public')->exists('category/slider/'.$category->image))
        {
            Storage::disk('public')->delete('category/slider/'.$category->image);
        }

        $category->delete();

        Toastr::success('Category Successfully  Deleted', 'Success');

        return redirect()->back();


    }
}
