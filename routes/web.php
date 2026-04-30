<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\ArtworkSellController;
use App\Http\Controllers\DemoArtworkController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ClassEventController;
use App\Http\Controllers\ArtistBrowseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MyClassesController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClassCheckoutController;
use App\Http\Controllers\OrderCheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ArtistOrderController;
use App\Http\Controllers\OrderSummaryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\ArtistCustomOrderController;
use App\Http\Controllers\BulkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/logout-success', function () {
    return view('logoutSuccess');
})->name('logout.success');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// --- STRIPE CALLBACK ROUTES (outside auth) ---
Route::get('/checkout/class/success', [ClassCheckoutController::class, 'success'])->name('class.checkout.success');
Route::get('/checkout/order/success', [OrderCheckoutController::class, 'success'])->name('order.checkout.success');
Route::get('/checkout/order/cancel',  [OrderCheckoutController::class, 'cancel']) ->name('order.checkout.cancel');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-classes', [MyClassesController::class, 'index'])->name('my.classes');

    // --- ORDER ROUTES (buyer) ---
    Route::get('/my-orders',                   [OrderController::class, 'index'])          ->name('orders.index');
    Route::get('/my-orders/{order}',           [OrderController::class, 'show'])           ->name('orders.show');
    Route::post('/my-orders/{order}/complete', [OrderController::class, 'complete'])       ->name('orders.complete');
    Route::get('/my-orders/{order}/receipt',   [OrderController::class, 'downloadReceipt'])->name('orders.receipt');

    // --- REVIEW ROUTES ---
    Route::get('/my-orders/{order}/review',          [ReviewController::class, 'create']) ->name('reviews.create');
    Route::post('/reviews',                          [ReviewController::class, 'store'])  ->name('reviews.store');
    Route::get('/reviews/{order}/{review}/complete', [ReviewController::class, 'complete'])->name('reviews.complete');
    Route::get('/reviews/{review}/edit',             [ReviewController::class, 'edit'])   ->name('reviews.edit');
    Route::put('/reviews/{review}',                  [ReviewController::class, 'update']) ->name('reviews.update');
    Route::delete('/reviews/{review}',               [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // --- FEEDBACK ROUTES ---
    Route::get('/feedback',  [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store']) ->name('feedback.store');

    // --- CART ROUTES ---
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/',        [CartController::class, 'index'])  ->name('index');
        Route::post('/add',    [CartController::class, 'add'])    ->name('add');
        Route::post('/update', [CartController::class, 'update']) ->name('update');
        Route::post('/remove', [CartController::class, 'remove']) ->name('remove');
        Route::post('/clear',  [CartController::class, 'clear'])  ->name('clear');
    });

    // --- CLASS CHECKOUT ROUTES ---
    Route::prefix('checkout/class')->name('class.checkout.')->group(function () {
        Route::get('/cancel/{classEventId}',   [ClassCheckoutController::class, 'cancel']) ->name('cancel');
        Route::get('/{classEventId}',          [ClassCheckoutController::class, 'show'])   ->name('show');
        Route::post('/{classEventId}/process', [ClassCheckoutController::class, 'process'])->name('process');
    });

    // --- ORDER/PRODUCT CHECKOUT ROUTES ---
    Route::prefix('checkout/order')->name('order.checkout.')->group(function () {
        Route::get('/',              [OrderCheckoutController::class, 'show'])   ->name('show');
        Route::post('/process',      [OrderCheckoutController::class, 'process'])->name('process');
        Route::get('/{order}/repay', [OrderCheckoutController::class, 'repay']) ->name('repay');
    });

    // --- USER PROFILE ROUTES ---
    Route::prefix('profile')->group(function () {
        Route::get('/',                [UserProfileController::class, 'show'])                  ->name('user.profile.show');
        Route::get('/edit',            [UserProfileController::class, 'edit'])                  ->name('user.profile.edit');
        Route::put('/update',          [UserProfileController::class, 'update'])                ->name('user.profile.update');
        Route::post('/update-image',   [UserProfileController::class, 'updateProfileImage'])   ->name('user.profile.image.update');
        Route::get('/change-password', [UserProfileController::class, 'showChangePasswordForm'])->name('user.profile.change-password');
        Route::put('/change-password', [UserProfileController::class, 'updatePassword'])        ->name('user.profile.update-password');
    });

    // --- STUDIO ROUTES ---
    Route::get('/studio',           [StudioController::class, 'index'])          ->name('studio');
    Route::get('/studio/register',  [StudioController::class, 'showRegisterForm'])->name('studio.register');
    Route::post('/studio/register', [StudioController::class, 'register'])       ->name('studio.register.submit');

    // --- ARTIST ROUTES ---
    Route::prefix('artist')->group(function () {
        Route::get('/studio',          [ArtistController::class, 'profile']) ->name('artist.profile');
        Route::get('/profile/edit',    [ArtistController::class, 'edit'])    ->name('artist.profile.edit');
        Route::post('/profile/update', [ArtistController::class, 'update'])  ->name('artist.profile.update');
        Route::get('/dashboard',       [ArtistController::class, 'dashboard'])->name('artist.dashboard');

        Route::post('/demo/upload',           [DemoArtworkController::class, 'store'])     ->name('artist.demo.upload');
        Route::get('/demo/{id}/edit',         [DemoArtworkController::class, 'edit'])      ->name('artist.demo.edit');
        Route::post('/demo/{id}',             [DemoArtworkController::class, 'update'])    ->name('artist.demo.update');
        Route::delete('/demo/{id}',           [DemoArtworkController::class, 'destroy'])   ->name('artist.demo.delete');
        Route::post('/demo/{id}/unlink-sell', [DemoArtworkController::class, 'unlinkSell'])->name('artist.demo.unlink-sell');

        Route::post('/artwork/sell',             [ArtworkSellController::class, 'store'])     ->name('artist.artwork.sell');
        Route::get('/artwork/{id}/edit',         [ArtworkSellController::class, 'edit'])      ->name('artist.artwork.edit');
        Route::post('/artwork/{id}',             [ArtworkSellController::class, 'update'])    ->name('artist.artwork.update');
        Route::delete('/artwork/{id}',           [ArtworkSellController::class, 'destroy'])   ->name('artist.artwork.delete');
        Route::post('/artwork/{id}/unlink-demo', [ArtworkSellController::class, 'unlinkDemo'])->name('artist.artwork.unlink-demo');

        Route::post('/demo/reorder', [DemoArtworkController::class, 'reorder'])->name('artist.demo.reorder');

        // --- ARTIST ORDER ROUTES (seller) ---
        Route::get('/orders',                 [ArtistOrderController::class, 'index']) ->name('artist.orders');
        Route::post('/orders/{order}/accept', [ArtistOrderController::class, 'accept'])->name('artist.orders.accept');
        Route::post('/orders/{order}/ship',   [ArtistOrderController::class, 'ship'])  ->name('artist.orders.ship');

        // --- ORDER SUMMARY (seller dashboard) ---
        Route::get('/order-summary',            [OrderSummaryController::class, 'index'])    ->name('artist.order.summary');
        Route::get('/order-summary/chart-data', [OrderSummaryController::class, 'chartData'])->name('artist.order.summary.chart');

        // --- CUSTOM ORDER REQUESTS (seller) ---
        Route::get('/custom-orders',                       [ArtistCustomOrderController::class, 'index']) ->name('artist.custom-orders.index');
        Route::get('/custom-orders/{customOrder}',         [ArtistCustomOrderController::class, 'show'])  ->name('artist.custom-orders.show');
        Route::post('/custom-orders/{customOrder}/accept', [ArtistCustomOrderController::class, 'accept'])->name('artist.custom-orders.accept');
        Route::post('/custom-orders/{customOrder}/refuse', [ArtistCustomOrderController::class, 'refuse'])->name('artist.custom-orders.refuse');

        // --- BULK ORDER ROUTES (seller) ---
        Route::get('/bulk-orders',              [BulkOrderController::class, 'sellerIndex'])->name('artist.bulk-orders.index');
        Route::get('/bulk-orders/{id}',         [BulkOrderController::class, 'sellerShow']) ->name('artist.bulk-orders.show');
        Route::post('/bulk-orders/{id}/accept', [BulkOrderController::class, 'accept'])     ->name('artist.bulk-orders.accept');
        Route::post('/bulk-orders/{id}/refuse', [BulkOrderController::class, 'refuse'])     ->name('artist.bulk-orders.refuse');
    });

    // --- CLASS AND EVENT ROUTES ---
    Route::prefix('class-event')->group(function () {
        Route::get('/browse',                           [ClassEventController::class, 'browse'])         ->name('class.event.browse');
        Route::get('/',                                 [ClassEventController::class, 'index'])          ->name('class.event.index');
        Route::post('/',                                [ClassEventController::class, 'store'])          ->name('class.event.store');
        Route::get('/{id}/data',                        [ClassEventController::class, 'getData'])        ->name('class.event.data');
        Route::get('/{id}/participants',                [ClassEventController::class, 'getParticipants'])->name('class.event.participants');
        Route::delete('/{id}/participants/{bookingId}', [ClassEventController::class, 'dropParticipant'])->name('class.event.participants.drop');
        Route::get('/{id}/edit',                        [ClassEventController::class, 'edit'])           ->name('class.event.edit');
        Route::post('/{id}/enroll',                     [ClassEventController::class, 'enroll'])         ->name('class.event.enroll');
        Route::delete('/{id}/enroll',                   [ClassEventController::class, 'unenroll'])       ->name('class.event.unenroll');
        Route::get('/{id}',                             [ClassEventController::class, 'show'])           ->name('class.event.show');
        Route::post('/{id}',                            [ClassEventController::class, 'update'])         ->name('class.event.update');
        Route::delete('/{id}',                          [ClassEventController::class, 'destroy'])        ->name('class.event.destroy');
    });

    // --- BROWSE & PRODUCT ROUTES ---
    Route::get('/artists/debug-check', [ArtistBrowseController::class, 'debugCheck']);
    Route::get('/artists',      [ArtistBrowseController::class, 'index'])->name('artist.browse');
    Route::get('/artists/{id}', [ArtistBrowseController::class, 'show']) ->name('artist.browse.show');
    Route::get('/product/{id}', [ProductController::class, 'show'])      ->name('product.show');

    // --- BULK ORDER ROUTES (buyer) ---
    Route::get('/bulk-orders/create/{artwork}',    [BulkOrderController::class, 'create'])     ->name('bulk-orders.create');
    Route::post('/bulk-orders/store/{artwork}',    [BulkOrderController::class, 'store'])      ->name('bulk-orders.store');
    Route::get('/bulk-orders/{id}/pay',            [BulkOrderController::class, 'pay'])        ->name('bulk-orders.pay');
    Route::get('/bulk-orders/{id}/pay/success',    [BulkOrderController::class, 'paySuccess']) ->name('bulk-orders.pay.success');
    Route::get('/bulk-orders/{id}',                [BulkOrderController::class, 'show'])       ->name('bulk-orders.show');
    Route::get('/bulk-orders',                     [BulkOrderController::class, 'index'])      ->name('bulk-orders.index');

    // --- FAVORITE ROUTES ---
    Route::post('/artist/{user}/favorite', [FavoriteController::class, 'toggle'])->name('artist.favorite');
    Route::get('/my-favorites',            [FavoriteController::class, 'index']) ->name('favorites.index');

    // --- REPORT ROUTES ---
    Route::post('/artist/{id}/report', [ArtistController::class, 'report'])->name('artist.report');

    // --- CUSTOM ORDER ROUTES (buyer) ---
    Route::get('/custom-orders/create/{seller}',               [CustomOrderController::class, 'create'])       ->name('custom-orders.create');
    Route::post('/custom-orders/store/{seller}',               [CustomOrderController::class, 'store'])        ->name('custom-orders.store');
    Route::post('/custom-orders/{customOrder}/accept-counter', [CustomOrderController::class, 'acceptCounter'])->name('custom-orders.accept-counter');
    Route::post('/custom-orders/{customOrder}/refuse-counter', [CustomOrderController::class, 'refuseCounter'])->name('custom-orders.refuse-counter');
    Route::get('/custom-orders/{customOrder}/pay',             [CustomOrderController::class, 'pay'])          ->name('custom-orders.pay');
    Route::get('/custom-orders/{customOrder}/pay/success',     [CustomOrderController::class, 'paySuccess'])   ->name('custom-orders.pay.success');
    Route::get('/custom-orders/{customOrder}',                 [CustomOrderController::class, 'show'])         ->name('custom-orders.show');
    Route::get('/custom-orders',                               [CustomOrderController::class, 'index'])        ->name('custom-orders.index');

    // --- ADMIN ROUTES ---
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('/users',                [AdminController::class, 'users'])      ->name('admin.users');
        Route::post('/users/{user}/ban',    [AdminController::class, 'banUser'])    ->name('admin.users.ban');
        Route::post('/users/{user}/unban',  [AdminController::class, 'unbanUser'])  ->name('admin.users.unban');

        Route::get('/feedbacks',                  [AdminController::class, 'feedbacks'])       ->name('admin.feedbacks');
        Route::post('/feedbacks/{feedback}/read', [AdminController::class, 'markFeedbackRead'])->name('admin.feedbacks.read');
        Route::delete('/feedbacks/{feedback}',    [AdminController::class, 'deleteFeedback'])  ->name('admin.feedbacks.delete');

        Route::get('/reports',                  [AdminController::class, 'reports'])           ->name('admin.reports');
        Route::post('/reports/{report}/status', [AdminController::class, 'updateReportStatus'])->name('admin.reports.status');

        Route::get('/admins',               [AdminController::class, 'admins'])     ->name('admin.admins');
        Route::post('/admins/add',          [AdminController::class, 'addAdmin'])   ->name('admin.admins.add');
        Route::post('/admins/{user}/remove',[AdminController::class, 'removeAdmin'])->name('admin.admins.remove');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// --- STRIPE WEBHOOK (outside auth) ---
Route::post('/webhook/stripe', [ClassCheckoutController::class, 'webhook'])->name('stripe.webhook');