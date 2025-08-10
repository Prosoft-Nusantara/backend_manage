<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgresController;
use App\Http\Controllers\ExportController;
// use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::group([
  'prefix' => 'auth'
], function () {
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login', [AuthController::class, 'login']);
  Route::group([
    'middleware' => 'auth:api'
  ], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('list-user', [AuthController::class, 'listUser']);
    Route::post('update-users/{id}', [AuthController::class, 'resetPassword']);
    Route::post('update-pw/{id}', [AuthController::class, 'updatePw']);
    Route::delete('delete-user/{id}', [AuthController::class, 'deleteUser']);
    Route::post('delete-users', [AuthController::class, 'deleteUsers']);

    Route::post('ganti-password', [AuthController::class, 'ubahPassword']);

    Route::get('active-token/{id}', [AuthController::class, 'getActiveToken']);
  });
});


Route::group([
  'prefix' => 'karyawan'
], function () {
  Route::group([
    'middleware' => 'auth:api',
  ], function () {
    // Divisi
    Route::get('/divisi', [KaryawanController::class, 'getAllDivisi']);
    Route::post('/divisi', [KaryawanController::class, 'createDivisi']);
    Route::post('/divisi/{id}', [KaryawanController::class, 'updateDivisi']);
    Route::delete('/divisi/{id}', [KaryawanController::class, 'deleteDivisi']);

    // Unit
    Route::get('/unit', [KaryawanController::class, 'getAllUnit']);
    Route::post('/unit', [KaryawanController::class, 'createUnit']);
    Route::post('/unit/{id}', [KaryawanController::class, 'updateUnit']);
    Route::delete('/unit/{id}', [KaryawanController::class, 'deleteUnit']);

    // Manager
    Route::get('/manager', [KaryawanController::class, 'getAllManager']);
    Route::post('/manager', [KaryawanController::class, 'createManager']);
    Route::post('/manager/{id}', [KaryawanController::class, 'updateManager']);
    Route::delete('/manager/{id}', [KaryawanController::class, 'deleteManager']);

    // karyawan
    Route::get('/list', [KaryawanController::class, 'getAllKaryawan']);
    Route::get('/by-manager', [KaryawanController::class, 'getMyKaryawan']);
    Route::post('/create', [KaryawanController::class, 'createKaryawan']);
    Route::post('/update/{id}', [KaryawanController::class, 'updateKaryawan']);
    Route::delete('/delete/{id}', [KaryawanController::class, 'deleteKaryawan']);

    // Coordinator
    Route::get('/coordinator', [KaryawanController::class, 'getAllCoordinator']);
    Route::post('/coordinator', [KaryawanController::class, 'createCoordinator']);
    Route::post('/coordinator/{id}', [KaryawanController::class, 'updateCoordinator']);
    Route::delete('/coordinator/{id}', [KaryawanController::class, 'deleteCoordinator']);

    // Operasional
    Route::get('/operasional', [KaryawanController::class, 'getAllOperasional']);
    Route::post('/operasional', [KaryawanController::class, 'createOperasional']);
    Route::post('/operasional/{id}', [KaryawanController::class, 'updateOperasional']);
    Route::delete('/operasional/{id}', [KaryawanController::class, 'deleteOperasional']);
  });
});

Route::group([
  'prefix' => 'project'
], function () {
  Route::group([
    'middleware' => 'auth:api',
  ], function () {
    Route::get('/all', [ProjectController::class, 'getAllProjects']);
    Route::get('/ongoing', [ProjectController::class, 'getProjectsOnGoing']);
    Route::get('/piutang', [ProjectController::class, 'getProjectsPiutang']);
    Route::get('/lunas', [ProjectController::class, 'getProjectsLunas']);
    Route::get('/manager', [ProjectController::class, 'getProjectsByManager']);
    Route::post('/create', [ProjectController::class, 'createProject']);
    Route::post('/complete/{id}', [ProjectController::class, 'completeProject']);
    Route::post('/payment/{id}', [ProjectController::class, 'paymentProyek']);
    Route::post('/update/{id}', [ProjectController::class, 'updateProject']);
    Route::delete('/delete/{id}', [ProjectController::class, 'deleteProject']);
    Route::post('/action', [ProjectController::class, 'actionProject']);

    Route::get('/export/{status?}', [ExportController::class, 'export']);
    
    Route::post('/update-aktifitas/{id}', [ProjectController::class, 'updateAktifitas']);
    Route::post('/aktifitas/{id}', [ProjectController::class, 'selesaikanAktifitas']);
    // tim
    Route::get('/detail/{id}', [ProjectController::class, 'detailProject']);
    Route::get('/karyawan', [ProjectController::class, 'listKaryawan']);
    Route::post('/add-tim/{id}', [ProjectController::class, 'addTimToProject']);
    Route::delete('/delete-tim/{id}', [ProjectController::class, 'deleteTimFromProject']);
  });
});

Route::group([
  'prefix' => 'dashboard'
], function () {
  Route::group([
    'middleware' => 'auth:api',
  ], function () {
    Route::get('/highlight', [DashboardController::class, 'highlightProject']);
  });
});

Route::group([
  'prefix' => 'progres'
], function () {
  Route::group([
    'middleware' => 'auth:api',
  ], function () {
    Route::get('/list/{id}', [ProgresController::class, 'listProgresByProject']);
    Route::post('/create', [ProgresController::class, 'createProgres']);
    Route::post('/update', [ProgresController::class, 'updateProgres']);
    Route::delete('/delete', [ProgresController::class, 'deleteProgres']);
  });
});
