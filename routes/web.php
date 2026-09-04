<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LiveStreamController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CreditsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\FacebookWebhookController;

// Public
Route::get('/', fn() => view('welcome'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

// Portal Flow
Route::get('/pt', [\App\Http\Controllers\PortalController::class, 'index']);
Route::post('/pt/connect', [\App\Http\Controllers\PortalController::class, 'connect']);

// Facebook Auth
Route::prefix('auth')->group(function () {
    Route::get('facebook', [AuthController::class, 'redirectToFacebook'])->name('auth.facebook');
    Route::get('facebook/callback', [AuthController::class, 'handleFacebookCallback']);
});

Route::get('/webhooks/facebook/messenger', [FacebookWebhookController::class, 'verify']);
Route::post('/webhooks/facebook/messenger', [FacebookWebhookController::class, 'handle']);

// Protected
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/index', '/dashboard');
    Route::redirect('/stats', '/dashboard');
    Route::redirect('/posts', '/post');
    Route::redirect('/messages', '/customers/messenger/messages');
    Route::redirect('/credit', '/credits');
    Route::get('/credits', [CreditsController::class, 'index'])->name('credits.index');
    Route::get('/settings', [ShopController::class, 'settings'])->name('settings.index');
    Route::redirect('/shops', '/settings')->name('shops.index');
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::get('/integrations', [IntegrationsController::class, 'index'])->name('integrations.index');
    Route::view('/help', 'help.index')->name('help.index');
    Route::get('/accounts', [ShopController::class, 'accounts'])->name('accounts');
    Route::post('/accounts', [ShopController::class, 'updateAccount'])->name('accounts.update');
    Route::get('/post', [BroadcastController::class, 'index']);
    Route::get('/chatOrders', [ChatController::class, 'index']);
    Route::get('/new_chat', [ChatController::class, 'index']);
    Route::redirect('/deposit', '/orders?type=hold');
    Route::redirect('/reportOrderDetails', '/reports');
    Route::redirect('/reportOrderShipping', '/reports?type=shipping');
    Route::redirect('/setting/shop', '/shops');
    Route::redirect('/setting/shipping', '/packing');
    Route::redirect('/setting/cod', '/financial');
    Route::get('/userAccess', [ShopController::class, 'access']);
    Route::get('/utility-templates', [ShopController::class, 'templates']);
    Route::redirect('/tutorials', '/tutorial');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products/import', [ProductController::class, 'importIndex'])->name('products.import');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.import.store');
    Route::get('/products/import/options', [ProductController::class, 'importIndex'])->defaults('mode', 'options')->name('products.import.options');
    Route::get('/products/print', [ProductController::class, 'printIndex'])->name('products.print');
    Route::get('/products/{id}/options', [ProductController::class, 'optionShell'])->name('products.options');
    Route::resource('/shops', ShopController::class)->except(['index']);
    Route::put('/shops/{id}/messenger', [ShopController::class, 'updateMessenger'])->name('shops.messenger');
    Route::post('/shops/{id}/tiktok/verify', [ShopController::class, 'verifyTikTokUsername'])->name('shops.tiktok.verify');
    Route::post('/shops/{id}/tiktok/check', [ShopController::class, 'checkTikTokLive'])->name('shops.tiktok.check');
    Route::post('/shops/{id}/shipping/check', [ShopController::class, 'checkShippingConnection'])->name('shops.shipping.check');
    Route::post('/shops/{id}/payment/check', [ShopController::class, 'checkPaymentConnection'])->name('shops.payment.check');
    Route::resource('/products', ProductController::class);
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-delete');
    
    // Legacy AJAX aliases for tests and backwards compatibility
    Route::post('/ajaxDeleteProducts', function (Illuminate\Http\Request $request) {
        $ids = $request->input('ids', []);
        App\Models\Product::whereIn('id', $ids)->delete();
        return response()->json(['status' => 'success']);
    });
    Route::post('/ajaxAdjustPrice', function (Illuminate\Http\Request $request) {
        $ids = $request->input('ids', []);
        $adj = (float)$request->input('adjustment');
        $type = $request->input('type');
        foreach (App\Models\Product::whereIn('id', $ids)->get() as $p) {
            if ($type === 'fixed') $p->update(['price' => $adj]);
            if ($type === 'percentage') $p->update(['price' => $p->price * (1 + $adj/100)]);
        }
        return response()->json(['status' => 'success']);
    });
    Route::post('/ajaxAdjustStock', function (Illuminate\Http\Request $request) {
        $ids = $request->input('ids', []);
        $adj = (int)$request->input('adjustment');
        $type = $request->input('type');
        foreach (App\Models\Product::whereIn('id', $ids)->get() as $p) {
            if ($type === 'set') $p->update(['stock' => $adj]);
            if ($type === 'add') $p->update(['stock' => $p->stock + $adj]);
        }
        return response()->json(['status' => 'success']);
    });

    // Alias matching original URLs
    Route::get('/printProductReport', [ProductController::class, 'printIndex'])->name('products.print.report');
    Route::get('/printStockReport', [ProductController::class, 'exportExcel'])->name('products.export.stock');
    // Product upload image
    Route::post('/products/{id}/image', [ProductController::class, 'uploadImage'])->name('products.image');
    // Product variants AJAX API
    Route::get('/products/{id}/variants', [ProductController::class, 'variantsIndex'])->name('products.variants.index');
    Route::post('/products/{id}/variants', [ProductController::class, 'variantsStore'])->name('products.variants.store');
    Route::post('/products/{id}/variants/bulk', [ProductController::class, 'variantsBulkStore'])->name('products.variants.bulk');
    Route::put('/products/{id}/variants/{variantId}', [ProductController::class, 'variantsUpdate'])->name('products.variants.update');
    Route::delete('/products/{id}/variants/{variantId}', [ProductController::class, 'variantsDestroy'])->name('products.variants.destroy');
    Route::post('/products/{id}/variants/bulk-delete', [ProductController::class, 'variantsBulkDestroy'])->name('products.variants.bulk-delete');
    Route::post('/products/{id}/variants/bulk-stock', [ProductController::class, 'variantsBulkStock'])->name('products.variants.bulk-stock');
    Route::post('/products/{id}/variants/bulk-price', [ProductController::class, 'variantsBulkPrice'])->name('products.variants.bulk-price');

    Route::resource('/orders', OrderController::class);
    Route::get('/live', [LiveStreamController::class, 'index'])->name('live.index');
    Route::post('/live/check-current', [LiveStreamController::class, 'checkCurrent'])->name('live.check-current');
    Route::get('/live/latest/copy', [LiveStreamController::class, 'copyLatest'])->name('live.copy-latest');
    Route::get('/live/print', [LiveStreamController::class, 'printIndex'])->name('live.print');
    Route::get('/live/{id}/details', [LiveStreamController::class, 'show'])->name('live.show');
    Route::post('/live/start', [LiveStreamController::class, 'start'])->name('live.start');
    Route::post('/live/connect-current', [LiveStreamController::class, 'connectCurrent'])->name('live.connect-current');
    Route::post('/live/stop/{id}', [LiveStreamController::class, 'stop'])->name('live.stop');
    Route::delete('/live/{id}', [LiveStreamController::class, 'destroy'])->name('live.destroy');
    Route::get('/packing', [PackingController::class, 'index']);
    Route::post('/packing/tracking/{id}', [PackingController::class, 'updateTracking']);
    Route::get('/broadcasts', [BroadcastController::class, 'index']);
    Route::post('/broadcasts', [BroadcastController::class, 'send']);
    Route::get('/tutorial', [TutorialController::class, 'index']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::resource('/payments', PaymentController::class);
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/messenger/readiness', [CustomerController::class, 'messengerReadiness'])->name('customers.messenger.readiness');
    Route::get('/customers/messenger/conflicts', [CustomerController::class, 'messengerConflicts'])->name('customers.messenger.conflicts');
    Route::get('/customers/messenger/send-control', [CustomerController::class, 'messengerSendControl'])->name('customers.messenger.send-control');
    Route::get('/customers/messenger-mappings/{mapping}', [CustomerController::class, 'showMessengerMapping'])->name('customers.messenger-mappings.show');
    Route::post('/customers/messenger-mappings/{mapping}/action', [CustomerController::class, 'updateMessengerMapping'])->name('customers.messenger-mappings.action');
    Route::post('/customers/messenger-mappings/{mapping}/orders/{order}/attach', [CustomerController::class, 'attachMessengerOrder'])->name('customers.messenger-mappings.orders.attach');
    Route::post('/customers/messenger-mappings/{mapping}/orders/{order}/review', [CustomerController::class, 'markMessengerOrderNeedsReview'])->name('customers.messenger-mappings.orders.review');
    Route::post('/customers/messenger-order-links/{link}/detach', [CustomerController::class, 'detachMessengerOrder'])->name('customers.messenger-order-links.detach');
    Route::post('/customers/messenger-order-links/{link}/action', [CustomerController::class, 'updateMessengerOrderLink'])->name('customers.messenger-order-links.action');
    Route::post('/customers/messenger-mappings/{mapping}/reply-drafts', [CustomerController::class, 'storeReplyDraft'])->name('customers.messenger-mappings.reply-drafts.store');
    Route::post('/customers/messenger-mappings/{mapping}/reply-drafts/{draft}', [CustomerController::class, 'runReplyDraft'])->name('customers.messenger-mappings.reply-drafts.action');
    Route::get('/customers/messenger/messages', [CustomerController::class, 'messengerMessages'])->name('customers.messenger.messages');
    Route::get('/customers/messenger/messages/{message}', [CustomerController::class, 'messengerMessageDetail'])->name('customers.messenger.messages.show');
    Route::post('/customers/messenger/messages/{message}/resolve', [CustomerController::class, 'resolveMessengerMessage'])->name('customers.messenger.messages.resolve');
    Route::get('/chat', [ChatController::class, 'index']);
    Route::get('/financial', [FinancialController::class, 'index']);
    Route::get('/stocks', [StockController::class, 'index']);
    Route::get('/reports', [ReportController::class, 'index']);
});

// API (no CSRF, no auth — grabber key)
Route::prefix('api')->group(function () {
    Route::get('/grabber/ping', [App\Http\Controllers\Api\GrabberController::class, 'ping']);
    Route::get('/grabber/streams', [App\Http\Controllers\Api\GrabberController::class, 'getStreams']);
    Route::post('/grabber/orders/tiktok-code', [App\Http\Controllers\Api\GrabberController::class, 'createOrderFromCode']);
});
// Health check
Route::get('/health', [App\Http\Controllers\HealthController::class, 'index']);
