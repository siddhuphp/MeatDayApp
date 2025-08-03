<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\User;
use App\Models\Profile;
use App\Models\Roles;
use App\Models\ResetPassword;
use App\Http\Resources\UserResource;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProfileRequest;
use Illuminate\Support\Carbon;

class UsersController extends Controller
{
    use HttpResponses;

    /**
     * List of users
     */
    public function index(Request $request)
    {

        $query = User::query()->with('Profile');

        // Apply search filter
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->Where('first_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%');
        }

        // Mapping of request parameters to database columns
        $filterMapping = [
            'userId' => 'user_id',
            'roleId' => 'role_id',
        ];

        // Apply dynamic filters
        foreach ($request->all() as $key => $value) {
            if (array_key_exists($key, $filterMapping)) {
                $query->where($filterMapping[$key], $value);
            }
        }

        // Apply order by filter
        if ($request->has('order_by')) {
            $orderByColumn = $request->input('order_by');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($orderByColumn, $sortOrder);
        }

        // Apply other filters as needed
        $query->leftJoin('profiles', 'users.user_id', '=', 'profiles.user_id')
            ->leftJoin('languages', 'profiles.language_id', '=', 'languages.id')
            ->select(
                'users.*',
                'profiles.user_id as profile_user_id',
                'profiles.profile_pic',
                'profiles.language_id',
                'profiles.gender',
                'profiles.updated_by',
                'profiles.created_at',
                'profiles.updated_at',
                'languages.name as language_name',
            );

