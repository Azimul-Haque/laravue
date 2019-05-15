<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use Image, File;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {        
        $this->middleware('auth:api');
    }

    public function index()
    {
        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,array(
            'name'       => 'required|max:255',
            'email'      => 'required|email|unique:users,email',
            'image'      => 'sometimes',
            'password'   => 'required|min:6'
        ));

        

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->image) {
            // $image = time(). '.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ':')))[1])[0];
            $filename = random_string(5) . time(). '.' . explode(';', explode('/', $request->image)[1])[0];
            $location   = public_path('/images/users/'. $filename);
            // Image::make($request->image)->resize(800, null, function ($constraint) { $constraint->aspectRatio(); })->save($location);
            Image::make($request->image)->resize(300, 300)->save($location);
            $user->image = $filename;
        }

        $user->password = Hash::make($request->password);
        $user->save();
        
        // return User::create([
        //     'name'        => $request->name,
        //     'email'       => $request->email,
        //     'image'       => $filename,
        //     'password'    => Hash::make($request->password)
        // ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return $user;
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
        $user = User::findOrFail($id);

        $this->validate($request,array(
            'name'       => 'required|max:255',
            'email'      => 'required|email|unique:users,email,'. $user->id,
            'image'      => 'sometimes',
            'password'   => 'sometimes|min:6'
        ));

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->image != $user->image) {
            $image_path = public_path('/images/users/'. $user->image);
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            // $image = time(). '.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ':')))[1])[0];
            $filename = random_string(5) . time(). '.' . explode(';', explode('/', $request->image)[1])[0];
            $location   = public_path('/images/users/'. $filename);
            // Image::make($request->image)->resize(800, null, function ($constraint) { $constraint->aspectRatio(); })->save($location);
            Image::make($request->image)->resize(300, 300)->save($location);
            $user->image = $filename;
        }
        if($request->password != '') {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return ['message' => 'Updated successfully! '];    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return ['message' => 'User deleted!'];
    }
}
