<?php

Route::group(['prefix' => 'web'], function () {
    Route::group(['middleware' => 'csrf'], function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::get('logout', 'AuthController@logout');
    });
    Route::group(['middleware' => 'auth'], function () {
        Route::post('password/change', 'AuthController@changePassword');
        Route::resource('sponsorships', 'SponsorshipController');
        Route::get('sponsorships/page/{page}', 'SponsorshipController@paged');
        Route::post('sponsorships/{id}/postpone', 'SponsorshipController@postponeApplication');
        Route::post('sponsorships/{id}/close', 'SponsorshipController@close');
        Route::get('sponsorships/{sponsorship}/applications', 'SponsorshipApplicationController@index');
        Route::post('sponsorships/{sponsorship}/applications/{id}/approve', 'SponsorshipApplicationController@approve');
        Route::post('sponsorships/{sponsorship}/applications/{id}/reject', 'SponsorshipApplicationController@reject');
    });
});

//Route::group(['prefix' => 'api', 'middleware' => 'auth'], function () {
//    Route::get('teams/{team}/sponsorships', 'SponsorshipApplicationController@appliedSponsorships');
//});

Route::group(['prefix' => 'api', 'middleware' => ['request.sign', 'team.inject']], function () {

    Route::group(['prefix' => 'user'], function () {
        Route::post('register', 'UserController@register');
        Route::post('login', 'UserController@login');
        Route::get('logout', 'UserController@logout');
        Route::get('sponsorships', 'SponsorshipController@sponsorships');
    });
    Route::group(['middleware' => 'team.auth'], function () {
        Route::group(['prefix' => 'user',], function () {
            Route::post('password/change', 'UserController@changePassword');
            Route::get('profile', 'UserController@showProfile');
            Route::post('profile/update', 'UserController@updateProfile');
            Route::get('sponsorships', 'SponsorshipApplicationController@appliedSponsorships');
        });
        Route::group(['prefix' => 'team'], function () {
            Route::get('list', 'TeamController@getTeams');
            Route::get('detail', 'TeamController@getTeam');
        });
        Route::post('sponsorships/{sponsorship}/applications', 'SponsorshipApplicationController@store');
    });
    Route::get('sponsorships', 'SponsorshipController@sponsorships');
});

Route::get('/', 'WelcomeController@index');
//Route::get('/logout', 'AuthController@logout');

