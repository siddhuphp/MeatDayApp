<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Hash;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreRegisterRequest;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use HttpResponses;

    public function register(StoreRegisterRequest $request)
    {
        $request->validated();
        $user = User::create([
            'first_name' => ucfirst($request->first_name),
            'last_name' => ucfirst($request->last_name),
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'password' => bcrypt($request->password),
            'user_type' => 'normal',
            'role_id' => 'f41f0ea4-535f-48a0-89bc-53e049b0ab76' // User role
        ]);

        if (!$user) {
            Log::error('User creation failed.', ['fields' => $request]);
            return $this->error(['message' => 'User creation failed.'], '', 500);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        // Send verification email
        $this->regstrMail($request->email, $user->user_id, ucfirst($request->first_name));

        return $this->success([
            'user' => [
                'user_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_no' => $user->phone_no,
                'user_type' => $user->user_type,
                'role' => $user->getRoleName(),
                'fixed_role' => $user->getFixedRole(),
                'is_admin' => $user->isAdmin(),
                'is_content_creator' => $user->isContentCreator(),
                'is_manager' => $user->isManager(),
                'is_tester' => $user->isTester(),
                'is_user' => $user->isUser(),
            ],
            'token' => $token,
        ], 'User registered successfully!', 201);
    }

    public function registerAdmin(Request $request) {
        // Only existing admins can register new admins
        if (!$request->user() || !$request->user()->isAdmin()) {
            return $this->error(['message' => 'Unauthorized access. Admin role required to register new admins.'], 'Unauthorized', 403);
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,role_id'
        ]);

        $admin = User::create([
            'first_name' => ucfirst($request->first_name),
            'last_name' => ucfirst($request->last_name),
            'email' => $request->email,
            'phone_no' => $request->phone_no ?? null,
            'password' => bcrypt($request->password),
            'user_type' => 'admin',
            'role_id' => $request->role_id,
            'email_verified_at' => now() // Auto verify admin accounts
        ]);

        $token = $admin->createToken('adminToken')->plainTextToken;

        return $this->success([
            'user' => [
                'user_id' => $admin->user_id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'email' => $admin->email,
                'phone_no' => $admin->phone_no,
                'user_type' => $admin->user_type,
                'role' => $admin->getRoleName(),
                'fixed_role' => $admin->getFixedRole(),
                'is_admin' => $admin->isAdmin(),
                'is_content_creator' => $admin->isContentCreator(),
                'is_manager' => $admin->isManager(),
                'is_tester' => $admin->isTester(),
                'is_user' => $admin->isUser(),
            ],
            'token' => $token
        ], 'Admin registered successfully', 201);
    }

    public function googleLogin(Request $request)
    {

        $where = ["email" => $request->email];
        $user = User::where($where)->first();

        if ($user) {
            $updateUserData = [
                'first_name' => ucfirst($request->name),
                'email' => $request->email,
                'user_type' => 'google'
            ];

            $userUpdate = User::where('user_id', $user->user_id)->update($updateUserData);
        } else {
            $userCreateData = [
                'first_name' => ucfirst($request->name),
                'last_name' => '',
                'email' => $request->email,
                'phone_no' => null,
                'password' => '',
                'role_id' => 'f41f0ea4-535f-48a0-89bc-53e049b0ab76',
                'user_type' => 'google'
            ];

            $user = User::create($userCreateData);
        }


        if (!$user) {
            return $this->error([
                'message' => "Login failed"
            ], '', 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return $this->success([
            'user' => [
                'user_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_no' => $user->phone_no,
                'user_type' => $user->user_type,
                'role' => $user->getRoleName(),
                'fixed_role' => $user->getFixedRole(),
                'is_admin' => $user->isAdmin(),
                'is_content_creator' => $user->isContentCreator(),
                'is_manager' => $user->isManager(),
                'is_tester' => $user->isTester(),
                'is_user' => $user->isUser(),
            ],
            'token' => $token
        ], '', 200);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            "email" => 'required|email',
            "password" => 'required|string|min:6|max:30',
        ]);

        // Check Email
        $where = ["email" => $fields['email']];
        $user = User::where($where)->first();

        //check user exist
        if (empty($user)) {
            return $this->error([
                'message' => "Bad credentials, Invalid user"
            ], 'Bad credentials, Invalid user', 401);
        }

        // Check if email is verified
        if (empty($user->email_verified_at)) {
            return $this->error([
                'message' => "Please activate your account through the registered email"
            ], 'Please activate your account through the registered email', 401);
        }

        // Check Password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return $this->error([
                'message' => "Bad credentials"
            ], 'Bad credentials', 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return $this->success([
            'user' => [
                'user_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_no' => $user->phone_no,
                'user_type' => $user->user_type,
                'role' => $user->getRoleName(),
                'fixed_role' => $user->getFixedRole(),
                'is_admin' => $user->isAdmin(),
                'is_content_creator' => $user->isContentCreator(),
                'is_manager' => $user->isManager(),
                'is_tester' => $user->isTester(),
                'is_user' => $user->isUser(),
            ],
            'token' => $token
        ], '', 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success([
            'message' => 'logged out'
        ]);
    }

    private function regstrMail($email, $token, $username)
    {
        $verificationLink = route('verify', ['token' => $token]);
        $data = [
            'subject' => 'Email Verification',
            'verification_link' => $verificationLink,
            'username' => $username
        ];

        $viewName = 'emails.register';

        $mail = Mail::to($email);
        
        // Only add BCC if the constant is defined and not null
        $bccRecipients = config('constants.BCC');
        if ($bccRecipients) {
            $mail->bcc([$bccRecipients]);
        }
        
        $mail->send(new TestEmail($data, $viewName));
    }
}
