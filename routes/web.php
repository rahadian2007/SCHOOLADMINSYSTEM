<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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

Route::group(['middleware' => ['get.menu', 'web']], function () {

Route::get('/', [HomeController::class, 'index']);

    Route::group(['middleware' => ['role:admin|cashier']], function () {
        Route::resource('orders', 'OrderController');
        Route::resource('products', 'ProductController');
        Route::resource('settlements', 'SettlementController');
        Route::get('cashiers', 'UsersController@cashiersList')->name('users.cashiersList');
        Route::get('canteen/settings', 'SettingsController@index')->name('canteen.settings.index');
        Route::put('canteen/settings', 'SettingsController@update')->name('canteen.settings.update');
    });

    Route::group(['middleware' => ['role:admin']], function () {
        Route::resource('va', 'VirtualAccountController');
        Route::post('va-export', 'VirtualAccountController@export')->name('va.export');
        Route::resource('payments', 'PaymentController');
        Route::resource('bread', 'BreadController'); // BREAD resource
        Route::resource('users', 'UsersController');
        Route::resource('roles', 'RolesController');
        Route::resource('mail', 'MailController');

        Route::get('prepareSend/{id}', 'MailController@prepareSend')->name('prepareSend');
        Route::post('mailSend/{id}', 'MailController@send')->name('mailSend');

        Route::get('/roles/move/move-up', 'RolesController@moveUp')->name('roles.up');
        Route::get('/roles/move/move-down', 'RolesController@moveDown')->name('roles.down');

        Route::prefix('menu/element')->group(function () {
            Route::get('/', 'MenuElementController@index')->name('menu.index');
            Route::get('/move-up', 'MenuElementController@moveUp')->name('menu.up');
            Route::get('/move-down', 'MenuElementController@moveDown')->name('menu.down');
            Route::get('/create', 'MenuElementController@create')->name('menu.create');
            Route::post('/store', 'MenuElementController@store')->name('menu.store');
            Route::get('/get-parents', 'MenuElementController@getParents');
            Route::get('/edit', 'MenuElementController@edit')->name('menu.edit');
            Route::post('/update', 'MenuElementController@update')->name('menu.update');
            Route::get('/show', 'MenuElementController@show')->name('menu.show');
            Route::get('/delete', 'MenuElementController@delete')->name('menu.delete');
        });

        Route::prefix('menu/menu')->group(function () {
            Route::get('/', 'MenuController@index')->name('menu.menu.index');
            Route::get('/create', 'MenuController@create')->name('menu.menu.create');
            Route::post('/store', 'MenuController@store')->name('menu.menu.store');
            Route::get('/edit', 'MenuController@edit')->name('menu.menu.edit');
            Route::post('/update', 'MenuController@update')->name('menu.menu.update');
            Route::get('/delete', 'MenuController@delete')->name('menu.menu.delete');
        });

        Route::prefix('media')->group(function () {
            Route::get('/', 'MediaController@index')->name('media.folder.index');
            Route::get('/folder/store', 'MediaController@folderAdd')->name('media.folder.add');
            Route::post('/folder/update', 'MediaController@folderUpdate')->name('media.folder.update');
            Route::get('/folder', 'MediaController@folder')->name('media.folder');
            Route::post('/folder/move', 'MediaController@folderMove')->name('media.folder.move');
            Route::post('/folder/delete', 'MediaController@folderDelete')->name('media.folder.delete');

            Route::post('/file/store', 'MediaController@fileAdd')->name('media.file.add');
            Route::get('/file', 'MediaController@file');
            Route::post('/file/delete', 'MediaController@fileDelete')->name('media.file.delete');
            Route::post('/file/update', 'MediaController@fileUpdate')->name('media.file.update');
            Route::post('/file/move', 'MediaController@fileMove')->name('media.file.move');
            Route::post('/file/cropp', 'MediaController@cropp');
            Route::get('/file/copy', 'MediaController@fileCopy')->name('media.file.copy');
        });
    });

    Route::prefix('va-outbound')->group(function () {
        Route::post('/{va}', 'SnapVaOutboundController@updateVaStatus')->name('va.status-update');
    });

    Route::group(['middleware' => ['role:user']], function () {
        Route::get('/colors', fn() => view('dashboard.colors'));
        Route::get('/typography', fn() => view('dashboard.typography'));
        Route::get('/charts', fn() => view('dashboard.charts'));
        Route::get('/widgets', fn() => view('dashboard.widgets'));
        Route::get('/404', fn() => view('dashboard.404'));
        Route::get('/500', fn() => view('dashboard.500'));

        Route::prefix('base')->group(function () {
            Route::get('/breadcrumb', fn() => view('dashboard.base.breadcrumb'));
            Route::get('/cards', fn() => view('dashboard.base.cards'));
            Route::get('/carousel', fn() => view('dashboard.base.carousel'));
            Route::get('/collapse', fn() => view('dashboard.base.collapse'));
            Route::get('/forms', fn() => view('dashboard.base.forms'));
            Route::get('/jumbotron', fn() => view('dashboard.base.jumbotron'));
            Route::get('/list-group', fn() => view('dashboard.base.list-group'));
            Route::get('/navs', fn() => view('dashboard.base.navs'));
            Route::get('/pagination', fn() => view('dashboard.base.pagination'));
            Route::get('/popovers', fn() => view('dashboard.base.popovers'));
            Route::get('/progress', fn() => view('dashboard.base.progress'));
            Route::get('/scrollspy', fn() => view('dashboard.base.scrollspy'));
            Route::get('/switches', fn() => view('dashboard.base.switches'));
            Route::get('/tables', fn() => view('dashboard.base.tables'));
            Route::get('/tabs', fn() => view('dashboard.base.tabs'));
            Route::get('/tooltips', fn() => view('dashboard.base.tooltips'));
        });

        Route::prefix('buttons')->group(function () {
            Route::get('/buttons', fn() => view('dashboard.buttons.buttons'));
            Route::get('/button-group', fn() => view('dashboard.buttons.button-group'));
            Route::get('/dropdowns', fn() => view('dashboard.buttons.dropdowns'));
            Route::get('/brand-buttons', fn() => view('dashboard.buttons.brand-buttons'));
        });

        Route::prefix('icon')->group(function () {
            Route::get('/coreui-icons', fn() => view('dashboard.icons.coreui-icons'));
            Route::get('/flags', fn() => view('dashboard.icons.flags'));
            Route::get('/brands', fn() => view('dashboard.icons.brands'));
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/alerts', fn() => view('dashboard.notifications.alerts'));
            Route::get('/badge', fn() => view('dashboard.notifications.badge'));
            Route::get('/modals', fn() => view('dashboard.notifications.modals'));
        });

        Route::resource('notes', 'NotesController');
    });

    Auth::routes();

    Route::resource('resource/{table}/resource', 'ResourceController')->names([
        'index'   => 'resource.index',
        'create'  => 'resource.create',
        'store'   => 'resource.store',
        'show'    => 'resource.show',
        'edit'    => 'resource.edit',
        'update'  => 'resource.update',
        'destroy' => 'resource.destroy',
    ]);
});
