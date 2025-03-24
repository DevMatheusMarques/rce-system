<?php
use App\Http\Controllers\FrontRenderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/register', [UserController::class, 'registerGuest']);
Route::post('/password/forgot', [UserController::class, 'passwordForgot']);
Route::post('/password/reset', [UserController::class, 'passwordReset']);

Route::get('/', function () {
   return redirect()->route('auth');
});

Route::get('/proxy/old/{endpoint}', function ($endpoint) {
    $cnpj = request()->only(['cnpj']);

    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', "https://api-publica.speedio.com.br/$endpoint", [
        'query' => $cnpj
    ]);

    return response($response->getBody()->getContents(), $response->getStatusCode())
        ->header('Content-Type', $response->getHeaderLine('Content-Type'));
});
Route::get('/consult/{cnpj}', function ($cnpj) {

    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', "https://publica.cnpj.ws/cnpj/$cnpj");

    return response($response->getBody()->getContents(), $response->getStatusCode())
        ->header('Content-Type', $response->getHeaderLine('Content-Type'));
});

Route::get('auth', [FrontRenderController::class, 'authView'])->name('auth');
Route::get('password/reset/{token}', [FrontRenderController::class, 'resetPassword']);
Route::get('user/logged', [UserController::class, 'logged']);
Route::get('password/verify', [FrontRenderController::class, 'verifyResetPassword']);

Route::middleware('level:admin')->prefix('admin')->group(function () {

    /**
     * Usar com cautela os métodos 'delete', pois a exclusão é em cascata.
     */
    Route::get('/access/only/admin', function () {
        return response()->json([
            'status' => 200,
        ], 200);
    });

    Route::get('/', [FrontRenderController::class, 'layoutView'])->name('admin');

    Route::post('password/reset/authenticated', [UserController::class, 'passwordResetAuthenticated']);
    Route::put('user/email/update', [UserController::class, 'updateEmail']);
    Route::post('user/profile/picture', [UserController::class, 'addProfilePicture']);

    Route::get('user/get', [UserController::class, 'getAll']);
    Route::post('user/register', [UserController::class, 'register']);
    Route::get('user/getbyid/{id}', [UserController::class, 'getById']);
    Route::put('user/update/{id}', [UserController::class, 'update']);
    Route::delete('user/delete/{id}', [UserController::class, 'delete']);

    Route::post('product/register', [ProductController::class, 'register']);
    Route::get('product/get', [ProductController::class, 'getAll']);
    Route::get('product/getbyid/{id}', [ProductController::class, 'getById']);
    Route::post('product/update/{id}', [ProductController::class, 'update']); //deve ser post, pois não consigo enviar imagem com put
    Route::delete('product/delete', [ProductController::class, 'delete']);
    Route::post('product/picture/{id}', [ProductController::class, 'addPicture']);
    Route::put('product/update/statuses', [ProductController::class, 'updateStatuses']);

    Route::post('supplier/register', [SupplierController::class, 'register']);
    Route::get('supplier/get', [SupplierController::class, 'getAll']);
    Route::get('supplier/getbyid/{id}', [SupplierController::class, 'getById']);
    Route::put('supplier/update/{id}', [SupplierController::class, 'update']);
    Route::delete('supplier/delete/{id}', [SupplierController::class, 'delete']);

    Route::get('purchase/export/pdf/{id}', [PurchaseController::class, 'exportPDF']);
    Route::post('purchase/register', [PurchaseController::class, 'register']);
    Route::put('purchase/update/{id}', [PurchaseController::class, 'update']);
    Route::put('purchase/status/update', [PurchaseController::class, 'updateStatus']);
    Route::get('purchase/getbyid/{id}', [PurchaseController::class, 'getById']);
    Route::get('purchase/get', [PurchaseController::class, 'getAll']);
    Route::delete('purchase/delete/{id}', [PurchaseController::class, 'delete']);

    Route::post('order/register', [OrderController::class, 'register']);
    Route::post('order/update/{id}', [OrderController::class, 'update']);
    Route::put('order/status/update', [OrderController::class, 'updateStatus']);
    Route::get('order/getbyid/{id}', [OrderController::class, 'getById']);
    Route::get('order/get', [OrderController::class, 'getAll']);
    Route::get('order/sector/comparison/get', [OrderController::class, 'getSectorComparison']);
    Route::get('order/ranking/product/requester/get', [OrderController::class, 'getTopProductsAndUsers']);
});

