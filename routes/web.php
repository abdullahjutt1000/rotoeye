<?php

use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DowntimeController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ManualRecordsController;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\ProcessStructureController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SimpleController;
use App\Http\Controllers\SleeveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ReportsController;
use App\Http\Middleware\StoreDeviceString;
use Illuminate\Support\Facades\Route;
/// mine code
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\GrpDashboardController;

///end mine
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
Route::get('testing-data', [RecordController::class, 'testing_data']);

//Master data uploading
Route::get('upload/products', [ProductsController::class, 'uploadProducts']);

//Login Routes
Route::get('/', [UserController::class, 'login']);
Route::post('login', [UserController::class, 'doLogin']);
Route::get('check/user/access', [UserController::class, 'checkUserAccess']);
//Route::get('select/machine',[MachineController::class,'selectMachine']);
Route::post('select/machine', [MachineController::class, 'submitMachine']);
Route::get('fetch/local/records', [MachineController::class, 'fetchLocalRecords']);

//User Routes
Route::post('change/user', [UserController::class, 'changeUser']);
Route::get('user/add/{id}', [UserController::class, 'create']);
Route::post('user/add/{id}', [UserController::class, 'store']);
Route::get('users/{id}', [UserController::class, 'index']);
Route::get('user/delete/{id}', [UserController::class, 'destroy']);
Route::get('user/update/{id}/{machine}', [UserController::class, 'edit']);
Route::post('user/update/{id}/{machine}', [UserController::class, 'update']);

//Materials Routes
Route::get('material/add/{id}', [MaterialsController::class, 'create']);
Route::post('material/add/{id}', [MaterialsController::class, 'store']);
Route::get('materials/{id}', [MaterialsController::class, 'index']);
Route::get('material/delete/{id}', [MaterialsController::class, 'destroy']);
Route::get('material/update/{machine_id}/{material_id}', [MaterialsController::class, 'edit']);
Route::post('material/update/{id}', [MaterialsController::class, 'update']);

//Product Routes
Route::get('product/add', [ProductsController::class, 'create']);
Route::post('product/add', [ProductsController::class, 'store']);
Route::get('products/{id}', [ProductsController::class, 'index']);
Route::get('product/update/{product_id}/{id}', [ProductsController::class, 'edit']);
Route::post('product/update/{id}', [ProductsController::class, 'update']);
Route::get('product/delete/{id}', [ProductsController::class, 'destroy']);
Route::get('product/all', [ProductsController::class, 'allProducts']);


//Job Routes
Route::get('change/job', [JobsController::class, 'changeJob']);
Route::post('change/job', [JobsController::class, 'storeChangeJob']);
Route::get('select/job/{id}', [JobsController::class, 'selectJob']);
Route::post('submit/job', [JobsController::class, 'submitJob']);
Route::post('new/production', [JobsController::class, 'newProduction']);

Route::get('production/order/details/{id}', [JobsController::class, 'show']);

Route::get('production/orders', [JobsController::class, 'index']);
Route::get('import/local/data/{machine}', [DashboardController::class, 'importLocalData']);

//Dashboard Routes
Route::get('dashboard', [DashboardController::class, 'index']);
Route::get('dashboard/{id}', [DashboardController::class, 'index']);
Route::get('production/dashboard', [DashboardController::class, 'productionDashboard']);
Route::post('get/record', [DashboardController::class, 'getRecord']);
Route::get('gets/record', [DashboardController::class, 'getRecords']);


Route::get('allocate/downtime', [DowntimeController::class, 'allocateDowntime']);
Route::get('allocate/downtime/manual', [DowntimeController::class, 'allocateDowntimeManual']);
Route::get('get/multiple/time', [DowntimeController::class, 'getMultipleTime']);

//Error Code Routes
Route::get('error-code/add/{id}', [ErrorController::class, 'create']);
Route::post('error-code/add/{id}', [ErrorController::class, 'store']);
Route::get('error-codes/{id}', [ErrorController::class, 'index']);
Route::get('error-code/update/{id}', [ErrorController::class, 'edit']);
Route::post('error-code/update/{id}', [ErrorController::class, 'update']);

//Department Routes
Route::get('departments/{id}', [DepartmentController::class, 'index']);
Route::get('department/add/{id}', [DepartmentController::class, 'create']);
Route::post('department/add/{id}', [DepartmentController::class, 'store']);
Route::get('department/update/{id}/{machineID}', [DepartmentController::class, 'edit']);
Route::post('department/update/{id}/{machineID}', [DepartmentController::class, 'update']);

///// mine routes
//Categories errors  Routes
Route::get('categories/{id}', [CategoriesController::class, 'index']);
Route::get('categories/add/{id}', [CategoriesController::class, 'create']);
Route::post('categories/add/{id}', [CategoriesController::class, 'store']);
Route::get('categories/update/{id}/{machineID}', [CategoriesController::class, 'edit']);
Route::post('categories/update/{id}/{machineID}', [CategoriesController::class, 'update']);
////end mine



