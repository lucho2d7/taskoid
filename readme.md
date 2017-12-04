# Taskoid: a task management REST API web service

This is a BackEnd Exercise project.

## Requirements

### Create a REST web service

Create a REST web service (API only no web ui needed) of a TO DO list. The actions to perform are:

- [x] Create a new task
- [x] Update a task
- [x] Delete a task.
- [x] Show a task by id.
- [x] List all tasks:
- [x] I want to filter the tasks by due date, completed and uncompleted, date of creation, and date of update.
- [x] The response, must be paginated showing only 5 results per page.

### Technologies to use:

- [x] The exercise must be developed in PHP.
- [x] You CAN use any framework that you want with no restrictions.
- [x] You MUST use a MongoDB Database.
- [x] The list of all result must be cached with Redis or Memcached.
- [x] Take care of the validations of the required fields.

### Task Schema:

| Attribute     | Data Type     | Notes            |
| ------------- | ------------- | ---------------- |
| _id           | [id]          |                  |
| title         | [string]      | (required)       |
| description   | [string]      |                  |
| due_date      | [datetime]    | (required)       |
| completed     | [boolean]     | (default: false) |
| created_at    | [datetime]    |                  |
| updated_at    | [datetime]    |                  |

### Notes:

- [x] Submit the application to a git repository with the necessary installation/execution instructions.
- [x] All documentation and comments in code should be in English