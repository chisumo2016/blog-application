<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class SettingsController extends Controller
{
    //
    public function  index()
    {
        return view('admin.settings');
    }

    public function  updateProfile(Request $request)
    {
        $this->validate($request, [
            'name'  => 'required',
            'email' => 'required|email',
            'image' => 'required|image',
            ''
        ]);

        $image = $request->file('image');
        $slug = str_slug($request->name);
        $user = User::findOrFail(Auth::id());

        if(isset($image))
        {
            // Make unique for image
            $current_date  = Carbon::now()->toDateString();
            $image_name    = $slug.'-'.$current_date.'-'.uniqid().'.'.$image->getClientOriginalExtension();

            if(!Storage::disk('public')->exists('profile'))
            {
                Storage::disk('public')->makeDirectory('profile');
            }

            //Delete old image form profile folder
            if(Storage::disk('public')->exists('profile/'.$user->image))
            {
                Storage::disk('public')->delete('profile/'.$user->image);
            }

            $profile = Image::make( $image)->resize(500,500)->stream();
            Storage::disk('public')->put('profile/'.$image_name,$profile  );
        }else{
            $image_name = $user->image;
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->image = $image_name;
        $user->about = $request->about;

        $user->save();

        Toastr::success('Profile Successfully Updated  :)', 'Success');
        return redirect()->back();

    }

    public function  updatePassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        $hashedPassword = Auth::user()->password;
        if(Hash::check($request->old_password,$hashedPassword))
        {
            if(!Hash::check($request->password, $hashedPassword))
            {
                $user = User::find(Auth::id());
                $user->password = Hash::make($request->getPassword);
                $user->save();

                Toastr::success('Password Successfully Changed  :)', 'Success');

                Auth::logout();
                return redirect()->back();

            }else{

                Toastr::error('New password cannot be the same as old password   :)', 'Error');

                return redirect()->back();
            }

        }else{

            Toastr::error('Current password not match  :)', 'Error');
            return redirect()->back();
        }
    }
}
