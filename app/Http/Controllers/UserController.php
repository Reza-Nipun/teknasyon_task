<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\User;
use App\Device;
use App\Purchase;

class UserController extends Controller
{
    public function userRegistration(Request $request){
        $email_address = $request->input('email_address');
        $password = $request->input('password');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $mobile_no = $request->input('mobile_no');
        
        $res = User::where('email_address', $email_address)->get();

        if(sizeof($res) > 0){
            $data = [
                "response" => "401",
                "message" => "Your email address is already registered!",
            ];
            
            return response()->json($data);
        }else{

            $user = new User();
            $user->email_address = $email_address;
            $user->password = Hash::make($password);
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->mobile_no = $mobile_no;
            $user->status = 1; // Initially or By default Account Status will be Active=1
            $user->save();

            $data = [
                "response" => "201",
                "message" => "Congratulations, your registration is successful!",
            ];

            return response()->json($data);
        }
    }

    public function userLogin(Request $request){
        $email_address = $request->input('email_address');
        $password = $request->input('password');

        $user = User::where(['email_address'=>$email_address, 'status'=>1])->first();

        if(!$user || !Hash::check($password, $user->password))
        {
            $data = [
                "response" => "401",
                "message" => "Failed, Email/Password is wrong!",
            ];

            return response()->json($data);
        }else{
            $u_id = $user->id;
            $app_id = $request->input('app_id');
            $language = $request->input('language');
            $os = $request->input('os');

            // Creating Client Token Start
            $date_time_utc = Carbon::now();
            $reformated_date_time = str_replace( array(" ", "/", ".", "-", ":"), '', $date_time_utc);
            
            $client_token = $u_id.$reformated_date_time;
            // Creating Client Token End

            $device = new Device();
            $device->u_id = $u_id;
            $device->app_id = $app_id;
            $device->language = $language;
            $device->os = $os;
            $device->client_token = $client_token;
            $device->save();

            $data = [
                "response" => "200",
                "message" => "Login Successful",
                "date_time_utc" => $date_time_utc,
                "client_token" => $client_token,
            ];

            return response()->json($data);
        }

    }

    public function checkUserSubscription($client_token){
        $user_info = Device::where('client_token', $client_token)
            ->leftjoin('users', 'users.id', '=', 'devices.u_id')
            ->select('devices.u_id', 'devices.app_id', 'devices.language', 'devices.os', 
                    'devices.client_token', 'users.email_address', 'users.first_name', 
                    'users.last_name', 'users.mobile_no', 'users.status')
            ->get();

            if(sizeof($user_info) > 0){
                $data = [
                    "response" => "200",
                    "message" => "Data Found",
                    "data" => $user_info,
                ];
    
                return response()->json($data);
            }else{
                $data = [
                    "response" => "404",
                    "message" => "No Data Found!",
                ];
    
                return response()->json($data);
            }
            
    }

    public function purchaseRequest(Request $request, $client_token, $receipt, $expire_date){
        $user_device_info = Device::where('client_token', $client_token)->get();
        
        $uid = $user_device_info[0]->u_id;

        $receipt_last_char = substr($receipt, -1); // returns last character of receipt

        $expire_date_time = null;

        if($receipt_last_char%2 <> 0)  
        {  
            // Odd Number
            $expire_date_time = Carbon::createFromFormat('Y-m-d H:i:s', $expire_date, 'UTC');
        } 

        $purchase = new Purchase();
        $purchase->u_id=$uid;
        $purchase->client_token=$client_token;
        $purchase->receipt=$receipt;
        $purchase->expire_date=$expire_date_time;
        $purchase->save();
        
        $data = [
            "response" => "201",
            "message" => "Congratulations, you have purhcased successfully!",
        ];

        return response()->json($data);
    }

    public function logoutDevice($client_token){
        
        Device::where('client_token', $client_token)->delete();

        $data = [
            "response" => "200",
            "message" => "Successfully Logged Out!",
        ];

        return response()->json($data);
    }

}