//Section Routes
Route::get('section/add/{id}', [SectionController::class, 'create']);
Route::post('section/add/{id}', [SectionController::class, 'store']);
Route::get('sections/{id}', [SectionController::class, 'index']);


//Business Unit Routes
Route::get('business-units/{id}', [BusinessUnitController::class, 'index']);
Route::get('business-unit/update/{id}/{machineID}', [BusinessUnitController::class, 'edit']);
Route::post('business-unit/update/{id}/{machineID}', [BusinessUnitController::class, 'update']);
Route::get('business-unit/add/{id}', [BusinessUnitController::class, 'create']);
Route::post('business-unit/add/{id}', [BusinessUnitController::class, 'store']);

//Companies Routes
Route::get('companies/{id}', [CompanyController::class, 'index']);
Route::get('company/update/{id}/{machineID}', [CompanyController::class, 'edit']);
Route::post('company/update/{id}/{machineID}', [CompanyController::class, 'update']);
Route::get('company/add/{id}', [CompanyController::class, 'create']);
Route::post('company/add/{id}', [CompanyController::class, 'store']);

//Machine Routes
Route::get('machines/{id}', [MachineController::class, 'index']);
Route::get('machine/add/{id}', [MachineController::class, 'create']);
Route::post('machine/add/{id}', [MachineController::class, 'store']);
Route::get('machine/update/{id}', [MachineController::class, 'edit']);
Route::get('machine/updateStatus/{id}', [MachineController::class, 'updateStatus']);  // mine
Route::post('machine/update/{id}/{machineID}', [MachineController::class, 'update']);
Route::get('machine/delete/{id}', [MachineController::class, 'destroy']);
Route::get('allocate/machines/{id}', [MachineController::class, 'allocateMachines']);
Route::post('allocate/machines/{id}', [MachineController::class, 'storeAllocateMachines']);
Route::get('add/more/machines/{id}', [MachineController::class, 'addMoreMachines']);
// Route made by Abdullah
Route::get('machines/{sap_code}/{bin1}', [MachineController::class, 'download']);
// Route made by Abdullah

//Process Routes
Route::get('processes', [ProcessController::class, 'index']);
Route::get('process/update/{id}', [ProcessController::class, 'edit']);
Route::post('process/update/{id}', [ProcessController::class, 'update']);
Route::get('process/add', [ProcessController::class, 'create']);
Route::post('process/add', [ProcessController::class, 'store']);

Route::get('password/expired', [UserController::class, 'passwordExpired']);
Route::post('store/expired/password', [UserController::class, 'storeExpiredPassword']);

Route::get('change/password', [UserController::class, 'changePassword']);
Route::get('change/password/{id}', [UserController::class, 'changePassword']);
Route::get('change/password/{id}', [UserController::class, 'ChangePasswordUser']);
Route::post('change/password/{id}/{machineID}', [UserController::class, 'storeChangePassword']);

//Reporting Routes
//// mine route
Route::post('export-version/{id}', [ReportsController::class, 'reportsExport']);
Route::get('group-dashboard', [GrpDashboardController::class, 'index']);
Route::post('group-dashboard-report-new', [GrpDashboardController::class, 'groupDashboardNew']);
Route::get('group-dashboard-report-new', [GrpDashboardController::class, 'groupDashboardNew']);
Route::post('group-dashboard-report', [GrpDashboardController::class, 'groupDashboard']);
Route::get('group-dashboard-report', [GrpDashboardController::class, 'groupDashboard']);

Route::get('import-group-dashboard-report', [ReportsController::class, 'importgroupDashboard']);
Route::post('import-group-dashboard-report', [ReportsController::class, 'postimportgroupDashboard']);
///
Route::get('reports/{id}', [ReportsController::class, 'reports']);
Route::post('/production/report/{id}', [ReportsController::class, 'ProductionReports']);
Route::patch('/production/report/{id}', [ReportsController::class, 'ProductionReports']);
Route::put('/production/report/{id}', [ReportsController::class, 'ProductionReports']);
Route::post('losses/report/{id}', [ReportsController::class, 'lossesReports']);
Route::get('get/job/performance/{machine_id}/{from}/{to}/{job_id}/{shift}', [ReportsController::class, 'jobPerformance']);
Route::get('get/error/history/{machine_id}/{from}/{to}/{error_id}/{shift}/', [ReportsController::class, 'errorHistory']);

//Downtime Update Route
Route::get('downtime/update/{id}', [DashboardController::class, 'recordsUpdate']);
Route::get('get/historic/records', [DashboardController::class, 'getHistoricRecords']);

