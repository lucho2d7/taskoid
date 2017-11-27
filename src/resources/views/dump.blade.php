@extends('layouts.master')

@section('head')
@stop

@section('content')
    <a href="/">Home</a>
    <div class="page-header">Task list.</div>
    <div class="table-responsive">
        <table class="table table-striped">
        @if (count($tasks) > 0)
            <?php $i = 1 ?>

            <thead>
                <th>No.</th>
                <th>Id.</th>
                <th>Title</th>
                <th>Description</th>
                <th>Owner</th>
                <th>Due Date</th>
                <th>Createad At</th>
                <th>Updated At</th>
            </thead>

            @foreach ($tasks as $task)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $task->_id }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->description }}</td>
                    <td>{{ $task->user_id }}</td>
                    <td>{{ $task->due_date }}</td>
                    <td>{{ $task->created_at }}</td>
                    <td>{{ $task->updated_at }}</td>
                </tr>
                <?php $i++ ?>
            @endforeach
        @else
            <p>No tasks in database.</p>
        @endif
        </table>
    </div>
    <a href="#">Back to top</a>
@stop