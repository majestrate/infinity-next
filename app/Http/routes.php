<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@getIndex');

/*
| Control Panel (cp)
| Anything having to deal with secure information goes here.
| This includes:
| - Registration, Login, and Account Recovery.
| - Contributor status.
| - Board creation, Board management, Volunteer management.
| - Top level site management.
*/
Route::group(['prefix' => 'cp'], function()
{
	// Simple /cp/ requests go directly to /cp/home
	Route::any('/', 'Auth\HomeController@getIndex');
	
	Route::controllers([
		// /cp/auth handles sign-ins and registrar work.
		'auth'     => 'Auth\AuthController',
		// /cp/home is a landing page.
		'home'     => 'Auth\HomeController',
		// /cp/password handles password resets and recovery.
		'password' => 'Auth\PasswordController',
	]);
	
	if (env('CONTRIB_ENABLED', false))
	{
		Route::controllers([
			// /cp/donate is a Stripe cashier system for donations.
			'donate'   => 'Auth\DonateController',
		]);
	}
});

/*
| Contribution (contribute)
| Only enabled if CONTRIB_ENABLED is set to TRUE.
| Opens the fundraiser page.
*/
if (env('CONTRIB_ENABLED', false))
{
	Route::get('contribute', 'ContributeController@index');
}


/*
| Board (/anything/)
| A catch all. Used to load boards.
*/
Route::group([
	'namespace' => 'Board',
	'prefix'    => '{board}',
	'where'     => ['board' => '[a-z]{1,31}'],
], function()
{
	// /board/file/ requests (for thumbnails & files) goes to the FileController.
	Route::group([
		'prefix'    => 'file',
	], function()
	{
		Route::get('/', 'FileController@getIndex');
		
		Route::get('{hash}/{filename}', 'FileController@getFile')
			->where([
				'hash'     => "[a-f0-9]{32}",
			]);
	});
	
	// Pushes simple /board/ requests to their index page.
	Route::any('/',    'BoardController@getIndex');
	
	// Routes /board/1 to an index page for a specific pagination point.
	Route::get('{id}', 'BoardController@getIndex')->where(['id' => '[0-9]+']);
	
	// More complicated /board/view requests.
	Route::controller('', 'BoardController');
});



// force no caching
Route::filter('after', function($response) {
	// No caching for pages
	$response->header("Pragma", "no-cache");
	$response->header("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
});