//Record Update Routes
Route::get('records/update/{id}', [RecordController::class, 'index']);
Route::post('get/date-wise/machine/jobs', [MachineController::class, 'getDateWiseMachineJobs']);
Route::get('date-wise/machine/records', [RecordController::class, 'getDateWiseMachineRecords']);
Route::post('update/date-wise/machine/records', [RecordController::class, 'updateDateWiseMachineRecords']);

Route::get('get/product-wise/jobs/{id}', [ProductsController::class, 'getJobs']);
Route::get('get/product-wise/processes/{id}', [ProductsController::class, 'getProcesses']);

//API Routes
// mine code
//Route::get('SDjson/{str}',[DashboardController::class,'jsnquery']);
//
Route::get('Num/{num_id}/LDT/{ldt}/Mtr/{mtr}/Rpm/{rpm}', [DashboardController::class, 'live']);
Route::get('Num/{num_id}/LDT/{ldt}/Mtr/{mtr}/Rpm/{rpm}/Sd/{sd}', [DashboardController::class, 'live_with_sd']);
Route::get('rhnum/{rh_num_id}/rdt/{rdt}/rh/{rh_value}/temp/{temp_value}', [DashboardController::class, 'liveRH']);
Route::get('rhnum/{rh_num_id}/ldt/{rdt}/rh/{rh_value}/temp/{temp_value}', [DashboardController::class, 'liveRH']);

Route::get('get/time', [DashboardController::class, 'getTime']);
Route::post('sap/update/products', [ProductsController::class, 'sapUpdate']);

Route::get('process-structure/update/{id}', [ProcessStructureController::class, 'edit']);
Route::post('process-structure/update', [ProcessStructureController::class, 'update']);

Route::get('get/time', [DashboardController::class, 'getTime']);

Route::post('check/product/process', [JobsController::class, 'checkProductProcess']);

Route::post('export/pdf', [ReportsController::class, 'exportPDF']);

Route::get('check/json', [DashboardController::class, 'checkJSON']);
//Excel Exports
Route::get('getExcel', [DashboardController::class, 'getExcel']);
Route::post('circuit/log', [DashboardController::class, 'circuitLog']);

Route::get('check/reports', [ReportsController::class, 'checkReport']);


//Send Email
Route::get('send/email', [DashboardController::class, 'sendEmail']);

Route::get('check/cron', [DashboardController::class, 'checkCron']);

Route::get('get/local/records', [DashboardController::class, 'getLocalRecords']);

//Allocate Downtime
////// mine code
Route::get('downtimewitherrors/update/report/{id}', [DowntimeController::class, 'reportwitherrorsIds']);
Route::post('downtimewitherrorsfilters/update/report/{id}', [DowntimeController::class, 'allocateDownTImeManuallywitherrorsIds']);

Route::get('circuits/update/numberdays/{id}', [DowntimeController::class, 'updatenumberdays']);
Route::post('circuits/update/numberdays/{id}/{machine_id}', [DowntimeController::class, 'numberdaysupdate']);
/////


Route::get('downtime/update/report/{id}', [DowntimeController::class, 'report']);
Route::post('downtime/update/report/{id}', [DowntimeController::class, 'allocateDownTImeManually']);
Route::post('downtime/report/update/{id}', [DowntimeController::class, 'updateDowntimesReport']);

Route::post('records/manual/update', [ManualRecordsController::class, 'manual_update']);
Route::get('manual/get/recent', [ManualRecordsController::class, 'get_recent_record_manual']);
Route::get('records/manual/{id}', [ManualRecordsController::class, 'manual']);
Route::post('manual/newjob', [ManualRecordsController::class, 'newProduction']);
Route::post('manual/submit-job', [ManualRecordsController::class, 'submitJob']);

Route::get('/productivity/update/from/{from}/to/{to}/', [ProductivityController::class, 'oee_dashboard']);
Route::get('/productivity/{id}', [ProductivityController::class, 'index']);
//Sleeves
Route::get('/sleeves/{id}', [SleeveController::class, 'index']);
Route::get('sleeves/add/{id}', [SleeveController::class, 'create']);
Route::post('sleeves/add/{id}', [SleeveController::class, 'store']);
Route::get('/sleeve/update/{sleeve_id}/{machine_id}/{id}', [SleeveController::class, 'edit']);
Route::post('/sleeve/update/{sleeve_id}/{machine_id}/{id}', [SleeveController::class, 'update']);

//Manual Rotoeye
Route::get('/latest/record/{id}', [RecordController::class, 'getLatestRecord']);
Route::post('/manual/allocate/downtime', [RecordController::class, 'manualSaveRecord']);
Route::get('/dashboard/manual/{id}', [DashboardController::class, 'manual_dashboard']);


//Update Job
Route::get('update/job/{id}', [JobsController::class, 'job_details']);
Route::post('job/update/{id}', [JobsController::class, 'update_job']);



// Simple user code

Route::get('simple', [SimpleController::class, 'index']);
Route::post('simplelogin', [SimpleController::class, 'Login']);