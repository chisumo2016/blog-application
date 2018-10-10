<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Notifications\AuthorPostApproved;
use App\Notifications\NewAuthorPost;
use App\Notifications\NewPostNotify;
use App\Post;
use App\Subscriber;
use App\Tag;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $posts = Post::latest()->get();
        return view('admin.post.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

         $categories = Category::all();
         $tags = Tag::all();
         return view('admin.post.create', compact('categories', 'tags'));


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
           'title'          => 'required',
            'image'         =>  'required',
            'categories'    =>  'required',
            'tags'          =>  'required',
            'description'   =>  'required',
        ]);

        $image = $request->file('image');
        $slug = str_slug($request->title);

        if(isset($image))
        {
            // Make unique for image
            $current_date  = Carbon::now()->toDateString();
            $image_name    = $slug.'-'.$current_date.'-'.uniqid().'.'.$image->getClientOriginalExtension();


            //check post dir is exists
            if(!Storage::disk('public')->exists('post'))
            {
                Storage::disk('public')->makeDirectory('post');
            }

            //Resize image for posy and upload

            $postImage = Image::make($image)->resize(1600,1066)->stream();

            Storage::disk('public')->put('post/'.$image_name,$postImage );

        }else{

            $image_name = "default.png";
        }

        $post = new Post();
        $post->user_id = Auth::id();
        $post->title = $request ->title;
        $post->slug  = $slug;
        $post->image = $image_name;
        $post->description = $request->description;

        if(isset($request->status))
        {
            $post->status = true;

        }else{
            $post->status = false;
        }

        $post->is_approved  = true;

        $post->save();

        $post->categories()->attach($request->categories);

        $post->tags()->attach($request->tags);

        Toastr::success('Post Successfully Created !', 'Success');

        return redirect()->route('admin.post.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //

        return view('admin.post.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.post.edit', compact('post','categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        //
        $this->validate($request, [
            'title' => 'required',
            'image' => 'image',
            'categories' => 'required',
            'tags' => 'required',
            'description' => 'required',
        ]);

        $image = $request->file('image');
        $slug = str_slug($request->title);

        if (isset($image)) {
            // Make unique for image
            $current_date = Carbon::now()->toDateString();
            $image_name = $slug . '-' . $current_date . '-' . uniqid() . '.' . $image->getClientOriginalExtension();


            //check post dir is exists
            if (!Storage::disk('public')->exists('post')) {
                Storage::disk('public')->makeDirectory('post');
            }

            //Delete the old post  Image

            if (Storage::disk('public')->exists('post/' . $post->image)) {
                Storage::disk('public')->delete('post/' . $post->image);
            }

            //Resize image for posy and upload

            $postImage = Image::make($image)->resize(1600, 1066)->stream();

            Storage::disk('public')->put('post/' . $image_name, $postImage);

        } else {

            $image_name = $post->image;
        }


        $post->user_id = Auth::id();
        $post->title = $request->title;
        $post->slug = $slug;
        $post->image = $image_name;
        $post->description = $request->description;

        if (isset($request->status)) {
            $post->status = true;

        } else {
            $post->status = false;
        }

        $post->is_approved = true;

        $post->save();

        $post->categories()->sync($request->categories);

        $post->tags()->sync($request->tags);

        //send notification to subscriber

        $subscribers = Subscriber::all();

        foreach ($subscribers as $subscriber)
        {
            Notification::route('mail', $subscriber->email)
                ->notify(new NewPostNotify($post));
        }

        Toastr::success('Post Successfully Update !', 'Success');

        return redirect()->route('admin.post.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        //Cheking if the image exists

        if(Storage::disk('public')->exists('post/'. $post->image))
        {
            Storage::disk('public')->delete('post/'.$post->image);
        }

        //Dettach categories and tags
        $post->categories()->detach();
        $post->tags()->detach();

        Toastr::success('Post Successfully Deleted !', 'Success');

        return redirect()->route('admin.post.index');

        //return $post;
    }


    /* Pending and Approval */

    public function  pending()
    {
        $posts = Post::where('is_approved', false)->get();

        return  view('admin.post.pending', compact('posts'));
    }

    public function  approval($id)
    {
         $post = Post::find($id);

         if($post->is_approved == false)
         {
             $post->is_approved =true;
             $post->save();

             //Sending Notifcation to admin

             //send notification to subscriber

             $subscribers = Subscriber::all();
             $post->user->notify(new AuthorPostApproved($post));

             foreach ($subscribers as $subscriber)
             {
                 Notification::route('mail', $subscriber->email)
                     ->notify(new NewPostNotify($post));
             }

             Toastr::success('Post Successfully Approved!', 'Success');
         }else{
             Toastr::Info('This post is already approved !', 'Info');
         }


         return redirect()->back();
    }
}