        // Retrieve results
        $data = $query->paginate(10);
        return $this->success([
            'users' => UserResource::collection($data),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'first_page_url' => $data->url(1),
                'last_page_url' => $data->url($data->lastPage()),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl()
            ],
        ], '', 200);
    }

    /**
     * Get user detaills
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $query = User::where('users.user_id', $id)
            ->leftJoin('profiles', 'users.user_id', '=', 'profiles.user_id')
            ->leftJoin('languages', 'profiles.language_id', '=', 'languages.id')
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.phone_no',
                'users.email',
                'users.email_verified_at',
                'users.role_id',
                'profiles.user_id as profile_user_id',
                'profiles.profile_pic',
                'profiles.language_id',
                'profiles.gender',
                'languages.name as language_name',
            )
            ->with('Profile')
            ->first();
        // Print the SQL query

        return $this->success([
            'user' => $query ? new UserResource($query) : '',
        ], '', 200);
    }

    public function testMail()
    {
        $data = [
            'subject' => 'Welcome Subject',
            'title' => 'Welcome to Our Service',
            'message' => 'Thank you for joining us!',
        ];

        $viewName = 'emails.welcome';

        Mail::to('sid.esunuri@mycareerbots.com')->send(new TestEmail($data, $viewName));
    }

    public function verify()
    {
        if (!empty($_GET['token']) && is_string($_GET['token'])) {
            $user = User::where('user_id', $_GET['token'])->first();

            if (empty($user)) {
                return $this->error([], 'User not found', 401);
            }

            // Check if user is already verified
            if (!empty($user['email_verified_at'])) {
                return response()->make("
                <html>
                    <head>
                        <script>
                            let countdown = 5;
                            function updateCountdown() {
                                document.getElementById('countdown').innerText = countdown;
                                countdown--;
                                if (countdown < 0) {
                                    window.location.href = 'https://press-club.itprior.com/login';
                                } else {
                                    setTimeout(updateCountdown, 1000);
                                }
                            }
                            window.onload = updateCountdown;
                        </script>
                    </head>
                    <body style='text-align:center; font-family:Arial, sans-serif; margin-top:50px;'>
                        <h2 style='color:blue;'>You are already verified!</h2>
                        <p>Redirecting to login in <span id='countdown'>5</span> seconds...</p>
                    </body>
                </html>
            ", 200, ['Content-Type' => 'text/html']);
            }

            // Update verification status
            $data = [
                'email_verified_at' => date('Y-m-d H:i:s'),
            ];
            User::where('user_id', $user['user_id'])->update($data);

            // Send email only if the user has first name and email
            if ($user['first_name'] && $user['email']) {
                $this->regstrSuccMail($user['email'], $user['first_name']);
            }

            // Return success message with countdown
            return response()->make("
            <html>
                <head>
                    <script>
                        let countdown = 5;
                        function updateCountdown() {
                            document.getElementById('countdown').innerText = countdown;
                            countdown--;
                            if (countdown < 0) {
                                window.location.href = 'https://press-club.itprior.com/login';
                            } else {
                                setTimeout(updateCountdown, 1000);
                            }
                        }
                        window.onload = updateCountdown;
                    </script>
                </head>
                <body style='text-align:center; font-family:Arial, sans-serif; margin-top:50px;'>
                    <h2 style='color:green;'>User verified successfully!</h2>
                    <p>Redirecting to login in <span id='countdown'>5</span> seconds...</p>
                </body>
            </html>
        ", 200, ['Content-Type' => 'text/html']);
        }
    }


    private function regstrSuccMail($email, $username)
    {
        $data = [
            'subject' => 'Registration Successful!',
            'username' => $username,
            'loginLink' => 'https://press-club.itprior.com/login'
        ];

        $viewName = 'emails.email_register_success';

        Mail::to($email)
            ->bcc([config('constants.BCC')]) // Add BCC recipients
            ->send(new TestEmail($data, $viewName));
    }

    public function resetPassword(Request $request)
    {
        $fields = $request->validate([
            "email" => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $fields['email'])->first();
        if (empty($user)) {
            return $this->error([
                [],
            ], 'Email not registered', 401);
        }

        $existingToken = ResetPassword::where('email', $user->email)->first();
        if ($existingToken) {
            // Decide how to handle the existing token. For example:
            $existingToken->delete(); // Deletes the old token before creating a new one.
        }

        // Generate a token
        $token = Str::random(60) . uniqid() . uniqid(date('YMDHIS'));

        ResetPassword::create([
            'email' => $user['email'],
            'token' => $token,
            'created_at' => now(),
        ]);

        $resetLink = 'https://press-club.itprior.com/setPassword/' . $token;

        $data = [
            'subject' => 'Reset Your Password',
            'title' => 'Reset Your Password',
            'resetLink' => $resetLink,
            'username' => $user['first_name']
        ];

        $viewName = 'emails.email_resetPassword';

        Mail::to($user['email'])->send(new TestEmail($data, $viewName));

        return $this->success([
            [],
        ], 'Reset password request received, Please check your registered mail to set new password', 200);
    }

    public function setNewPassword(Request $request, $code)
    {
        if (!empty($code) && is_string($code)) {
            $user = ResetPassword::where('token', $code)->first();
            if (empty($user)) {
                return $this->error([
                    [],
                ], 'Link is expired, Try again', 401);
            }

            $fields = $request->validate([
                "password" => 'required|string|confirmed'
            ]);

            $data = [
                'password' => bcrypt($request->password),
            ];
            User::where('email', $user['email'])->update($data);

            ResetPassword::where('token', $code)->delete();

            return $this->success([
                '',
            ], 'Password reset successfully!', 200);
        }

        return $this->error([
            [],
        ], 'Bad Request', 401);
    }

    public function store(StoreUserRequest $request)
    {
        $request->validated();
        $imagePath = '';
        if (!empty($request->file('user_image'))) {
            $image = $request->file('user_image');
            $imagePath = $image->store('user_images', 'public');
        }
        $user = User::create([
            'first_name' => ucfirst($request->first_name),
            'last_name' => ucfirst($request->last_name),
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'phone_no' => $request->phone_no,
        ]);

        $update = Profile::create([
            'profile_pic' => $imagePath,
            'language_id' => $request->language_id,
            'gender' => $request->gender,
            'user_id' => $user->user_id,
        ]);

        if (!$user) {
            Log::error('User creation failed.', ['fields' => $request]);
            return $this->error(['message' => 'User creation failed.'], '', 500);
        }

        //send mail
        // $this->regstrMail($request->email, $user->user_id);

        return $this->success([
            'user' => $user,
        ], 'User created successfully!', 201);
    }

    /**
     * Delete user.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        $role = "";
        if (!empty($user->user_id)) {
            $roleData = Roles::find($user->role_id);
            if (!empty($roleData->fixed_role)) {
                $role = $roleData->fixed_role;
            }
        }

        if (!empty($user->user_id) && ($role != 'admin')) {
            User::where('user_id', $id)->delete();
            $this->deleteProfileImage($request->user()->user_id, $id, true);
        }
        return $this->success([], 'User deleted successfully!', 204);
    }

    /**
     * Delete user image.
     */
    public function destroyImage(Request $request, $id)
    {
        $this->deleteProfileImage($request->user()->user_id, $id);
        return $this->success([], 'Image deleted successfully!', 204);
    }

    private function deleteProfileImage($updatedBy, $id, $deleteProfile = false)
    {
        $image = Profile::find($id);

        if (!empty($image->profile_pic)) {
            Profile::where('user_id', $image->user_id)->update(
                [
                    'profile_pic' => '',
                    'updated_by' => $updatedBy,
                ]
            );
            Storage::delete("public/" . $image->profile_pic);
        }

        if (!empty($image) && $deleteProfile) {
            $image->delete();
        }
    }

    /**
     * Update profile details Siddhu edi pending
     */
    public function update(StoreProfileRequest $request, $id)
    {
        $request->validated();

        $data = [];
        if (!empty($request->language_id)) {
            $data['language_id'] = $request->language_id;
        }
        if (!empty($request->gender)) {
            $data['gender'] = $request->gender;
        }
        if (!empty($data)) {
            $data['updated_by'] = $request->user()->user_id;
            $data['updated_at'] = now();
            Profile::updateOrCreate(
                ['user_id' => $id], // Condition
                $data // Data
            );
        }

        $userData = [];
        if (!empty($request->first_name)) {
            $userData['first_name'] = $request->first_name;
        }

        if (!empty($request->last_name)) {
            $userData['last_name'] = $request->last_name;
        }

        if (!empty($request->role_id)) {
            $userData['role_id'] = $request->role_id;
        }

        if (!empty($request->status)) {
            $userData['status'] = $request->status;
        }

        // if (!empty($request->phone_no)) {
        //     $userData['phone_no'] = $request->phone_no;
        // }

        if (!empty($userData)) {
            User::where('user_id', $id)->update($userData);
        }

        if (empty($data) && empty($userData)) {
            return $this->error([], 'Atleast one field is required', 400);
        }

        return $this->success([], 'Profile details are updated successfully!', 201);
    }

    /**
     * Update profile picture
     */
    public function changeProfileImage(Request $request)
    {
        $request->validate([
            'user_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'user_id' => 'required|string|exists:users,user_id',
        ]);

        $imagePath = '';
        if (!empty($request->file('user_image'))) {
            $image = $request->file('user_image');
            $this->deleteProfileImage($request->user()->user_id, $request->user_id);
            $imagePath = $image->store('user_images', 'public');
        }

        $profile = Profile::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'profile_pic' => $imagePath,
            ]
        );
        return $this->success([], 'Profile pictue is updated successfully!', 201);
    }

    /**
     * Change the password for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        // Validate the request data
        $fields = $request->validate([
            'old_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'max:30',
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*?&_-]/', // must contain at least one special character
                'confirmed',
            ],
        ], [
            'new_password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&_-).',
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        // Get the currently authenticated user
        $user = Auth::user();

        // Check if the old password is correct
        if (!Hash::check($fields['old_password'], $user->password)) {
            return $this->error([], 'The provided old password does not match our records.', 203);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($fields['new_password']),
        ]);

        return $this->success([], 'Password changed successfully!', 200);
    }
}
