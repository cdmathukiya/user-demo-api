<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\Invitation;
use App\Mail\Verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {

    }

    public function sendInvitation(Request $request)
    {
        $inputs = $request->all();

        $validator = \Validator::make($inputs, [
            'email' => 'required|email',
        ],
            [
                'email.required' => 'Please enter email address.',
                'email.email'    => 'Please enter valid email address.',
            ]);

        if ($validator->fails()) {
            $response = [
                'code'    => 0,
                'message' => "Validation error",
                'errors'  => $validator->errors()->toArray(),
                'result'  => [],
            ];
            return response()->json($response);
        }

        $user = User::where('email', $inputs['email'])->first();

        if (empty($user)) {
            $mailData = [
                'email'          => $inputs['email'],
                'invitationLink' => url('api/register') . "?email=" . $inputs['email'],
            ];

            try {
                \Mail::to($inputs['email'])->send(new Invitation($mailData));
            } catch (\Exception $ex) {
                \Log::info($ex->getMessage());
            }

            $response = [
                'code'    => 1,
                'message' => "Invitation send successfully!",
                'result'  => [],
            ];
        } else {
            $response = [
                'code'    => 0,
                'message' => "Email Already register with us.",
                'result'  => [],
            ];
        }

        return response()->json($response);
    }

    public function register(Request $request)
    {
        $inputs = $request->all();

        $validator = \Validator::make($inputs, [
            'user_name' => 'required|unique:users|min:4|max:20',
            'email'     => 'required|email|unique:users',
            'password'  => 'required',
        ],
            [
                'username.required' => 'Please enter username.',
                'username.min'      => 'Username must be between 4 to 20 character.',
                'username.max'      => 'Username must be between 4 to 20 character.',
                'email.required'    => 'Please enter email address.',
                'email.email'       => 'Please enter valid email address.',
                'password.required' => 'Please enter password.',
            ]);

        if ($validator->fails()) {
            $response = [
                'code'    => 0,
                'message' => "Validation error",
                'errors'  => $validator->errors()->toArray(),
                'result'  => [],
            ];
            return response()->json($response);
        }
        $inputs['otp']           = random_int(100000, 999999);
        $inputs['password']      = Hash::make($inputs['password']);
        $inputs['is_verified']   = 1;
        $inputs['registered_at'] = date('Y-m-d H:i:s');

        $user = User::create($inputs);

        if ($user) {
            try {
                \Mail::to($inputs['email'])->send(new Verification($inputs));
            } catch (\Exception $ex) {
                \Log::info($ex->getMessage());
            }

            $response = [
                'code'    => 1,
                'message' => "Registration is successfull! Please verify your email with otp we have send it to your email",
                'result'  => [],
            ];
        } else {
            $response = [
                'code'    => 0,
                'message' => "Something went wrong! please try again",
                'result'  => [],
            ];
        }

        return response()->json($response);
    }

    public function verify(Request $request)
    {
        $inputs    = $request->all();
        $validator = \Validator::make($inputs, [
            'email' => 'required|email',
            'otp'   => 'required',
        ],
            [
                'email.required' => 'Please enter email address.',
                'email.email'    => 'Please enter valid email address.',
                'otp.required'   => 'Please enter otp.',
            ]);

        if ($validator->fails()) {
            $response = [
                'code'    => 0,
                'message' => "Validation error",
                'errors'  => $validator->errors()->toArray(),
                'result'  => [],
            ];
            return response()->json($response);
        }

        $user = User::where('email', $inputs['email'])->first();

        if (!empty($user)) {
            if (!empty($user->otp) && $user->otp == $inputs['otp']) {

                $user->otp           = "";
                $user->is_verified   = 1;
                $user->registered_at = date('Y-m-d H:i:s');
                $user->save();

                $response = [
                    'code'    => 1,
                    'message' => "Your account is successfully verify",
                    'result'  => [],
                ];
            } else {
                $response = [
                    'code'    => 0,
                    'message' => "Invalid otp!",
                    'result'  => [],
                ];
            }
        } else {
            $response = [
                'code'    => 0,
                'message' => "User Not found!",
                'result'  => [],
            ];
        }
        return response()->json($response);
    }

    public function login(Request $request)
    {
        $inputs = $request->all();

        $validator = \Validator::make($inputs, [
            'user_name' => 'required',
            'password'  => 'required',
        ],
            [
                'username.required' => 'Please enter username.',
                'password.required' => 'Please enter password.',
            ]);

        if ($validator->fails()) {
            $response = [
                'code'    => 0,
                'message' => "Validation error",
                'errors'  => $validator->errors()->toArray(),
                'result'  => [],
            ];
            return response()->json($response);
        }

        $user = User::where('user_name', $inputs['user_name'])->first();

        if (!empty($user)) {
            if (Hash::check($inputs['password'], $user->password)) {

                if ($user->is_verified == 1) {
                    $user->access_token = $user->getAccessToken();

                    $response = [
                        'code'    => 1,
                        'message' => "Login successfull!",
                        'result'  => new UserResource($user),
                    ];
                } else {
                    $response = [
                        'code'    => 0,
                        'message' => "Please contact to admin to verify your account",
                        'result'  => [],
                    ];
                }
            } else {
                $response = [
                    'code'    => 0,
                    'message' => "Invalid password!",
                    'result'  => [],
                ];
            }
        } else {
            $response = [
                'code'    => 0,
                'message' => "User Not found with this username!",
                'result'  => [],
            ];
        }
        return response()->json($response);

    }

    public function updateProfile(Request $request)
    {
        $user = \Auth::user();

        if (!empty($user)) {
            $inputs = $request->all();

            $validator = \Validator::make($inputs, [
                'name'      => 'required',
                'user_name' => 'required|unique:users,user_name,' . $user->id . '|min:4|max:20',
                'email'     => 'required|email|unique:users,email,' . $user->id,
                'avatar'    => 'nullable|image|dimensions:min_width=256,min_height=256|mimes:jpg,jpeg,png,bmp,webp',
            ],
                [
                    'name.required'     => 'Please enter name.',
                    'username.required' => 'Please enter username.',
                    'email.required'    => 'Please enter email address.',
                    'email.email'       => 'Please enter valid email address.',
                    'avatar.image'      => 'Please upload valid image.',
                    'avatar.dimensions' => 'Image size must be 256px * 256px.',
                    'avatar.mimes'      => 'Image must be jpg,jpeg,png,bmp,webp.',
                ]);

            if ($validator->fails()) {
                $response = [
                    'code'    => 0,
                    'message' => "Validation error",
                    'errors'  => $validator->errors()->toArray(),
                    'result'  => [],
                ];
                return response()->json($response);
            }

            $tempFile = $request->file('avatar');
            $fileName = !empty($inputs['old_avatar']) ? $inputs['old_avatar'] : "";

            if (!empty($tempFile)) {
                //Move Uploaded tempFile
                $fileName        = md5(Str::slug($tempFile->getClientOriginalName(), "-")) . '_' . $user->id . '_' . time() . "." . $tempFile->getClientOriginalExtension();
                $uploadPath      = env('UPLOAD_PATH');
                $destinationPath = public_path($uploadPath) . 'avatar';
                $tempFile->move($destinationPath, $fileName);
            }

            $inputs['avatar'] = $fileName;
            $user->fill($inputs);
            $user->save();

            $response = [
                'code'    => 1,
                'message' => "Profile updated successfull!",
                'result'  => new UserResource($user),
            ];
        } else {
            $response = [
                'code'    => 0,
                'message' => "User Not found with this username!",
                'result'  => [],
            ];
        }
        return response()->json($response);
    }
}