Route::middleware('level:manager')->prefix('manager')->group(function () {
    Route::get('/access/only/manager', function () {
        return response()->json([
            'status' => 200,
        ], 200);
    });

    Route::get('/', [FrontRenderController::class, 'layoutView'])->name('manager');

    Route::get('user/get', [UserController::class, 'getAll']);
    Route::put('user/update/{id}', [UserController::class, 'update']);

    Route::post('password/reset/authenticated', [UserController::class, 'passwordResetAuthenticated']);
    Route::put('user/email/update', [UserController::class, 'updateEmail']);
    Route::post('user/profile/picture', [UserController::class, 'addProfilePicture']);

    Route::post('user/register', [UserController::class, 'register']);
    Route::get('user/get', [UserController::class, 'getAll']);
    Route::get('user/getbyid/{id}', [UserController::class, 'getById']);
    Route::put('user/update/{id}', [UserController::class, 'update']);

    Route::post('product/register', [ProductController::class, 'register']);
    Route::get('product/get', [ProductController::class, 'getAll']);
    Route::get('product/getbyid/{id}', [ProductController::class, 'getById']);
    Route::post('product/update/{id}', [ProductController::class, 'update']);
    Route::post('product/picture/{id}', [ProductController::class, 'addPicture']);
    Route::put('product/update/statuses', [ProductController::class, 'updateStatuses']);

    Route::post('supplier/register', [SupplierController::class, 'register']);
    Route::get('supplier/get', [SupplierController::class, 'getAll']);
    Route::get('supplier/getbyid/{id}', [SupplierController::class, 'getById']);
    Route::put('supplier/update/{id}', [SupplierController::class, 'update']);

    Route::get('purchase/export/pdf/{id}', [PurchaseController::class, 'exportPDF']);
    Route::put('purchase/status/update', [PurchaseController::class, 'updateStatus']);
    Route::post('purchase/register', [PurchaseController::class, 'register']);
    Route::put('purchase/update/{id}', [PurchaseController::class, 'update']);
    Route::get('purchase/getbyid/{id}', [PurchaseController::class, 'getById']);
    Route::get('purchase/get', [PurchaseController::class, 'getAll']);

    Route::post('order/register', [OrderController::class, 'register']);
    Route::post('order/update/{id}', [OrderController::class, 'update']);
    Route::put('order/status/update', [OrderController::class, 'updateStatus']);
    Route::get('order/getbyid/{id}', [OrderController::class, 'getById']);
    Route::get('order/get', [OrderController::class, 'getAll']);

    Route::get('order/sector/comparison/get', [OrderController::class, 'getSectorComparison']);
    Route::get('order/ranking/product/requester/get', [OrderController::class, 'getTopProductsAndUsers']);
});

Route::middleware('level:operator')->prefix('operator')->group(function () {
    Route::get('/access/only/operator', function () {
        return response()->json([
            'status' => 200,
        ], 200);
    });

    Route::get('/', [FrontRenderController::class, 'layoutView'])->name('operator');

    Route::get('user/get', [UserController::class, 'getAll']);
    Route::put('user/update/{id}', [UserController::class, 'update']);

    Route::post('password/reset/authenticated', [UserController::class, 'passwordResetAuthenticated']);
    Route::put('user/email/update', [UserController::class, 'updateEmail']);
    Route::post('user/profile/picture', [UserController::class, 'addProfilePicture']);

    Route::post('product/register', [ProductController::class, 'register']);
    Route::get('product/get', [ProductController::class, 'getAll']);
    Route::get('product/getbyid/{id}', [ProductController::class, 'getById']);
    Route::post('product/update/{id}', [ProductController::class, 'update']);
    Route::post('product/picture/{id}', [ProductController::class, 'addPicture']);
    Route::put('product/update/statuses', [ProductController::class, 'updateStatuses']);

    Route::post('supplier/register', [SupplierController::class, 'register']);
    Route::get('supplier/get', [SupplierController::class, 'getAll']);
    Route::get('supplier/getbyid/{id}', [SupplierController::class, 'getById']);
    Route::put('supplier/update/{id}', [SupplierController::class, 'update']);

    Route::post('order/register', [OrderController::class, 'register']);
    Route::post('order/update/{id}', [OrderController::class, 'update']);
    Route::put('order/status/update', [OrderController::class, 'updateStatus']);
    Route::get('order/getbyid/{id}', [OrderController::class, 'getById']);
    Route::get('order/get', [OrderController::class, 'getAll']);

    Route::post('purchase/register', [PurchaseController::class, 'register']);
    Route::put('purchase/update/{id}', [PurchaseController::class, 'update']);
    Route::get('purchase/export/pdf/{id}', [PurchaseController::class, 'exportPDF']);
    Route::put('purchase/status/update', [PurchaseController::class, 'updateStatus']);
    Route::get('purchase/getbyid/{id}', [PurchaseController::class, 'getById']);
    Route::get('purchase/get', [PurchaseController::class, 'getAll']);

});

require __DIR__ . '/auth.php';
