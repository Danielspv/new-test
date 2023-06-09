<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function index(){

        $users = User::paginate(5);

        if ($users == null) { //VALIDATE THAT THE USER EXISTS
            return response()->json([
                'status' => 'error',
                'code' =>  404,
                'message' => 'Users not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' =>  200,
            'message' => 'All users',
            'user' => $users
        ], 200);
    }
    public function login(Request $request)
    {
        //Login validation
        $validate = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Incorrect data validation',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $user = User::where('email', $request->input('email'))->first();

        //Validating if the password is the same as the encrypted Hash password
        $validatePassword = Hash::check($request->input('password'), $user->password);

        if ($validatePassword) {
            $token = Str::random(80);
            $user->api_token = hash('sha256', $token);
            $user->update();

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Login success',
                'user' => [
                    'sub' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'iat' => time(),
                    'exp' => time() + (7 * 60),
                    'api_token' =>  $token
                ]
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Password incorrect'
            ];
        }

        //Returning response
        return response()->json($data, $data['code']);
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:100',
            'email' => 'required|email:rfc,dns|unique:users|max:150',
            'password' => 'required|confirmed|min:8|max:16'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Incorrect data validation',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'status' => 'success',
            'code' =>  200,
            'message' => 'Successfully registered user',
            'user' => $user
        ], 200);
    }

    public function show($id){

        $user = User::find($id);
        if ($user == null) { //VALIDATE THAT THE USER EXISTS
            return response()->json([
                'status' => 'error',
                'code' =>  404,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' =>  200,
            'message' => 'User successfully deleted',
            'user' => $user
        ], 200);

    }

    /**
     * Función para actualizar el usuario
     * @param Number
     * @return Json
     */
    public function update(Request $request, $id)
    {
       $user = User::find($id);

       $validate = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:100',
            'email' => 'required|email:rfc,dns|max:150|unique:users,email,'.$id,
            'password' => 'required|confirmed|min:8|max:16'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Incorrect data validation',
                'errors' =>  $validate->errors()
            ], 400);
        }
        
        if ($user == null) {
            return response()->json([
                'status' => 'error',
                'code' =>  404,
                'message' => 'User not found'
            ], 404);
        }

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'status' => 'success',
            'code' =>  200,
            'message' => 'Successfully registered user',
            'user' => $user
        ], 200);
    }

    public function delete($id){

        $user = User::find($id);
        if ($user == null) { //VALIDATE THAT THE USER EXISTS
            return response()->json([
                'status' => 'error',
                'code' =>  404,
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'code' =>  200,
            'message' => 'User successfully deleted'
        ], 200);
    }
}
