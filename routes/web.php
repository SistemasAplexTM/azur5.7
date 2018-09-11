<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();
Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', 'HomeController@index')->name('home');

    // ******************************************************************************

    /* REGISTRO DE LOG DE ACTIVIDADES DE USUARIOS */
    Route::get('logActivity', 'LogActivityController@index')->name('logActivity.index');
    Route::get('logActivity/all', 'LogActivityController@getAll')->name('logActivity.getAll');
    /*--- MODULO USER ---*/
    // Route::resource('user', 'UserController', ['except' => ['show', 'create', 'edit']]);
    Route::get('user', 'MenuController@index')->name('users.index')->middleware('permission:users.index');
    Route::post('user', 'MenuController@store')->name('users.store')->middleware('permission:users.store');
    Route::put('user', 'MenuController@update')->name('users.update')->middleware('permission:users.update');
    Route::delete('user', 'MenuController@destroy')->name('users.destroy')->middleware('permission:users.destroy');

    Route::post('user/validarUsername', 'UserController@validarUsername');
    Route::post('user/validar', 'UserController@validar');
    Route::get('user/all', 'UserController@getAll')->name('datatable/all');
    Route::get('user/restaurar/{id}', 'UserController@restaurar');
    // Route::get('user/getDataSelect/{table}', 'UserController@getDataSelect');

    /* ADMIN_TABLE */
    Route::delete('administracion/{id}', 'AdminTableController@destroy');
    Route::post('administracion/', 'AdminTableController@store');
    Route::put('administracion/update/{type}/{id}', 'AdminTableController@update');
    Route::get('administracion/{type}/all', 'AdminTableController@getAll');
    Route::get('administracion/{type}', 'AdminTableController@index');
    Route::get('administracion/{type}/getDataSelect', 'AdminTableController@getDataSelect');
    Route::get('administracion/{type}/restaurar/{id}', 'AdminTableController@restaurar');
    Route::get('administracion/{type}/delete/{id}/{logical?}', 'AdminTableController@delete')->name('AdminTable.delete');

    /*--- MODULO PRODUCTO ---*/
    Route::resource('product', 'ProductController', ['except' => ['show', 'create', 'edit']]);
    Route::get('product/all', 'ProductController@getAll')->name('datatable/all');
    Route::get('product/delete/{id}/{logical?}', 'ProductController@delete')->name('product.delete');
    Route::get('product/restaurar/{id}', 'ProductController@restaurar');
    Route::get('product/getDataSelect', 'ProductController@getDataSelect');

    /*--- MODULO CLIENTES ---*/
    Route::resource('clientes', 'ClienteController', ['except' => ['show', 'create', 'edit']]);
    Route::get('clientes/all', 'ClienteController@getAll')->name('datatable/all');
    Route::get('clientes/delete/{id}/{logical?}', 'ClienteController@delete')->name('clientes.delete');
    Route::get('clientes/restaurar/{id}', 'ClienteController@restaurar');
    Route::get('clientes/getDataSelect', 'ClienteController@getDataSelect');

    /*--- MODULO UNIDAD DE SERVICIO ---*/
    Route::resource('unidadServicio', 'UnidadServicioController', ['except' => ['show', 'create', 'edit']]);
    Route::get('unidadServicio/all', 'UnidadServicioController@getAll')->name('datatable/all');
    Route::get('unidadServicio/delete/{id}/{logical?}', 'UnidadServicioController@delete')->name('unidadServicio.delete');
    Route::get('unidadServicio/restaurar/{id}', 'UnidadServicioController@restaurar');
    Route::get('unidadServicio/getDataSelect', 'UnidadServicioController@getDataSelect');
    Route::get('unidadServicio/getDataByCliente/{cliente_id}/{tipo_us}', 'UnidadServicioController@getDataByCliente');
    Route::get('unidadServicio/getGrupoEdadByUs/{us_id}', 'UnidadServicioController@getGrupoEdadByUs');
    Route::post('unidadServicio/addGrupoEtareo', 'UnidadServicioController@addGrupoEtareo');
    Route::post('unidadServicio/updateCoverage', 'UnidadServicioController@updateCoverage');
    Route::delete('unidadServicio/deleteGrupoEdad/{id}', 'UnidadServicioController@deleteGrupoEdad');

    /*--- MODULO MENU ---*/
    // Route::resource('menus', 'MenuController', ['except' => ['show', 'create', 'edit']]);

    Route::get('menus', 'MenuController@index')->name('menus.index');
    Route::post('menus', 'MenuController@store')->name('menus.store');
    Route::put('menus/{id}', 'MenuController@update')->name('menus.update');
    Route::get('menus/destroy/{id}/{table?}', 'MenuController@destroy')->name('menus.destroy');

    Route::post('menus/addMenuDetail', 'MenuController@addMenuDetail');
    Route::get('menus/all/{type}', 'MenuController@getAll')->name('datatable/all');
    Route::get('menus/allDetalle/{id_menu}', 'MenuController@getAllDetalle')->name('datatable.allDetalle');
    // Route::get('menus/delete/{id}/{logical?}', 'MenuController@delete')->name('menus.delete');
    Route::get('menus/restaurar/{id}/{table?}', 'MenuController@restaurar');
    Route::get('menus/getDataSelect/{tipo_us_id}', 'MenuController@getDataSelect');
    Route::post('menus/updateDetailMenu', 'MenuController@updateDetailMenu');

    /*--- MODULO TERCERO ---*/
    Route::resource('tercero', 'TerceroController', ['except' => ['show', 'create', 'edit']]);
    Route::get('tercero/all', 'TerceroController@getAll')->name('datatable/all');
    Route::get('tercero/delete/{id}/{logical?}', 'TerceroController@delete')->name('tercero.delete');
    Route::get('tercero/restaurar/{id}', 'TerceroController@restaurar');
    Route::get('tercero/getDataSelect', 'TerceroController@getDataSelect');

    /*--- MODULO MINUTA ---*/
    Route::resource('minuta', 'MinutaController', ['except' => ['show', 'create']]);
    Route::get('minuta/all', 'MinutaController@getAll')->name('datatable/all');
    Route::get('minuta/delete/{id}/{logical?}', 'MinutaController@delete')->name('minuta.delete');
    Route::get('minuta/restaurar/{id}', 'MinutaController@restaurar');
    Route::get('minuta/getDataSelect', 'MinutaController@getDataSelect');
    Route::get('minuta/{id_minuta}/getMenusUnidadesByMinuta/{id_us}', 'MinutaController@getMenusUnidadesByMinuta');
    Route::get('minuta/{id_minuta}/getPedidoCompleto/{product_type?}/{id_uds?}/{name_minuta?}/{remanencia?}', 'MinutaController@getPedidoCompleto');
    Route::get('minuta/{id_minuta}/getProductsMinuta/{id_us}', 'MinutaController@getProductsMinuta');
    Route::post('minuta/{id_minuta}/saveRemanencia', 'MinutaController@saveRemanencia');
    Route::get('minuta/{id_minuta}/getRemanenciasByMinuta/{id_us}', 'MinutaController@getRemanenciasByMinuta');
    Route::delete('minuta/{id_minuta}/eliminarRemanencia/{id}', 'MinutaController@eliminarRemanencia');
    Route::get('minuta/{id_minuta}/restaurarRemanencia/{id}', 'MinutaController@restaurarRemanencia');
});
