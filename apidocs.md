FORMAT: 1A

# Taskoid

# Auth Signup [/auth/signup]
Signup resource representation.

## Signup to the system as user. [POST /auth/signup]


+ Parameters
    + name: (string, required) - User full name.
    + email: (string, required) - User email.
    + password: (string, required) - User password.
    + password_confirmation: (string, required) - User password confirmation.

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

## Signup verification for a new user. [POST /auth/signup]


+ Parameters
    + name: (string, required) - User full name.
    + email: (string, required) - User email.
    + password: (string, required) - User password.

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

# Auth Login [/auth/login]
Login resource representation.

## Login [POST /auth/login]


+ Parameters
    + email: (string, required) - User email.
    + password: (string, required) - User password.

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "_id": "2",
                "role": "user",
                "name": "John Doe",
                "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI1YTI1ZDJlNzcwZjFiODAwMDgxZGYzZTQiLCJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODEvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE1MTI0MjgyODEsImV4cCI6MTUxMjQzMTg4MSwibmJmIjoxNTEyNDI4MjgxLCJqdGkiOiIxblFzUGlwWmZQVDRLVkVvIn0.Jf-suzGfgnYeEFTmKhAHLNUoBkwQ5X0a8_V-PuSKy4E"
            }

## Logout [POST /auth/login]


+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

# Auth Forgot Password [/auth/recovery]
Forgotten password recovery function.

## Request a password reset link through email. [POST /auth/recovery]


+ Parameters
    + email: (string, required) - User email.

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

# Auth Reset Password [/auth/reset]
User password reset function.

## Request a password reset. [POST /auth/reset]


+ Parameters
    + token: (string, required) - A valid password reset token.
    + email: (string, required) - The user email.
    + password: (string, required) - A new password.
    + password_confirmation: (string, required) - The new password confirmation.

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

# Tasks [/tasks]
Task resource representation.

## Obtain a list of Tasks. [GET /tasks]
Get a JSON representation of the requested tasks

+ Parameters
    + title: (string, optional) - Return only Tasks with partial match for title field.
    + description: (string, optional) - Return only Tasks with partial match for description field.
    + due_date_from: (date, optional) - Return only Tasks with due date after or equal than from the specified date.
    + due_date_to: (date, optional) - Return only Tasks with due date before or equal than from the specified date.
    + completed: (boolean, optional) - Return only Tasks with specified completed value
    + created_at_from: (date, optional) - Return only Tasks with created date after or equal than from the specified date.
    + created_at_to: (date, optional) - Return only Tasks with created date before or equal than from the specified date.
    + updated_at_from: (date, optional) - Return only Tasks with updated date after or equal than from the specified date.
    + updated_at_to: (date, optional) - Return only Tasks with updated date before or equal than from the specified date.
    + user_id: (string, optional) - Return only Tasks that belong to the specified user.
    + page: (integer, optional) - Page number.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "tasks": {
                    "current_page": 1,
                    "data": [
                        "[task array]"
                    ],
                    "from": 1,
                    "last_page": 113,
                    "next_page_url": "http://localhost:8081/api/tasks?page=2",
                    "path": "http://localhost:8081/api/tasks",
                    "per_page": 5,
                    "prev_page_url": null,
                    "to": 5,
                    "total": 564
                }
            }

## Store a new Task. [POST /tasks]


+ Parameters
    + title: (string, required) - Task title.
    + description: (string, required) - Task description.
    + due_date: (date, required) - Task due date.
    + completed: (boolean, required) - Task completed status.
    + user_id: (boolean, required) - Task owner id.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "task": {
                    "completed": true,
                    "title": "Some interesting task",
                    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                    "due_date": "2017-12-27 15:15:00",
                    "user_id": "5a24c388c6abc200273ed173",
                    "updated_at": "2017-12-04 23:08:12",
                    "created_at": "2017-12-04 23:08:12",
                    "_id": "5a25d55c70f1b800081df3e5"
                }
            }

## Display the specified Task. [GET /tasks/{id}]


+ Parameters
    + id: (string, required) - Task Id.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "task": {
                    "completed": true,
                    "title": "Some interesting task",
                    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                    "due_date": "2017-12-27 15:15:00",
                    "user_id": "5a24c388c6abc200273ed173",
                    "updated_at": "2017-12-04 23:08:12",
                    "created_at": "2017-12-04 23:08:12",
                    "_id": "5a25d55c70f1b800081df3e5"
                }
            }

## Update the specified Task. [PUT /tasks]


+ Parameters
    + id: (integer, required) - Task Id.
    + title: (string, required) - Task title.
    + description: (string, required) - Task description.
    + due_date: (date, required) - Task due date.
    + completed: (boolean, required) - Task completed status.
    + user_id: (boolean, optional) - Task owner id.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "task": {
                    "completed": true,
                    "title": "Some interesting task",
                    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
                    "due_date": "2017-12-27 15:15:00",
                    "user_id": "5a24c388c6abc200273ed173",
                    "updated_at": "2017-12-04 23:08:12",
                    "created_at": "2017-12-04 23:08:12",
                    "_id": "5a25d55c70f1b800081df3e5"
                }
            }

## Remove the specified Task. [DELETE /tasks/{id}]


+ Parameters
    + id: (string, required) - Task Id.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

# Users [/users]
User resource representation.

## Obtain a list of Users. [GET /users]
Get a JSON representation of the requested users

+ Parameters
    + id: (string, optional) - A user id.
    + name: (string, optional) - Partial match for name field.
    + email: (string, optional) - Partial match for email field.
    + role: (string, optional) - A valid role.
    + status: (string, optional) - A valid status.
    + page: (integer, optional) - Page number.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "users": {
                    "current_page": 1,
                    "data": [
                        "[user array]"
                    ],
                    "from": 1,
                    "last_page": 113,
                    "next_page_url": "http://localhost:8081/api/users?page=2",
                    "path": "http://localhost:8081/api/users",
                    "per_page": 5,
                    "prev_page_url": null,
                    "to": 5,
                    "total": 564
                }
            }

## Store a new User. [POST /users]


+ Parameters
    + name: (string, required) - User name.
    + email: (string, required) - Email email.
    + password: (string, required) - User password.
    + role: (string, required) - User role.
    + status: (string, required) - User status.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "user": {
                    "role": "user",
                    "name": "Peter Griffin",
                    "email": "myemail@example.net",
                    "status": "enabled",
                    "_id": "5a24c388c6abc200273ed173"
                }
            }

## Display the specified User. [GET /users/{id}]


+ Parameters
    + id: (string, required) - User Id.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "user": {
                    "role": "user",
                    "name": "Peter Griffin",
                    "email": "myemail@example.net",
                    "status": "enabled",
                    "_id": "5a24c388c6abc200273ed173",
                    "updated_at": "2017-12-04 23:08:12",
                    "created_at": "2017-12-04 23:08:12"
                }
            }

## Update the specified User. [PUT /users]


+ Parameters
    + name: (string, optional) - User name.
    + email: (string, optional) - Email email.
    + password: (string, optional) - User password.
    + role: (string, optional) - User role.
    + status: (string, optional) - User status.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }

## Remove the specified User. [DELETE /users/{id}]


+ Parameters
    + id: (string, required) - User Id.

+ Request (application/json)
    + Headers

            Authorization: Bearer [token]
    + Body

            ""

+ Response 200 (application/json)
    + Body

            {
                "status": "ok"
            }