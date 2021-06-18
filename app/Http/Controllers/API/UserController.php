<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password],
            ]); // validasi lewat controller

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User Registered');
        } catch (Exception $error) {
            //throw $th;
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authenticatoin Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized',
                ], 'Authentication Failed', 500);
            }
            $user = User::where('email', $request->email)->first();

            // MENGECEK PASSWORD YANG DI INPUT DENGAN PASSWORD YANG DARI DATABASE
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }
            // MEMBUAT TOKEN BARU
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
            //throw $th;
        }
    }


    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data Profile user berhasil di ambil');
    }

    public function updateProfile(Request $request)
    {
        // tanpa validate;
        $data = $request->all();
        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');

        // dengan validate 
        // ! udah bisa cuman harus bener bener uniq / belum pernah buat username sama gmail nya jadi hati hati 
        // ! dan disat update harus semuanya karena belum ada validasi di setiap field nya
        // try {
        //     $request->validate([
        //         'name' => ['required', 'string', 'max:255'],
        //         'username' => ['required', 'string', 'max:255', 'unique:users'],
        //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        //         'phone' => ['nullable', 'string', 'max:255'],
        //     ]);

        //     // var_dump($request);

        //     $data = $request->all();
        //     $user = Auth::user();
        //     $user->update($data);


        //     return ResponseFormatter::success($user, 'Profile Updated');
        // } catch (Exception $error) {

        //     return ResponseFormatter::error([
        //         'message' => 'Something went wrong',
        //         'error' => $error
        //     ], 'Authenticatoin Failed', 500);
        // }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }
}
