<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use App\Task;
use JWTAuth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * User resource representation.
 *
 * @Resource("Users", uri="/users")
 */
class UserController extends ApiController
{
	/**
     * Obtain a list of Users.
     *
     * Get a JSON representation of the requested users
     *
     * @Get("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="string", description="A user id."),
     *      @Parameter("name", type="string", description="Partial match for name field."),
     *      @Parameter("email", type="string", description="Partial match for email field."),
     *      @Parameter("role", type="string", description="A valid role."),
     *      @Parameter("status", type="string", description="A valid status."),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok","users":""})
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list', [User::class, $request->all()]);

        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->validate($request, [
            'id' => 'numeric|min:1',
            'name' => 'min:2|max:255',
            'email' => 'min:2|max:255',
            'status' => 'validstatus',
            'role' => 'validrole',
        ]);

        $users = [];

        if($currentUser->isSuperAdmin()
            || $currentUser->isAdmin()) {

            $users = User::namePartial($request->input('name'))
                            ->emailPartial($request->input('email'))
                            ->role([$request->input('role')], $currentUser)
                            ->userId($request->input('id'))
                            ->status($request->input('status'))
                            ->paginate(5);
        }

        return response()->json([
                'status' => 'ok',
                'users' => $users,
            ], 200);
    }

	/**
     * Store a new User.
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("name", type="string", description="User name.", required=true),
     *      @Parameter("email", type="string", description="Email email.", required=true),
     *      @Parameter("password", type="string", description="User password.", required=true),
     *      @Parameter("role", type="string", description="User role.", required=true),
     *      @Parameter("status", type="string", description="User status.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->authorize('store', [User::class, $request->input('role')]);
        
        $this->validate($request, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:255|confirmed',
            'role' => 'required|validrole',
            'status' => 'required|validstatus',
        ]);

        $user = new User();
        $user->fill($request->all());
        $user->password = $request->input('password');
        $user->save();
        
        $user->setHidden(['created_at', 'updated_at', 'password']);
        
        return response()->json([
                'status' => 'ok',
                'user' => $user
            ], 200);
    }

    /**
     * Display the specified User.
     *
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="integer", description="User Id.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function view(User $user)
    {
    	$this->authorize('view', $user);
    	
        $user->setHidden(['password']);

    	return response()->json([
                'status' => 'ok',
                'user' => $user
            ], 200);
    }

    /**
     * Update the specified User.
     *
     * @Put("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("name", type="string", description="User name.", required=false),
     *      @Parameter("email", type="string", description="Email email.", required=false),
     *      @Parameter("password", type="string", description="User password.", required=false),
     *      @Parameter("role", type="string", description="User role.", required=false),
     *      @Parameter("status", type="string", description="User status.", required=false),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \App\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->authorize('update', [$user, $request->input('role')]);
        
        $this->validate($request, [
            'name' => 'min:2|max:255',
            'email' => ['email', Rule::unique('users')->ignore($user->id)],
            'password' => 'min:8|max:255|confirmed',
            'role' => 'validrole',
            'status' => 'validstatus',
        ]);

        $user->fill($request->all());
        
        $password = $request->input('password');

        if(!empty($password)) {
            $user->password = $request->input('password');
        }
        
        $user->save();
        
        //$user->setHidden(['created_at', 'updated_at', 'password']);
        
        return response()->json([
                'status' => 'ok'
            ], 200);
    }

    /**
     * Remove the specified User.
     *
     * @Delete("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="integer", description="User Id.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->authorize('delete', $user);

        if($currentUser->id === $user->id) {
            // Self account deletion
            JWTAuth::invalidate();
        }
        
        $user->delete();

        return response()->json([
                'status' => 'ok'
            ], 200);
    }
}
