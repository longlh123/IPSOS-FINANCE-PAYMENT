<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\VinnetController;
use App\Http\Controllers\AdministrativeDivisionsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MetadataController;
use App\Http\Controllers\ProjectTypeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RespondentController;
use App\Http\Controllers\TechcombankPanelController;
use App\Http\Controllers\TechcombankSurveysController;
use App\Http\Controllers\GotItController;
use App\Http\Middleware\EnsureUserHasRole;

Route::post('/login', [LoginController::class, 'login'])
    ->name('index');

Route::get('/administrative-divisions/old/provinces', [AdministrativeDivisionsController::class, 'get_old_provinces']);
Route::get('/administrative-divisions/old/{provinceId}/districts', [AdministrativeDivisionsController::class, 'get_old_districts']);
Route::get('/administrative-divisions/new/provinces', [AdministrativeDivisionsController::class, 'get_provinces']);
Route::get('/administrative-divisions/new/{provinceId}/districts', [AdministrativeDivisionsController::class, 'get_districts']);

Route::get('/project-management/metadata', [MetadataController::class, 'index']);

Route::get('/project-management/departments', [DepartmentController::class, 'index']);
Route::get('/project-management/{department_id}/teams', [DepartmentController::class, 'get_teams']);
Route::get('/project-management/project-types', [ProjectTypeController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/users', [UserController::class, 'index'])->middleware('ensureUserHasRole:admin');
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/project-management/projects', [ProjectController::class, 'index'])
        ->middleware('ensureUserHasRole:Admin,Scripter');

    Route::get('/project-management/projects/{projectId}/show', [ProjectController::class, 'show'])
        ->middleware('ensureUserHasRole:Admin,Scripter');

    Route::post('/project-management/projects/store', [ProjectController::class, 'store'])->middleware('ensureUserHasRole:Admin,Scripter');
    Route::put('/project-management/projects/{projectId}/update', [ProjectController::class, 'update'])->middleware('ensureUserHasRole:admin');
    Route::put('/project-management/projects/{projectId}/status', [ProjectController::class, 'updateStatus'])->middleware('ensureUserHasRole:Admin,Scripter');
    Route::put('/project-management/projects/{projectId}/disabled', [ProjectController::class, 'updateDisabled'])->middleware('ensureUserHasRole:admin');

    Route::delete('/project-management/projects/{projectId}/provinces/{provinceId}/remove', [ProjectController::class, 'removeProvinceFromProject'])->middleware('ensureUserHasRole:admin');

    Route::get('/project-management/{projectId}/transactions/view', [ProjectController::class, 'showTransactions'])->middleware('ensureUserHasRole:admin,Finance');

    Route::get('/project-management/projects/{projectId}/respondents/show', [RespondentController::class, 'show'])->middleware('ensureUserHasRole:admin');

    Route::get('/project-management/projects/{projectId}/employees/show', [EmployeeController::class, 'show'])->middleware('ensureUserHasRole:admin,Field Manager,Finance');

    Route::get('/project-management/vinnet/merchant/view', [VinnetController::class, 'get_merchant_info'])->middleware('ensureUserHasRole:admin,Finance');
    Route::post('/project-management/vinnet/change-key', [VinnetController::class, 'change_key'])->middleware('ensureUserHasRole:admin,Finance');
    Route::get('/project-management/vinnet/merchantinfo', [VinnetController::class, 'merchantinfo'])->middleware('ensureUserHasRole:admin,Finance');
});

Route::get('/project-management/project/verify-vinnet-token/{internal_code}/{project_name}/{respondent_id}/{remember_token}', [ProjectController::class, 'verify_vinnet_token']);

// Route::post('/project-management/vinnet/change-key', [VinnetController::class, 'change_key']);
Route::post('/project-management/vinnet/transactions', [VinnetController::class, 'perform_multiple_transactions']);
Route::post('/project-management/vinnet/reject-transaction', [VinnetController::class, 'reject_transaction']); //API update status lý do đáp viên không nhận quà qua tin nhắn

Route::post('/project-management/got-it/transaction', [GotItController::class, 'perform_transaction']);
Route::post('/project-management/got-it/reject-transaction', [GotItController::class, 'reject_transaction']); //API update status lý do đáp viên không nhận quà qua tin nhắn



Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);

Route::post('/reset-password', [ResetPasswordController::class, 'reset']);


Route::get('/techcombank-panel/users', [TechcombankPanelController::class, 'index']);
Route::get('/techcombank-panel/{table_name}/{column_name}', [TechcombankPanelController::class, 'getCount']);
Route::get('/techcombank-panel/total-members', [TechcombankPanelController::class, 'getTotalMembers']);
Route::get('/techcombank-panel/provinces', [TechcombankPanelController::class, 'getProvince']);
Route::get('/techcombank-panel/age-group', [TechcombankPanelController::class, 'getAgeGroup']);
Route::get('/techcombank-panel/occupation', [TechcombankPanelController::class, 'getOccupation']);
Route::get('/techcombank-panel/channels', [TechcombankPanelController::class, 'getChannels']);
Route::get('/techcombank-panel/products', [TechcombankPanelController::class, 'getProducts']);
Route::get('/techcombank-panel/venn-products', [TechcombankPanelController::class, 'getVennProducts']);
Route::get('/techcombank-panel/panellist', [TechcombankPanelController::class, 'getPanellist']);

Route::get('/techcombank-panel/surveys', [TechcombankSurveysController::class, 'index']);

Route::post('/cmc-telecom/sendsms', [VinnetController::class, 'cmc_telecom_send_sms']);







