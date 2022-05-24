<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function postLogin(Request $request){
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if(Auth::attempt($data)){
            $user =  Auth::user();
            $log = [
                'ip' => $request->ip(),
                'agent' => $request->header('user-agent'),
                'url' => $request->fullUrl()
            ];
            $audit = new LogActivity();
            $audit->user_type = 'App/User';
            $audit->user_id = $user->id;
            $audit->auditable_type = 'App/User';
            $audit->auditable_id = $user->id;
            $audit->url = $log['url'];
            $audit->ip_address = $log['ip'];
            $audit->user_agent = $log['agent'];
            $audit->event = 'login';
            $audit->tags = $user->role;

            $audit->save();

            $logId = [
                'auditId' => $audit->id
            ];
            User::where('id',$user->id)->update($logId);

            return redirect()->route('dashboard');
        }
    }

    public function register()
    {
        return view('register');
    }

    public function store(Request $request)
    {
        // $this->validate($request, [
        //     'name' => 'required',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required',
        // ]);

        $name = $request->username;
        $email = $request->email;
        $password = Hash::make($request->password);

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->role = 'Admin';
        $user->save();

        return redirect('login')->with(['registered' => 'Registered successfully.']);
    }

    public function allUsers(){
        $check = Auth::check();
        if($check == 1){
            $auth = Auth::user();
            if($auth['role'] == 'Admin'){
                $users = User::all();
                $user =  Auth::user();
                return view('allUsers',['users' => $users, 'user' => $user]);
            }
            return back()->with(['user' => 'Only Admin can access this.']);
        }
        return redirect('login')->with(['failed' => 'You need to login first']);
    }

    public function editUser(Request $request){
        $editUser = User::where('id', $request->id)->get();
        $user =  Auth::user();
        return view('editUser',['editUser' => $editUser, 'user' => $user]);
    }

    public function update(Request $request)
    {
        // $this->validate($request, [
        //     'name' => 'required',
        //     'email' => 'required|email|unique:users,email',
        // ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ];
        
        User::where('id',$request->id)->update($updateData);

        return redirect('allUsers');
    }

    public function delete(Request $request){
        $id = Auth::id();
        if($id != $request->id){
            User::where('id', $request->id)->delete();
            return redirect('allUsers');
        }
        return back()->with(['notDelete' => 'OOPPPSSSS!!!! You cant delete yourself.']);
    }


    public function index()
    {
        $check = Auth::check();
        if($check == 1){
            $user =  Auth::user();
            $audit = LogActivity::all();
            $userLog = LogActivity::where('id', $user['auditId'])->get();
            // return $user['auditId'];
            return view('dashboard',['audit' => $audit, 'user' => $user, 'userLog' => $userLog] );        
        }
        return redirect('login')->with(['failed' => 'You need to login first']);
    }

    public function logout(Request $request){        
        $logoutAt = [
            'logout_at' => Carbon::now()
        ];
        LogActivity::where('id', $request->logoutAt)->update($logoutAt);
        Auth::logout();
        return redirect('login')->with(['loggedout' => 'Logged out successfully.']);
    }
}
