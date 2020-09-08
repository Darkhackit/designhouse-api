<?php
//Public route
//Checking if user is logged in or out

Route::get('me',"User\MeController@getMe" );

//Getting designs

Route::get('designs','Design\DesignController@index');
Route::get('designs/{id}','Design\DesignController@findDesign');
Route::get('designs/slug/{slug}','Design\DesignController@findBySlug');

// Getting Teams
Route::get('teams/slug/{slug}','Teams\TeamController@findBySlug');
Route::get('teams/{id}/designs','Design\DesignController@getForTeam');
//Getting the Users

Route::get('users','User\UserController@index');
Route::get('users/{username}','User\UserController@findByUsername');
Route::get('users/{id}/designs','Design\DesignController@getForUser');

//Search

Route::get('search/designs','Design\DesignController@search');

Route::get('search/designers','User\UserController@search');


//Route group for authenticated users only


Route::group(['middleware' => 'auth:api'], function () {

    Route::post('logout',"Auth\LoginController@logout");
    Route::put('settings/profile', 'User\SettingsController@updateProfile');
    Route::put('settings/password', 'User\SettingsController@updatePassword');

    //Comment
    Route::post('designs/{id}/comment','Design\CommentController@store');
    Route::put('comment/{id}','Design\CommentController@update');
    Route::delete('comment/{id}','Design\CommentController@destroy');

    //Teams
     Route::post('teams', 'Teams\TeamController@store');
     Route::get('teams/{id}','Teams\TeamController@findById');
     Route::get('teams','Teams\TeamController@index');
     Route::get('users/teams','Teams\TeamController@fetchUserTeams');
     Route::put('teams/{id}','Teams\TeamController@update');
     Route::delete('teams/{id}','Teams\TeamController@destroy');
     Route::delete('teams/{id}/user/{user_id}','Teams\TeamController@removeFromTeam');

    //Likes
    Route::post('designs/{id}/like', 'Design\DesignController@like');
    Route::get('designs/{id}/liked', 'Design\DesignController@checkIfUserHasLiked');

    //Upload Designs
    Route::post('designs' , 'Design\UploadController@upload' );
    Route::put('designs/{id}','Design\DesignController@update');
    Route::get('designs/{id}/byUser','Design\DesignController@userOwnsDesign');
    Route::delete('designs/{id}','Design\DesignController@destroy');

    //Invitation
    Route::post('invitations/{teamId}','Teams\InvitationsController@invite');
    Route::post('invitations/{id}/resend','Teams\InvitationsController@resend');
    Route::post('invitations/{id}/response','Teams\InvitationsController@response');
    Route::delete('invitations/{id}','Teams\InvitationsController@destroy');

    //Chat
    Route::post('chats','Chats\ChatController@sendMessage');
    Route::get('chats','Chats\ChatController@getUserChat');
    Route::get('chats/{id}/messages','Chats\ChatController@getChatMessages');
    Route::put('chats/{id}/markAsRead','Chats\ChatController@markAsRead');
    Route::delete('messages/{id}','Chats\ChatController@destroyMessages');

});
//Route group for guest
Route::group(['middleware' => 'guest:api'], function () {

    Route::post('register', 'Auth\RegisterController@register');
    Route::post('verification/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('verification/resend', 'Auth\VerificationController@resend');
    Route::post('login',"Auth\LoginController@login");
    Route::post('password/email','Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset','Auth\ResetPasswordController@reset');

});

