<?php

namespace App\Http\Controllers;

use App\Models\{AuditLog, User};
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="KMC M&E System API Documentation",
 *      description="Kibaha Municipal Council Monitoring and Evaluation System REST API Documentation",
 *      @OA\Contact(
 *          email="support@kmc.go.tz",
 *          name="KMC Development Team"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Development Server"
 * )
 *
 * @OA\Server(
 *      url="https://kmc.go.tz/api",
 *      description="Production Server"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Login with email and password to get the authentication token",
 *     name="Bearer",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="BearerAuth"
 * )
 *
 * AuthController
 * 
 * Handles authentication operations for the KMC M&E System
 */
class AuthController extends Controller
{
    use ApiResponseTrait;
    
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     * 
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Authenticate user and return token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@kmc.go.tz"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="1|abc123def456..."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            // API request
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Web request
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been deactivated. Please contact administrator.',
                    ], 403);
                }
                
                return back()
                    ->withErrors(['email' => 'Your account has been deactivated.'])
                    ->withInput($request->except('password'));
            }
            
            $user->updateLastLogin();

            // Log successful login
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'USER_LOGIN',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'new_values' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // API request - return token
            if ($request->expectsJson() || $request->is('api/*')) {
                $token = $user->createToken('mobile_app')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'full_name' => $user->full_name,
                            'position' => $user->position,
                            'department' => $user->department,
                            'phone' => $user->phone,
                            'organizational_unit' => $user->organizationalUnit ? [
                                'id' => $user->organizationalUnit->id,
                                'name' => $user->organizationalUnit->name,
                                'type' => $user->organizationalUnit->type,
                            ] : null,
                            'roles' => $user->roles->pluck('name'),
                            'is_active' => $user->is_active,
                        ],
                        'token' => $token,
                        'permissions' => $user->getAllPermissions(),
                    ],
                ], 200);
            }
            
            // Web request - redirect to dashboard
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Authentication failed
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return back()
            ->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ])
            ->withInput($request->except('password'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log logout
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'USER_LOGOUT',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // API request - revoke token
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($user) {
                // Revoke current token
                $request->user()->currentAccessToken()->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        }

        // Web request - destroy session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        if (!Auth::check() || !Auth::user()->hasPermission('create_users')) {
            abort(403, 'Unauthorized action.');
        }

        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        if (!Auth::check() || !Auth::user()->hasPermission('create_users')) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'full_name' => 'required|string|max:255',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'org_unit_id' => $request->org_unit_id,
            'department' => $request->department,
            'position' => $request->position,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Show the profile form
     */
    public function showProfileForm()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update($request->only([
            'full_name',
            'email',
            'phone',
            'department',
            'position',
        ]));

        return back()
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show the change password form
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change request
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log password change
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'PASSWORD_CHANGED',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Get authenticated user info (API)
     * 
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Get current authenticated user information",
     *     tags={"Authentication"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'position' => $user->position,
                'department' => $user->department,
                'org_unit' => $user->organizationalUnit?->name,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions(),
                'is_active' => $user->is_active,
                'last_login' => $user->last_login_at,
            ],
        ]);
    }

    /**
     * Refresh authentication token (API)
     * 
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Refresh authentication token",
     *     tags={"Authentication"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|newtoken123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        return response()->json([
            'token' => $request->user()->createToken('auth_token')->plainTextToken,
        ]);
    }

    /**
     * Check if user is authenticated (API)
     * 
     * @OA\Get(
     *     path="/api/v1/auth/check",
     *     summary="Check authentication status",
     *     tags={"Authentication"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authentication status",
     *         @OA\JsonContent(
     *             @OA\Property(property="authenticated", type="boolean", example=true),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="authenticated", type="boolean", example=false),
     *             @OA\Property(property="user", type="null", example=null)
     *         )
     *     )
     * )
     */
    public function check(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::check() ? [
                'id' => Auth::user()->id,
                'name' => Auth::user()->full_name,
                'roles' => Auth::user()->roles->pluck('name'),
            ] : null,
        ]);
    }
}
