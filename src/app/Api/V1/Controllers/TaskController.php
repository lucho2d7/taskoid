<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use App\Task;
use JWTAuth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Task resource representation.
 *
 * @Resource("Tasks", uri="/tasks")
 */
class TaskController extends ApiController
{
	/**
     * Obtain a list of Tasks.
     *
     * Get a JSON representation of the requested tasks
     *
     * @Get("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("title", type="string", description="Return only Tasks with partial match for title field."),
     *      @Parameter("description", type="string", description="Return only Tasks with partial match for description field."),
     *      @Parameter("due_date_from", type="date", description="Return only Tasks with due date after or equal than from the specified date."),
     *      @Parameter("due_date_to", type="date", description="Return only Tasks with due date before or equal than from the specified date."),
     *      @Parameter("completed", type="boolean", description="Return only Tasks with specified completed value"),
     *      @Parameter("created_at_from", type="date", description="Return only Tasks with created date after or equal than from the specified date."),
     *      @Parameter("created_at_to", type="date", description="Return only Tasks with created date before or equal than from the specified date."),
     *      @Parameter("updated_at_from", type="date", description="Return only Tasks with updated date after or equal than from the specified date."),
     *      @Parameter("updated_at_to", type="date", description="Return only Tasks with updated date before or equal than from the specified date."),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok","tasks":""})
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list', [Task::class, $request->all()]);

        $this->validate($request, [
            'title' => 'min:2|max:1020',
            'description' => 'min:2|max:1020',
            'due_date_from' => 'date_format:Y-m-d H:i:s',
            'due_date_to' => 'date_format:Y-m-d H:i:s',
            'completed' => 'boolean',
            'created_at_from' => 'date_format:Y-m-d H:i:s',
            'created_at_to' => 'date_format:Y-m-d H:i:s',
            'updated_at_from' => 'date_format:Y-m-d H:i:s',
            'updated_at_to' => 'date_format:Y-m-d H:i:s',
            'user_id' => 'string|min:24|validuserid',
            'page' => 'integer|min:1',
        ]);

        $currentUser = JWTAuth::parseToken()->authenticate();

        $user_id = ($currentUser->isSuperAdmin() || $currentUser->isAdmin())
                    ? $request->input('user_id')
                    : $currentUser->_id;

        $tasks = Task::userId($user_id)
                        ->titlePartial($request->input('title'))
                        ->descriptionPartial($request->input('description'))
                        ->completed($request->has('completed'), $request->input('completed'))
                        ->dueDateFrom($request->input('due_date_from'))
                        ->dueDateTo($request->input('due_date_to'))
                        ->createdAtFrom($request->input('created_at_from'))
                        ->createdAtTo($request->input('created_at_to'))
                        ->updatedAtFrom($request->input('updated_at_from'))
                        ->updatedAtTo($request->input('updated_at_to'))
                        ->orderBy('date', 'desc')
                        ->orderBy('time', 'desc')
                        ->paginate(5);

        return response()->json([
                'status' => 'ok',
                'tasks' => $tasks
            ], 200);
    }

	/**
     * Store a new Task.
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("title", type="string", description="Task title.", required=true),
     *      @Parameter("description", type="string", description="Task description.", required=true),
     *      @Parameter("due_date", type="date", description="Task due date.", required=true),
     *      @Parameter("completed", type="boolean", description="Task completed status.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->authorize('store', [Task::class, $request->input('user_id')]);
        
        $this->validate($request, [
            'title' => 'required|min:2|max:1020',
            'description' => 'required|min:2|max:1020',
            'completed' => 'boolean',
            'due_date' => 'required|date_format:Y-m-d H:i:s',
            'user_id' => 'string|min:24|validuserid',
        ]);
        
        $task = new Task();
        $task->fill($request->all());

        $task->user_id = ($currentUser->isUser() || !$request->has('user_id'))
                            ? $currentUser->id
                            : $request->input('user_id');

        $task->save();
        
        $task->setHidden(['user']);
        
        return response()->json([
                'status' => 'ok',
                'task' => $task
            ], 200);
    }

    /**
     * Display the specified Task.
     *
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="integer", description="Task Id.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function view(Task $task)
    {
    	$this->authorize('view', $task);
    	
        $task->setHidden(['user']);

    	return response()->json([
                'status' => 'ok',
                'task' => $task
            ], 200);
    }

    /**
     * Update the specified Task.
     *
     * @Put("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="integer", description="Task Id.", required=true),
     *      @Parameter("title", type="string", description="Task title.", required=true),
     *      @Parameter("description", type="string", description="Task description.", required=true),
     *      @Parameter("due_date", type="date", description="Task due date.", required=true),
     *      @Parameter("completed", type="boolean", description="Task completed status.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \App\Task  $task
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Task $task, Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->authorize('update', $task);

        $this->validate($request, [
            'title' => 'required|min:2|max:1020',
            'description' => 'required|min:2|max:1020',
            'completed' => 'boolean',
            'due_date' => 'required|date_format:Y-m-d H:i:s',
            'user_id' => 'string|min:24|validuserid',
        ]);

        $task->fill($request->all());

        $user_id = (int)$request->input('user_id');

        if(($currentUser->isAdmin() || $currentUser->isSuperAdmin())
            && $user_id) {
            $task->user_id = $user_id;
        }

        $task->save();
        
        $task->setHidden(['user']);
        
        return response()->json([
                'status' => 'ok',
                'task' => $task
            ], 200);
    }

    /**
     * Remove the specified Task.
     *
     * @Delete("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="integer", description="Task Id.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok"})
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function delete(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
                'status' => 'ok'
            ], 200);
    }
}
