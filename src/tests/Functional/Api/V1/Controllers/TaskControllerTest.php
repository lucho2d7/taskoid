<?php

namespace App\Functional\Api\V1\Controllers;

use Config;
use \App\User;
use \App\Task;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use JWTAuth;

/**
 * @group task_regular_user
 * Tests the api Task handling requests
 */
class TaskControllerRegularUserTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * The API credential token
     *
     * @var string
     */
    private $token = "";
    private $user = "";

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->token = JWTAuth::fromUser($this->user);
    }

    public function testTaskListReturnsTokenNotProvidedError()
    {
        $this->get('api/tasks',
        [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertJson([
            'error' => 'token_not_provided'
        ])->assertStatus(400);
    }

    public function testTaskListEmpty()
    {
        $this->get('api/tasks',
        [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'tasks' => [
                'current_page',
                'data',
                'from',
                'last_page',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ],
        ])->assertJson([
            'status' => 'ok',
            'tasks' => [
                'current_page' => 1,
                'data' => [],
                'from' => null,
                'last_page' => 0,
                'next_page_url' => null,
                'path' => $this->baseUrl.'/api/tasks',
                'per_page' => 5,
                'prev_page_url' => null,
                'to' => null,
                'total' => 0
            ]
        ])->assertStatus(200);
    }

    public function testTaskAddWithoutCompleted()
    {
        $task = factory(Task::class)->make();

        $this->post('api/tasks', [
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date->format('Y-m-d H:m:s'),
            'user_id' => $this->user->id
        ], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'task' => [
                'title',
                'description',
                'completed',
                'due_date',
                'user_id',
                'created_at',
                'updated_at'
            ],
        ])->assertJson([
            'status' => 'ok',
            'task' => [
                'title' => $task->title,
                'description' => $task->description,
                'completed' => false,//this is the default value
                'due_date' => $task->due_date->format('Y-m-d H:m:s'),
                'user_id' => $this->user->id
            ],
        ])->assertStatus(201);
    }

    public function testTaskAddWithCompleted()
    {
        $task = factory(Task::class)->make();

        $this->post('api/tasks', [
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date->format('Y-m-d H:m:s'),
            'completed' => true,
            'user_id' => $this->user->id
        ], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'task' => [
                'title',
                'description',
                'completed',
                'due_date',
                'user_id',
                'created_at',
                'updated_at'
            ],
        ])->assertJson([
            'status' => 'ok',
            'task' => [
                'title' => $task->title,
                'description' => $task->description,
                'completed' => true,
                'due_date' => $task->due_date->format('Y-m-d H:m:s'),
                'user_id' => $this->user->id
            ],
        ])->assertStatus(201);
    }

    public function testTaskAddWithInvalidInput()
    {
        $task = factory(Task::class)->states('invalid')->make();

        $this->post('api/tasks', [
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date,
            'completed' => $task->completed,
            'user_id' => $this->user->id
        ], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'error' => [
                'message',
                'errors' => [
                    'title',
                    'description',
                    'due_date',
                ],
            ],
        ])->assertJson([
            'error' => [
                'message' => '422 Unprocessable Entity',
                'errors' => [
                    'title' => ['The title must be at least 2 characters.'],
                    'description' => ['The description may not be greater than 1020 characters.'],
                    'due_date' => ['The due date does not match the format Y-m-d H:i:s.'],
                ],
            ],
        ])->assertStatus(422);
    }

    public function testTaskEdit()
    {
        $originalTask = factory(Task::class)->make();
        $this->user->tasks()->save($originalTask);

        $task = factory(Task::class)->make();

        $this->put('api/tasks/'.$originalTask->id, [
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date->format('Y-m-d H:m:s'),
            'completed' => $task->completed,
            'user_id' => $this->user->id
        ], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'task' => [
                'title',
                'description',
                'completed',
                'due_date',
                'user_id',
                'created_at',
                'updated_at'
            ],
        ])->assertJson([
            'status' => 'ok',
            'task' => [
                'title' => $task->title,
                'description' => $task->description,
                'completed' => $task->completed,
                'due_date' => $task->due_date->format('Y-m-d H:m:s'),
                'user_id' => $this->user->id
            ],
        ])->assertStatus(200);
    }

    public function testTaskEditWithInvalidInput()
    {
        $originalTask = factory(Task::class)->make();
        $this->user->tasks()->save($originalTask);

        $task = factory(Task::class)->states('invalid')->make();

        $this->put('api/tasks/'.$originalTask->id, [
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date,
            'completed' => $task->completed,
            'user_id' => $this->user->id
        ], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'error' => [
                'message',
                'errors' => [
                    'title',
                    'description',
                    'due_date',
                ],
            ],
        ])->assertJson([
            'error' => [
                'message' => '422 Unprocessable Entity',
                'errors' => [
                    'title' => ['The title must be at least 2 characters.'],
                    'description' => ['The description may not be greater than 1020 characters.'],
                    'due_date' => ['The due date does not match the format Y-m-d H:i:s.'],
                ],
            ],
        ])->assertStatus(422);
    }

    public function testTaskListWithOneResult()
    {
        $task = factory(Task::class)->make();
        $this->user->tasks()->save($task);

        $this->get('api/tasks',
        [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'tasks' => [
                'current_page',
                'data',
                'from',
                'last_page',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ],
        ])->assertJson([
            'status' => 'ok',
            'tasks' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'next_page_url' => null,
                'path' => $this->baseUrl.'/api/tasks',
                'per_page' => 5,
                'prev_page_url' => null,
                'to' => 1,
                'total' => 1
            ]
        ])->assertStatus(200);
    }

    public function testTaskListWithFiftyResultsPageOne()
    {
        $tasks = factory(Task::class, 50)->make();
        $this->user->tasks()->saveMany($tasks);

        $this->get('api/tasks',
        [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'tasks' => [
                'current_page',
                'data',
                'from',
                'last_page',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ],
        ])->assertJson([
            'status' => 'ok',
            'tasks' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 10,
                'next_page_url' => $this->baseUrl.'/api/tasks?page=2',
                'path' => $this->baseUrl.'/api/tasks',
                'per_page' => 5,
                'prev_page_url' => null,
                'to' => 5,
                'total' => 50
            ]
        ])->assertStatus(200);
    }

    public function testTaskListWithFiftyResultsPageTwo()
    {
        $tasks = factory(Task::class, 50)->make();
        $this->user->tasks()->saveMany($tasks);

        $this->get('api/tasks?page=2',
        [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
            'tasks' => [
                'current_page',
                'data',
                'from',
                'last_page',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ],
        ])->assertJson([
            'status' => 'ok',
            'tasks' => [
                'current_page' => 2,
                'from' => 6,
                'last_page' => 10,
                'next_page_url' => $this->baseUrl.'/api/tasks?page=3',
                'path' => $this->baseUrl.'/api/tasks',
                'per_page' => 5,
                'prev_page_url' => $this->baseUrl.'/api/tasks?page=1',
                'to' => 10,
                'total' => 50
            ]
        ])->assertStatus(200);
    }

    public function testTaskDelete()
    {
        $task = factory(Task::class)->make();
        $this->user->tasks()->save($task);

        $this->delete('api/tasks/'.$task->id, [], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'status',
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testTaskDeleteInexistent()
    {
        $this->delete('api/tasks/aaaaa', [], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'error' => [
                'message',
                'status_code'
            ],
        ])->assertJson([
            'error' => [
                'message' => 'not found',
                'status_code' => 404,
            ],
        ])->assertStatus(404);
    }

    public function testTaskDeleteFromOtherUser()
    {
        $otherUser = factory(User::class)->create();
        $task = factory(Task::class)->make();
        $otherUser->tasks()->save($task);

        $this->delete('api/tasks/'.$task->id, [], [
            'Accept' => $this->apiAcceptHeader,
            'Authorization' => 'Bearer '.$this->token
        ])->assertJsonStructure([
            'error' => [
                'message',
                'status_code'
            ],
        ])->assertJson([
            'error' => [
                'message' => 'This action is unauthorized.',
                'status_code' => 403,
            ],
        ])->assertStatus(403);
    }
}
