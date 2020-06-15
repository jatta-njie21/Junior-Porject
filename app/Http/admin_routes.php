<?php

/* ================== Homepage ================== */
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');
Route::auth();

/* ================== Access Uploaded Files ================== */
Route::get('files/{hash}/{name}', 'LA\UploadsController@get_file');

/*
|--------------------------------------------------------------------------
| Admin Application Routes
|--------------------------------------------------------------------------
*/

$as = "";
if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
	$as = config('laraadmin.adminRoute').'.';
	
	// Routes for Laravel 5.3
	Route::get('/logout', 'Auth\LoginController@logout');
}

Route::group(['as' => $as, 'middleware' => ['auth', 'permission:ADMIN_PANEL']], function () {
	
	/* ================== Dashboard ================== */
	
	Route::get(config('laraadmin.adminRoute'), 'LA\DashboardController@index');
	Route::get(config('laraadmin.adminRoute'). '/dashboard', 'LA\DashboardController@index');
	
	/* ================== Users ================== */
	Route::resource(config('laraadmin.adminRoute') . '/users', 'LA\UsersController');
	Route::get(config('laraadmin.adminRoute') . '/user_dt_ajax', 'LA\UsersController@dtajax');
	
	/* ================== Uploads ================== */
	Route::resource(config('laraadmin.adminRoute') . '/uploads', 'LA\UploadsController');
	Route::post(config('laraadmin.adminRoute') . '/upload_files', 'LA\UploadsController@upload_files');
	Route::get(config('laraadmin.adminRoute') . '/uploaded_files', 'LA\UploadsController@uploaded_files');
	Route::post(config('laraadmin.adminRoute') . '/uploads_update_caption', 'LA\UploadsController@update_caption');
	Route::post(config('laraadmin.adminRoute') . '/uploads_update_filename', 'LA\UploadsController@update_filename');
	Route::post(config('laraadmin.adminRoute') . '/uploads_update_public', 'LA\UploadsController@update_public');
	Route::post(config('laraadmin.adminRoute') . '/uploads_delete_file', 'LA\UploadsController@delete_file');
	
	/* ================== Roles ================== */
	Route::resource(config('laraadmin.adminRoute') . '/roles', 'LA\RolesController');
	Route::get(config('laraadmin.adminRoute') . '/role_dt_ajax', 'LA\RolesController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/save_module_role_permissions/{id}', 'LA\RolesController@save_module_role_permissions');
	
	/* ================== Permissions ================== */
	Route::resource(config('laraadmin.adminRoute') . '/permissions', 'LA\PermissionsController');
	Route::get(config('laraadmin.adminRoute') . '/permission_dt_ajax', 'LA\PermissionsController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/save_permissions/{id}', 'LA\PermissionsController@save_permissions');
	
	/* ================== Departments ================== */
	Route::resource(config('laraadmin.adminRoute') . '/departments', 'LA\DepartmentsController');
	Route::get(config('laraadmin.adminRoute') . '/department_dt_ajax', 'LA\DepartmentsController@dtajax');
	
	/* ================== Employees ================== */
	Route::resource(config('laraadmin.adminRoute') . '/employees', 'LA\EmployeesController');
	Route::get(config('laraadmin.adminRoute') . '/employee_dt_ajax', 'LA\EmployeesController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/change_password/{id}', 'LA\EmployeesController@change_password');
	
	/* ================== Organizations ================== */
	Route::resource(config('laraadmin.adminRoute') . '/organizations', 'LA\OrganizationsController');
	Route::get(config('laraadmin.adminRoute') . '/organization_dt_ajax', 'LA\OrganizationsController@dtajax');

	/* ================== Backups ================== */
	Route::resource(config('laraadmin.adminRoute') . '/backups', 'LA\BackupsController');
	Route::get(config('laraadmin.adminRoute') . '/backup_dt_ajax', 'LA\BackupsController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/create_backup_ajax', 'LA\BackupsController@create_backup_ajax');
	Route::get(config('laraadmin.adminRoute') . '/downloadBackup/{id}', 'LA\BackupsController@downloadBackup');









	/* ================== CategoryDepartments ================== */
	Route::resource(config('laraadmin.adminRoute') . '/categorydepartments', 'LA\CategoryDepartmentsController');
	Route::get(config('laraadmin.adminRoute') . '/categorydepartment_dt_ajax', 'LA\CategoryDepartmentsController@dtajax');

	/* ================== Categories ================== */
	Route::resource(config('laraadmin.adminRoute') . '/categories', 'LA\CategoriesController');
	Route::get(config('laraadmin.adminRoute') . '/category_dt_ajax', 'LA\CategoriesController@dtajax');

	/* ================== Products ================== */
	Route::resource(config('laraadmin.adminRoute') . '/products', 'LA\ProductsController');
	Route::get(config('laraadmin.adminRoute') . '/product_dt_ajax', 'LA\ProductsController@dtajax');

	/* ================== Products ================== */
	Route::resource(config('laraadmin.adminRoute') . '/products', 'LA\ProductsController');
	Route::get(config('laraadmin.adminRoute') . '/product_dt_ajax', 'LA\ProductsController@dtajax');

	/* ================== ProductAvatars ================== */
	Route::resource(config('laraadmin.adminRoute') . '/productavatars', 'LA\ProductAvatarsController');
	Route::get(config('laraadmin.adminRoute') . '/productavatar_dt_ajax', 'LA\ProductAvatarsController@dtajax');

	/* ================== Carts ================== */
	Route::resource(config('laraadmin.adminRoute') . '/carts', 'LA\CartsController');
	Route::get(config('laraadmin.adminRoute') . '/cart_dt_ajax', 'LA\CartsController@dtajax');

	/* ================== LineItems ================== */
	Route::resource(config('laraadmin.adminRoute') . '/lineitems', 'LA\LineItemsController');
	Route::get(config('laraadmin.adminRoute') . '/lineitem_dt_ajax', 'LA\LineItemsController@dtajax');

	/* ================== Orders ================== */
	Route::resource(config('laraadmin.adminRoute') . '/orders', 'LA\OrdersController');
	Route::get(config('laraadmin.adminRoute') . '/order_dt_ajax', 'LA\OrdersController@dtajax');

	/* ================== Customers ================== */
	Route::resource(config('laraadmin.adminRoute') . '/customers', 'LA\CustomersController');
	Route::get(config('laraadmin.adminRoute') . '/customer_dt_ajax', 'LA\CustomersController@dtajax');

	/* ================== CarRentals ================== */
	Route::resource(config('laraadmin.adminRoute') . '/carrentals', 'LA\CarRentalsController');
	Route::get(config('laraadmin.adminRoute') . '/carrental_dt_ajax', 'LA\CarRentalsController@dtajax');

	/* ================== CarAvatars ================== */
	Route::resource(config('laraadmin.adminRoute') . '/caravatars', 'LA\CarAvatarsController');
	Route::get(config('laraadmin.adminRoute') . '/caravatar_dt_ajax', 'LA\CarAvatarsController@dtajax');

	/* ================== BillingInfos ================== */
	Route::resource(config('laraadmin.adminRoute') . '/billinginfos', 'LA\BillingInfosController');
	Route::get(config('laraadmin.adminRoute') . '/billinginfo_dt_ajax', 'LA\BillingInfosController@dtajax');

	/* ================== Rentals ================== */
	Route::resource(config('laraadmin.adminRoute') . '/rentals', 'LA\RentalsController');
	Route::get(config('laraadmin.adminRoute') . '/rental_dt_ajax', 'LA\RentalsController@dtajax');
});
