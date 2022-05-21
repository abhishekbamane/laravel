<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

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

            return response()->json(['login' => 'login successfully', 'user' => $user]);
        }
    }

    public function store(Request $request)
    {
        $name = $request->username;
        $email = $request->email;
        $password = Hash::make($request->password);

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->role = 'User';
        $user->save();

        return response()->json(['create' => 'user created']);
    }

    public function allUsers(){
        $auth = Auth::user();
        if($auth['role'] == 'Admin'){
            $users = User::all();
            $user =  Auth::user();
            return response()->json(['users' => $users, 'user' => $user]);
        }
        return response()->json(['failed' => 'You need to login first']);
    }

    public function editUser(Request $request){
        $editUser = User::where('id', $request->id)->get();
        $user =  Auth::user();
        return response()->json(['editUser' => $editUser, 'user' => $user]);
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

        return response()->json(['updated' => 'User updated successfully']);
    }

    public function delete(Request $request){
        User::where('id', $request->id)->delete();
        return response()->json(['deleted' => 'User deleted successfully']);
    }


    public function index()
    {
        $check = Auth::check();
        if($check == 1){
            $user =  Auth::user();
            $audit = LogActivity::all();
            $userLog = LogActivity::where('id', $user['auditId'])->get();
            return response()->json(['audit' => $audit, 'user' => $user, 'userLog' => $userLog] );        
        }
        return response()->json(['failed' => 'You need to login first']);
    }

    public function logout(Request $request){        
        $logoutAt = [
            'logout_at' => Carbon::now()
        ];
        LogActivity::where('id', $request->logoutAt)->update($logoutAt);
        Auth::logout();
        return response()->json(['logged out']);
    }
}
