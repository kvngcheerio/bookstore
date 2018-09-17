<?php

return [
    'sign_up' => [
        'release_token' => env('SIGN_UP_RELEASE_TOKEN'),
        'validation_rules' => [
            'username' => 'bail|required|unique:users',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone' => 'required|digits_between:8,20|unique:users',
            'job_title' => 'required|string',
            'employed_date'=>'numeric|min:1950',
            'frontend_url' => 'required|string',
        ]
    ],
    'reader_sign_up' => [
        'release_token' => env('SIGN_UP_RELEASE_TOKEN'),
        'validation_rules' => [
            'username' => 'bail|required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|digits_between:8,20|unique:users',        
//		    users table data
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'frontend_url' => 'required|string',
        ]
    ],
    'edit_account' => [
        'validation_rules' => [
            'email' => 'bail|required|email',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone' => 'required|digits_between:8,20',
            'job_title' => 'required|string',
            'employed_date'=>'numeric|min:1950',
            'logo' => 'image|mimes:jpeg,png,jpg,gif,svg'
        ]
    ],
    'edit_reader_account' => [
        'validation_rules' => [
            'phone' => 'required|digits_between:8,20',
            'email1' => 'email',                       
//		    users table data
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'logo' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]
    ],
    'change_password' => [
        'validation_rules' => [
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]
    ],
    'admin_create_user' => [
        'validation_rules' => [
            'username' => 'bail|required|unique:users',
            'email' => 'required|email|unique:users',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone' => 'required|digits_between:8,20|unique:users',
            'frontend_url' => 'required|string',
        ]
    ],
    'admin_edit_user' => [
        'validation_rules' => [
            'email' => 'bail|required|email',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone' => 'required|digits_between:8,20'
        ]
    ],
    'login' => [
        'validation_rules' => [
            'username' => 'required',
            'password' => 'required|min:8'
        ]
    ],
    'forgot_password' => [
        'validation_rules' => [
            'email' => 'required|email',
            'frontend_url' => 'required|string',
        ]
    ],
    'reset_password' => [
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),
        'validation_rules' => [
            'reset_token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]
    ],
    'lock_user' => [
        'validation_rules' => [
            'reason' => 'required',
        ]
    ],
    'is_locked' => [
        'validation_rules' => [
            'user_id' => 'required|numeric',
        ]
    ],
    'add_user' => [
        'validation_rules' => [
            'email' => 'bail|required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone' => 'required|digits_between:8,20',
            'roles' => 'required|min:1'
        ]
    ],
    'setting' => [
        'validation_rules' => [
            'key' => 'required',
            'value' => 'required'
        ]
    ],
    'create_book_category' => [
        'validation_rules' => [
            'name' => 'required|string|unique:categories',
            'description' => 'required|string',
            'meta_keyword' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'picture' => 'image|max:2048',
        ]
    ],
    'update_book_category' => [
        'validation_rules' => [
            'name' => 'required|string',
            'description' => 'required|string',
            'meta_keyword' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'picture' => 'image|max:2048',
        ]
    ],
    'create_book' => [
        'validation_rules' => [
            'title' => 'required|string|max:450',
            'short_description' => 'required|max:600',
            'full_description' => 'required',
            'author_name' => 'required',
            'page_count' => 'required',
            'price' => 'required|numeric|max:99999999999999999999',
            'sku' => 'string|max:450',
            'category' => 'required',
            'category.*' => 'required|numeric',
           
            //'picture' => 'array',
            'picture' => 'image|max:2048',
            'picture.*' => 'required_with:picture|image|max:2048',
        ]
    ],
    'update_book' => [
        'validation_rules' => [
            'title' => 'required|string|max:450',
            'short_description' => 'required|max:600',
            'full_description' => 'required',
            'author_name' => 'required',
            'page_count' => 'required',
            'price' => 'required|numeric|max:99999999999999999999',
            'sku' => 'string|max:450',
            'category' => 'required',
            'category.*' => 'required|numeric',
           
            //'picture' => 'array',
            'picture' => 'image|max:2048',
            'picture.*' => 'required_with:picture|image|max:2048',
        ]
    ],
    'roles' => [
        'superadmin' => 1,
        'admin' => 2,
        'regular' => 3,
    ],
    'start_user_id' => 1926

];
