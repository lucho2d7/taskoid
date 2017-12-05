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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

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
     *      @Parameter("user_id", type="string", description="Return only Tasks that belong to the specified user."),
     *      @Parameter("page", type="integer", description="Page number."),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status": "ok","tasks": {"current_page": 1,
                                                        "data": { "[task array]" },
                                                        "from": 1,
                                                        "last_page": 113,
                                                        "next_page_url": "http://localhost:8081/api/tasks?page=2",
                                                        "path": "http://localhost:8081/api/tasks",
                                                        "per_page": 5,
                                                        "prev_page_url": null,
                                                        "to": 5,
                                                        "total": 564
                                                    }
                                                })
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
            'due_date_to' => 'date_format:Y-m-d H:i:s|after:due_date_from',
            'completed' => 'boolean',
            'created_at_from' => 'date_format:Y-m-d H:i:s',
            'created_at_to' => 'date_format:Y-m-d H:i:s|after:created_at_from',
            'updated_at_from' => 'date_format:Y-m-d H:i:s',
            'updated_at_to' => 'date_format:Y-m-d H:i:s|after:updated_at_from',
            'user_id' => 'string|min:24|validuserid',
            'page' => 'integer|min:1',
        ]);

        $currentUser = JWTAuth::parseToken()->authenticate();

        $user_id = ($currentUser->isSuperAdmin() || $currentUser->isAdmin())
                    ? $request->input('user_id')
                    : $currentUser->id;

        $user_role = 'user';

        if($currentUser->isSuperAdmin()) {
            $user_role = '';
        }
        else if($currentUser->isAdmin() && $user_id == $currentUser->id) {
            $user_role = 'admin';
        }

        $key = md5(serialize([$user_id, $user_role, $request->all()]));

        $tasks = Cache::tags('tasks')->remember($key, 10, function() use ($user_id, $user_role, $request) {
            return Task::userId($user_id)
                        ->titlePartial($request->input('title'))
                        ->descriptionPartial($request->input('description'))
                        ->completed($request->has('completed'), $request->input('completed'))
                        ->dueDateFrom($request->input('due_date_from'))
                        ->dueDateTo($request->input('due_date_to'))
                        ->createdAtFrom($request->input('created_at_from'))
                        ->createdAtTo($request->input('created_at_to'))
                        ->updatedAtFrom($request->input('updated_at_from'))
                        ->updatedAtTo($request->input('updated_at_to'))
                        ->userRole($user_role)
                        ->orderBy('due_date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->orderBy('updated_at', 'asc')
                        ->paginate(5);
        });

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
     *      @Parameter("user_id", type="boolean", description="Task owner id.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok","task": {"completed": true,
                                                    "title": "Some interesting task",
                                                    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                                                    "due_date": "2017-12-27 15:15:00",
                                                    "user_id": "5a24c388c6abc200273ed173",
                                                    "updated_at": "2017-12-04 23:08:12",
                                                    "created_at": "2017-12-04 23:08:12",
                                                    "_id": "5a25d55c70f1b800081df3e5"
                                                }})
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
            'description' => 'min:2|max:1020',
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
        
        Cache::tags('tasks')->flush();

        $task->setHidden(['user', 'user_role']);
        
        return response()->json([
                'status' => 'ok',
                'task' => $task
            ], 201);
    }

    /**
     * Display the specified Task.
     *
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", type="string", description="Task Id.", required=true),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok","task": {"completed": true,
                                                    "title": "Some interesting task",
                                                    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                                                    "due_date": "2017-12-27 15:15:00",
                                                    "user_id": "5a24c388c6abc200273ed173",
                                                    "updated_at": "2017-12-04 23:08:12",
                                                    "created_at": "2017-12-04 23:08:12",
                                                    "_id": "5a25d55c70f1b800081df3e5"
                                                }})
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function view(Task $task)
    {
    	$this->authorize('view', $task);
    	
        $task->setHidden(['user', 'user_role']);

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
     * @Parameters({
     *      @Parameter("id", type="integer", description="Task Id.", required=true),
     *      @Parameter("title", type="string", description="Task title.", required=true),
     *      @Parameter("description", type="string", description="Task description.", required=true),
     *      @Parameter("due_date", type="date", description="Task due date.", required=true),
     *      @Parameter("completed", type="boolean", description="Task completed status.", required=true),
     *      @Parameter("user_id", type="boolean", description="Task owner id."),
     * })
     * @Request("", headers={"Authorization": "Bearer [token]"})
     * @Response(200, body={"status":"ok","task": {"completed": true,
                                                    "title": "Some interesting task",
                                                    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                                                    "due_date": "2017-12-27 15:15:00",
                                                    "user_id": "5a24c388c6abc200273ed173",
                                                    "updated_at": "2017-12-04 23:08:12",
                                                    "created_at": "2017-12-04 23:08:12",
                                                    "_id": "5a25d55c70f1b800081df3e5"
                                                }})
     *
     * @param  \App\Task  $task
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Task $task, Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $this->authorize('update', [Task::class, $task, $request->input('user_id')]);

        $this->validate($request, [
            'title' => 'required|min:2|max:1020',
            'description' => 'min:2|max:1020',
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

        Cache::tags('tasks')->flush();
        
        // Do not return associated user data
        $task->setHidden(['user', 'user_role']);
        
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
     *      @Parameter("id", type="string", description="Task Id.", required=true),
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

        Cache::tags('tasks')->flush();

        return response()->json([
                'status' => 'ok'
            ], 200);
    }
}
