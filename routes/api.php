<?php

use Dingo\Api\Routing\Router;
use Tymon\JWTAuth\Facades\JWTAuth;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {

    $api->group(['middleware' => 'hidesensitiveinformation'], function (Router $api) {

    //auth, requires no sign-in
        $api->group(['prefix' => 'auth'], function (Router $api) {
        //authentication routes
           
            $api->post('reader/signup', 'App\Api\V1\Controllers\SignUpController@readerSignUp');

            $api->get('user-exists/email/{email}', 'App\Api\V1\Controllers\UserController@getUserByEmail');
            $api->get('user-exists/username/{username}', 'App\Api\V1\Controllers\UserController@getUserByUsername');


            $api->get('internal/signup', 'App\Api\V1\Controllers\SignUpController@showInternalUserSignUp');
            $api->post('internal/signup', 'App\Api\V1\Controllers\SignUpController@signUp');

            $api->post('login', 'App\Api\V1\Controllers\LoginController@login');

            $api->get('activate/{activation_token}', 'App\Api\V1\Controllers\AccountActivationController@getAccountActivation');

            $api->group(['prefix' => 'password'], function (Router $api) {
            //password reset routes
                $api->post('recovery', 'App\Api\V1\Controllers\ForgotPasswordController@sendResetEmail');
                $api->get('reset/{reset_token}', 'App\Api\V1\Controllers\ResetPasswordController@getResetPassword');
                $api->post('new', 'App\Api\V1\Controllers\ResetPasswordController@doResetPassword');
            });

            $api->get('is-taken/{field}/{value}', 'App\Api\V1\Controllers\SignUpController@checkFormFields');
        });

    //requires sign-in, all routes here need token to get access
        $api->group(['middleware' => 'jwt.auth'], function (Router $api) {
            $api->get('users/me', 'App\Api\V1\Controllers\UserController@showAuthenticatedUser');
            $api->put('change-password', 'App\Api\V1\Controllers\UserController@changePassword');

       
            $api->get('books-category', 'App\Api\V1\Controllers\CategoryController@index');
        

            $api->get('refresh', [
                'middleware' => 'tokenrefresh', 'as' => 'refresh',
                function () {
                    return response()->json([
                        'message' => 'Token refreshed. Check response headers for new token!'
                    ]);
                }
            ]);

        
            $api->put('reader/account', 'App\Api\V1\Controllers\ReaderController@update');

           

            $api->post('books/import', 'App\Api\V1\Controllers\BookController@massImport');
            $api->get('books/import-sample/{fileType}', 'App\Api\V1\Controllers\GoodController@sampleImportBooks');
            $api->get('books/latest/{amount}', 'App\Api\V1\Controllers\BookController@latest');

          

            $api->post('books/add-picture', 'App\Api\V1\Controllers\BookController@addPicture');

            $api->resource('pictures', 'App\Api\V1\Controllers\PictureController', [
                'only' => ['store']
            ]);
            $api->delete('pictures/{picturePath}', 'App\Api\V1\Controllers\PictureController@destroy')->where('picturePath', '(.*)');


            $api->group(['middleware' => ['role:internal_user|super_admin']], function (Router $api) {
                $api->get('internal/account/edit', 'App\Api\V1\Controllers\InternalUserController@show');
                $api->put('internal/account/update', 'App\Api\V1\Controllers\InternalUserController@update');

            });

          

            $api->group(['middleware' => ['permission:view_internal_users|change_others_password|update_internal_users']], function (Router $api) {
                $api->get('users/{id}', 'App\Api\V1\Controllers\UserController@getUserDetailForAdmin')->where(['id' => '[0-9]+']);
                $api->get('users/internal-users', 'App\Api\V1\Controllers\UserController@getInternalUsers');
                $api->get('users/all', 'App\Api\V1\Controllers\UserController@getAllNoPaginate');
            });

            $api->group(['middleware' => ['permission:view_readers|change_others_password|update_readers']], function (Router $api) {
                $api->get('users/readers', 'App\Api\V1\Controllers\UserController@getReaders');
            });

           
            $api->group(['middleware' => ['permission:delete_users']], function (Router $api) {
                $api->delete('users/{id}', 'App\Api\V1\Controllers\UserController@destroy')->where(['id' => '[0-9]+']);
            });

            $api->group(['middleware' => ['permission:change_others_password']], function (Router $api) {
                $api->put('users/change-password/{id}', 'App\Api\V1\Controllers\UserController@adminChangePassword')->where(['id' => '[0-9]+']);
            });

            $api->group(['middleware' => ['permission:create_internal_users']], function (Router $api) {
                $api->get('users/internal/create', 'App\Api\V1\Controllers\InternalUserController@showAdminCreate');
                $api->post('users/internal', 'App\Api\V1\Controllers\InternalUserController@adminStore');
            });

            $api->group(['middleware' => ['permission:create_readers']], function (Router $api) {
                $api->get('users/readers/create', 'App\Api\V1\Controllers\ReaderController@showAdminCreate');
                $api->post('users/readers', 'App\Api\V1\Controllers\ReaderController@adminStore');
            });

            $api->group(['middleware' => ['permission:update_readers']], function (Router $api) {
                $api->put('users/readers/{id}', 'App\Api\V1\Controllers\ReaderController@adminUpdate')->where(['id' => '[0-9]+']);
            });

            $api->group(['middleware' => ['permission:update_internal_users']], function (Router $api) {
                $api->put('users/internal/{id}', 'App\Api\V1\Controllers\InternalUserController@adminUpdate')->where(['id' => '[0-9]+']);
            });

            $api->group(['middleware' => ['permission:view_books_category|update_books_category']], function (Router $api) {
                $api->get('books-category/{id}', 'App\Api\V1\Controllers\CategoryController@show')->where(['id' => '[0-9]+']);
            });

            $api->group(['middleware' => ['permission:create_books_category']], function (Router $api) {
                $api->post('books-category', 'App\Api\V1\Controllers\CategoryController@store');
            });

            $api->group(['middleware' => ['permission:update_books_category']], function (Router $api) {
                $api->put('books-category/{id}', 'App\Api\V1\Controllers\CategoryController@update');
            });

            $api->group(['middleware' => ['permission:delete_books_category']], function (Router $api) {
                $api->delete('books-category/{id}', 'App\Api\V1\Controllers\CategoryController@destroy')->where(['id' => '[0-9]+']);
                $api->post('books-category/delete', 'App\Api\V1\Controllers\CategoryController@deleteMulti');
            });

            $api->group(['middleware' => ['permission:update_job_titles']], function (Router $api) {
                $api->put('job-titles/{id}', 'App\Api\V1\Controllers\InternalUserStatusController@update')->where(['id' => '[0-9]+']);
            });

            $api->group(['middleware' => ['permission:create_job_titles']], function (Router $api) {
                $api->post('job-titles', 'App\Api\V1\Controllers\InternalUserStatusController@update');
            });

            $api->group(['middleware' => ['permission:delete_job_titles']], function (Router $api) {
                $api->delete('job-titles/{id}', 'App\Api\V1\Controllers\InternalUserStatusController@destroy')->where(['id' => '[0-9]+']);
            });

           
            $api->get('logout', 'App\Api\V1\Controllers\LogoutController@logoutUser');

           

            
            $api->group(['middleware' => ['role:super_admin']], function (Router $api) {
                $api->resource('permissions', 'App\Api\V1\Controllers\PermissionController', [
                    'except' => ['update', 'destroy']
                ]);
            });

            $api->group(['middleware' => ['role:admin|super_admin']], function (Router $api) {
                //sam
                $api->resource('roles', 'App\Api\V1\Controllers\RoleController');

                $api->get('settings', 'App\Api\V1\Controllers\SettingController@index');
                $api->put('settings/{id}', 'App\Api\V1\Controllers\SettingController@update');

                $api->resource('message-templates', 'App\Api\V1\Controllers\MessageTemplateController', [
                    'except' => ['store', 'destroy']
                ]);

                $api->get('events', 'App\Api\V1\Controllers\EventController@index');
                $api->delete('events/{id}', 'App\Api\V1\Controllers\EventController@destroy');
                $api->delete('events/range', 'App\Api\V1\Controllers\EventController@destroyByDates');

                $api->resource('books', 'App\Api\V1\Controllers\BookController', [
                    'only' => ['store', 'update', 'destroy', 'show']
                ]);

                
                $api->resource('books-review', 'App\Api\V1\Controllers\BookReviewController', [
                    'only' => ['update', 'destroy', 'show']
                ]);
            });

      
             
            $api->resource('books-review', 'App\Api\V1\Controllers\BookReviewController', [
                'except' => ['update', 'destroy', 'show']
            ]);

           
            //user request account cancellation. mainly used by vendors to cancel their registration
            $api->post('user/cancel-account', 'App\Api\V1\Controllers\UserController@cancelAccount')->name('user.cancel_account');

            
        $api->resource('books', 'App\Api\V1\Controllers\BookController', [
            'except' => ['store', 'update', 'destroy', 'show']
        ]);
     
        $api->get('job-titles', 'App\Api\V1\Controllers\InternalUserStatusController@index');

        $api->get('image/{pictureUrl}', 'App\Api\V1\Controllers\PictureController@show')->where('pictureUrl', '(.*)');

        $api->get('hello', function () {
            return response()->json([
                'message' => 'this is a public url. everyone can see it'
            ]);
        });

        
    });
});

});
