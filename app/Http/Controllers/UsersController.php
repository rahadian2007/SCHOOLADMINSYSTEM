<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;

class UsersController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $you = auth()->user();
        $users = User::query();
        $query = $request->input('q');
        if ($query) {
            $users = $users->where('name', 'like', "%$query%")
                ->orWhereHas('detail', function ($q) use ($query) {
                    $q->where('phone', 'like', "%$query%");
                });
        }
        $users = $users->paginate(10);
        return view('user.index', compact('users', 'you'));
    }

    public function create()
    {
        $user = new User();
        $user->detail = new UserDetail();
        $data = compact('user');

        return view('user.form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:1|max:256',
            'email' => 'required|email|max:256',
            'phone' => 'required|max:15',
        ]);
        $newUserPayload = $request->only(['name', 'email']);
        $newUserPayload['password'] = 'password';
        $user = User::create($newUserPayload);
        $user->detail()->create($request->only('phone'));
        return redirect()->route('users.index')->with('message', 'Berhasil membuat user baru: ' . $user->name);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('user.detail', compact( 'user' ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        return view('user.form', compact('user'));
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
        $validatedData = $request->validate([
            'name' => 'required|min:1|max:256',
            'email' => 'required|email|max:256',
            'phone' => 'required|max:15',
        ]);
        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();
        $user->detail->phone = $request->input('phone');
        $user->detail->save();
        $request->session()->flash('message', 'Successfully updated user');
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if($user){
            $user->delete();
        }
        return redirect()->route('users.index')->with('message', 'Berhasil menghapus ' . $user->name);
    }
}
