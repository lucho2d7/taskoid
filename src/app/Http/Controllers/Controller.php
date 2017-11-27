<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use View;
use App\Task;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * DB dump
     *
     * @return \Illuminate\Http\Response
     */
    public function dump()
    {
        $tasks = Task::orderBy('user_id', 'asc')->orderBy('due_date', 'asc')->get();

        return View::make('dump')->with('tasks', $tasks);
    }
}