<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
Route::post('login-social', [App\Http\Controllers\Auth\SocialController::class, 'loginSocial']);
Route::post('get-infor-user', [App\Http\Controllers\Auth\SocialController::class, 'getInfoUser']);
Route::get('auth/{provider}', [App\Http\Controllers\Auth\SocialController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [App\Http\Controllers\Auth\SocialController::class, 'handleProviderCallback']);
Route::post('/send-otp', [App\Http\Controllers\Auth\SocialController::class, 'sendOtp']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::match(['get', 'post'], '/admin-login', [App\Http\Controllers\Admin\AdminController::class, 'login'])->name('admin-login');
Route::match(['get', 'post'], '/collaborator-login', [App\Http\Controllers\Admin\CollaboratorController::class, 'login'])->name('collaborator-login');

// Route::get('/admin-log',[App\Http\Controllers\Admin\AdminController::class,'log']);

// Route::resource('permission',App\Http\Controllers\Admin\PermissionController::class);
Route::get('show-permission', [App\Http\Controllers\Admin\PermissionController::class, 'showPermission']);
//GroupPermissionController
Route::resource('group-permission', App\Http\Controllers\Admin\GroupPermissionController::class);
// Route::resource('role',App\Http\Controllers\Admin\RoleController::class);
// Route::resource('brand',App\Http\Controllers\Admin\BrandController::class);
// Route::resource('category',App\Http\Controllers\Admin\CategoryController::class);

Route::get('/name-table-backup-database', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'showNameTable']);
Route::get('/backup-database/{table}', [App\Http\Controllers\Admin\DatabaseBackupController::class, 'backupDatabse']);

// Route::resource('coupon',App\Http\Controllers\Admin\CouponController::class);

// Route::get('get-detail-coupon/{code}',[App\Http\Controllers\Admin\CouponController::class,'getCouponDetail']);

// Route::resource('productStatus',App\Http\Controllers\Admin\ProductStatusController::class);
// Route::get('delete-all-product-status/{listId}',[App\Http\Controllers\Admin\ProductStatusController::class,'deleteAllProduct']);

// Route::resource('category',App\Http\Controllers\Admin\CategoryController::class);

// Route::get('delete-all-product-category/{listId}',[App\Http\Controllers\Admin\CategoryController::class,'deleteAllCategory']);
// Route::resource('brand',App\Http\Controllers\Admin\BrandController::class);
// Route::get('delete-all-product-brand/{listId}',[App\Http\Controllers\Admin\BrandController::class,'deleteAllBrand']);
// Route::resource('present',App\Http\Controllers\Admin\PresentController::class);
// Route::resource('order-status',App\Http\Controllers\Admin\OrderStatusController::class);
// Route::resource('shipping-method',App\Http\Controllers\Admin\ShippingMethodController::class);
// Route::resource('payment-method',App\Http\Controllers\Admin\PaymentMethodController::class);
// Route::resource('gift-promotion',App\Http\Controllers\Admin\GiftPromotionController::class);
// Route::resource('order',App\Http\Controllers\Admin\OrderController::class);
// Route::resource('cat-option',App\Http\Controllers\Admin\CatOptionController::class);
// Route::get('cat-option-child/{catId}',[App\Http\Controllers\Admin\CatOptionController::class,'catOpchild']);
// Route::resource('product-advertise',App\Http\Controllers\Admin\ProductAdvertiseController::class);
Route::resource('product', App\Http\Controllers\Admin\ProductController::class);
// Route::resource('menu',App\Http\Controllers\Admin\MenuController::class);

// Route::resource('product-flash-sale',App\Http\Controllers\Admin\ProductFlashSaleController::class);
// Route::post('delete-all-flash-sale',[App\Http\Controllers\Admin\ProductFlashSaleController::class,'deleteAll']);

// Route::resource('product-hot',App\Http\Controllers\Admin\ProductHotController::class);
// Route::post('delete-all-hot',[App\Http\Controllers\Admin\ProductHotController::class,'deleteAll']);
//CateParentPerController
// Route::resource('cate-parent-per',App\Http\Controllers\Admin\CateParentPerController::class);
// Route::get('select-cate-child-per/{parent}',[App\Http\Controllers\Admin\CateParentPerController::class,'showChildCategory']);
// Route::resource('cate-child-per',App\Http\Controllers\Admin\CateChildPerController::class);

//SupportGroupController
// Route::resource('support-group',App\Http\Controllers\Admin\SupportGroupController::class);
//SupportController
// Route::resource('support',App\Http\Controllers\Admin\SupportController::class);
Route::get('show-all-support', [App\Http\Controllers\Admin\SupportController::class, 'showAllSupport']);
//showAllSupport

// Route::resource('comment',App\Http\Controllers\Admin\CommentController::class);
//addCommentForUser
// Route::post('add-comment',[App\Http\Controllers\Admin\CommentController::class,'addCommentForUser']);
// Route::post('delete-all-comment',[App\Http\Controllers\Admin\CommentController::class,'deleteAll']);

//ConfigController
// Route::resource('config',App\Http\Controllers\Admin\ConfigController::class);
//DashboardController
// Route::resource('dashboard',App\Http\Controllers\Admin\DashboardController::class);
// Route::get('get-statistics',[App\Http\Controllers\Admin\DashboardController::class,'showStatisticsPage']);
Route::get('chart-statistics-page', [App\Http\Controllers\Admin\DashboardController::class, 'chartStatisticsPage']);

// Route::post('delete-all-promotion',[App\Http\Controllers\Admin\PromotionController::class,'deleteAllPromotion']);
// Route::resource('promotion',App\Http\Controllers\Admin\PromotionController::class);

// Route::resource('news-category',App\Http\Controllers\Admin\NewsCategoryController::class);
// Route::resource('news',App\Http\Controllers\Admin\NewsController::class);
// Route::post('delete-all-news',[App\Http\Controllers\Admin\NewsController::class,'deleteAllNews']);

//showAdvertise
Route::get('show-product-advertise', [App\Http\Controllers\Admin\ProductAdvertiseController::class, 'showAdvertise']);
Route::post('/verify-captcha', [App\Http\Controllers\Admin\OrderController::class, 'verify']);
Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {

    //Xóa ảnh chi tiết sản phẩm
    Route::delete('delete-detail-image', [App\Http\Controllers\Admin\ProductController::class, 'deleteAllDetailImage']);

    Route::resource('products', App\Http\Controllers\Admin\ProductsController::class);
    Route::resource('properties', App\Http\Controllers\Admin\PropertiesController::class);

    Route::get('show-product-new', [App\Http\Controllers\Admin\CategoryController::class, 'showCategory']);
    //HireCategoryController
    //HirePostController
    Route::resource('hire-category', App\Http\Controllers\Admin\HireCategoryController::class);
    Route::post('delete-all-hire-category', [App\Http\Controllers\Admin\HireCategoryController::class, 'deleteAll']);
    Route::resource('hire-post', App\Http\Controllers\Admin\HirePostController::class);
    Route::get('/downloadFile-candidate', [App\Http\Controllers\Admin\HirePostController::class, 'downloadFile']);
    Route::post('delete-all-hire-post', [App\Http\Controllers\Admin\HirePostController::class, 'deleteAll']);

    Route::get('show-candidates', [App\Http\Controllers\Admin\HirePostController::class, 'showCandidates']);
    Route::get('detail-candidates/{id}', [App\Http\Controllers\Admin\HirePostController::class, 'detailCandidates']);
    Route::post('/update-candidates/{id}', [App\Http\Controllers\Admin\HirePostController::class, 'updateCandidates']);
    Route::resource('setting-system', App\Http\Controllers\Admin\SettingController::class);

    Route::resource('menu', App\Http\Controllers\Admin\MenuController::class);

    //MailTemplateController
    Route::resource('mail-template', App\Http\Controllers\Admin\MailTemplateController::class);
    Route::post('delete-all-mail-template', [App\Http\Controllers\Admin\MailTemplateController::class, 'deleteAll']);
    // MailListController
    Route::resource('mail-list', App\Http\Controllers\Admin\MailListController::class);
    Route::post('delete-all-mail-list', [App\Http\Controllers\Admin\MailListController::class, 'deleteAll']);

    //IconController
    Route::resource('icon', App\Http\Controllers\Admin\IconController::class);
    Route::post('delete-all-icon', [App\Http\Controllers\Admin\IconController::class, 'deleteAll']);

    //SeoController
    Route::resource('seo', App\Http\Controllers\Admin\SeoController::class);

    // Route::resource('present',App\Http\Controllers\Admin\PresentController::class);
    // Route::resource('product',App\Http\Controllers\Admin\ProductController::class);
    //AboutController

    //ContactQouteController
    //ContactConfigController
    //ContactStaffController

    Route::resource('contact-qoute', App\Http\Controllers\Admin\ContactQouteController::class);
    Route::post('delete-all-contact-qoute', [App\Http\Controllers\Admin\ContactQouteController::class, 'deleteAll']);

    Route::resource('contact-config', App\Http\Controllers\Admin\ContactConfigController::class);
    Route::post('delete-all-contact-config', [App\Http\Controllers\Admin\ContactConfigController::class, 'deleteAll']);

    Route::resource('contact-staff', App\Http\Controllers\Admin\ContactStaffController::class);
    Route::post('delete-all-contact-staff', [App\Http\Controllers\Admin\ContactStaffController::class, 'deleteAll']);

    Route::resource('contact', App\Http\Controllers\Admin\ContactController::class);
    Route::post('delete-all-contact', [App\Http\Controllers\Admin\ContactController::class, 'deleteAll']);

    Route::get('get-detail-coupon/{code}', [App\Http\Controllers\Admin\CouponController::class, 'getCouponDetail']);

    Route::resource('about', App\Http\Controllers\Admin\AboutController::class);
    Route::post('delete-all-about', [App\Http\Controllers\Admin\AboutController::class, 'deleteAll']);

    Route::resource('guide', App\Http\Controllers\Admin\GuideController::class);
    Route::post('delete-all-guide', [App\Http\Controllers\Admin\GuideController::class, 'deleteAll']);

    Route::resource('service', App\Http\Controllers\Admin\ServiceController::class);
    Route::post('delete-all-service', [App\Http\Controllers\Admin\ServiceController::class, 'deleteAll']);

    Route::resource('faqs-category', App\Http\Controllers\Admin\FaqsCategoryController::class);
    Route::post('delete-all-faqs-category', [App\Http\Controllers\Admin\FaqsCategoryController::class, 'deleteAll']);

    Route::resource('faqs', App\Http\Controllers\Admin\FaqsController::class);
    Route::post('delete-all-faqs', [App\Http\Controllers\Admin\FaqsController::class, 'deleteAll']);

    //FaqsController
    //FaqsCategoryController
    //CatOptionController
    // Route::resource('cat-option',App\Http\Controllers\Admin\CatOptionController::class);
    // Route::resource('product-advertise',App\Http\Controllers\Admin\ProductAdvertiseController::class);
    // Route::resource('category',App\Http\Controllers\Admin\CategoryController::class);
    // Route::resource('brand',App\Http\Controllers\Admin\BrandController::class);

    //product
    Route::resource('product', App\Http\Controllers\Admin\ProductController::class);
    Route::post('delete-all-product', [App\Http\Controllers\Admin\ProductController::class, 'deleteAll']);

    //category
    Route::resource('category', App\Http\Controllers\Admin\CategoryController::class);
    Route::get('delete-all-product-category/{listId}', [App\Http\Controllers\Admin\CategoryController::class, 'deleteAllCategory']);

    //brand
    Route::resource('brand', App\Http\Controllers\Admin\BrandController::class);
    Route::post('delete-all-product-brand', [App\Http\Controllers\Admin\BrandController::class, 'deleteAllBrand']);
    //config
    Route::resource('config', App\Http\Controllers\Admin\ConfigController::class);

    //properties
    Route::resource('cat-option', App\Http\Controllers\Admin\CatOptionController::class);
    Route::get('cat-option-child/{catId}', [App\Http\Controllers\Admin\CatOptionController::class, 'catOpchild']);

    //productStatus
    Route::resource('productStatus', App\Http\Controllers\Admin\ProductStatusController::class);
    Route::post('delete-all-product-status', [App\Http\Controllers\Admin\ProductStatusController::class, 'deleteAllProduct']);

    //ROLE
    Route::resource('role', App\Http\Controllers\Admin\RoleController::class);
    Route::post('delete-all-role', [App\Http\Controllers\Admin\RoleController::class, 'deleteAll']);

    //admin log

    Route::get('/select-name-admin', [App\Http\Controllers\Admin\AdminController::class, 'showSelectAdmin']);
    Route::get('/admin-log', [App\Http\Controllers\Admin\AdminController::class, 'log']);

    Route::post('/delete-all-admin', [App\Http\Controllers\Admin\AdminController::class, 'deleteAll']);

    //permission

    Route::resource('cate-parent-per', App\Http\Controllers\Admin\CateParentPerController::class);
    Route::resource('permission', App\Http\Controllers\Admin\PermissionController::class);
    Route::get('select-cate-child-per/{parent}', [App\Http\Controllers\Admin\CateParentPerController::class, 'showChildCategory']);

    //product flash sale
    Route::resource('product-flash-sale', App\Http\Controllers\Admin\ProductFlashSaleController::class);
    Route::post('delete-all-flash-sale', [App\Http\Controllers\Admin\ProductFlashSaleController::class, 'deleteAll']);

    //product hot
    Route::resource('product-hot', App\Http\Controllers\Admin\ProductHotController::class);
    Route::post('delete-all-hot', [App\Http\Controllers\Admin\ProductHotController::class, 'deleteAll']);

    //product-advertise
    Route::resource('product-advertise', App\Http\Controllers\Admin\ProductAdvertiseController::class);
    Route::post('delete-all-product-advertise', [App\Http\Controllers\Admin\ProductAdvertiseController::class, 'deleteAll']);

    //ProductAdvertiseSpecialController
    Route::resource('product-advertise-special', App\Http\Controllers\Admin\ProductAdvertiseSpecialController::class);

    Route::post('delete-all-product-advertise-special', [App\Http\Controllers\Admin\ProductAdvertiseSpecialController::class, 'deleteAll']);

    //coupon
    Route::resource('coupon', App\Http\Controllers\Admin\CouponController::class);

    //order
    Route::resource('order-status', App\Http\Controllers\Admin\OrderStatusController::class);
    Route::post('delete-all-order-status', [App\Http\Controllers\Admin\OrderStatusController::class, 'deleteAll']);

    Route::resource('shipping-method', App\Http\Controllers\Admin\ShippingMethodController::class);
    Route::post('delete-all-shipping-method', [App\Http\Controllers\Admin\ShippingMethodController::class, 'deleteAll']);

    Route::resource('payment-method', App\Http\Controllers\Admin\PaymentMethodController::class);
    Route::post('delete-all-payment-method', [App\Http\Controllers\Admin\PaymentMethodController::class, 'deleteAll']);
    Route::resource('order', App\Http\Controllers\Admin\OrderController::class);

    //present
    Route::resource('present', App\Http\Controllers\Admin\PresentController::class);
    Route::post('delete-all-present', [App\Http\Controllers\Admin\PresentController::class, 'deleteAll']);

    //dashboard
    Route::get('no-approved-statistics', [App\Http\Controllers\Admin\DashboardController::class, 'noApprovedStatistics']);

    Route::resource('dashboard', App\Http\Controllers\Admin\DashboardController::class);
    Route::get('get-statistics', [App\Http\Controllers\Admin\DashboardController::class, 'showStatisticsPage']);

    //gift-promotion
    Route::resource('gift-promotion', App\Http\Controllers\Admin\GiftPromotionController::class);
    Route::post('delete-all-gift-promotion', [App\Http\Controllers\Admin\GiftPromotionController::class, 'deleteAll']);

    //promotion
    Route::post('delete-all-promotion', [App\Http\Controllers\Admin\PromotionController::class, 'deleteAllPromotion']);
    Route::resource('promotion', App\Http\Controllers\Admin\PromotionController::class);

    //support-group
    Route::resource('support-group', App\Http\Controllers\Admin\SupportGroupController::class);
    Route::post('delete-all-support-group', [App\Http\Controllers\Admin\SupportGroupController::class, 'deleteAll']);
    //support
    Route::resource('support', App\Http\Controllers\Admin\SupportController::class);
    Route::post('delete-all-support', [App\Http\Controllers\Admin\SupportController::class, 'deleteAll']);
    // Route::get('show-all-support',[App\Http\Controllers\Admin\SupportController::class,'showAllSupport']);

    //comment
    Route::resource('comment', App\Http\Controllers\Admin\CommentController::class);
    Route::post('delete-all-comment', [App\Http\Controllers\Admin\CommentController::class, 'deleteAll']);

    //news
    Route::resource('news-category', App\Http\Controllers\Admin\NewsCategoryController::class);
    Route::post('delete-all-news-category', [App\Http\Controllers\Admin\NewsCategoryController::class, 'deleteAll']);
    Route::resource('news', App\Http\Controllers\Admin\NewsController::class);
    Route::post('delete-all-news', [App\Http\Controllers\Admin\NewsController::class, 'deleteAllNews']);

    // Route::resource('shipping-method',App\Http\Controllers\Admin\ShippingMethodController::class);
    // Route::resource('payment-method',App\Http\Controllers\Admin\PaymentMethodController::class);

    // Route::resource('coupon',App\Http\Controllers\Admin\CouponController::class);

    // Route::resource('news',App\Http\Controllers\Admin\NewsController::class);

    // Route::resource('news-category',App\Http\Controllers\Admin\NewsCategoryController::class);

    //member
    Route::resource('member', App\Http\Controllers\Admin\MemberController::class);

    // Route::resource('order',App\Http\Controllers\Admin\OrderController::class);

    Route::resource('advertise', App\Http\Controllers\Admin\AdvertiseController::class);
    Route::post('delete-all-advertise', [App\Http\Controllers\Admin\AdvertiseController::class, 'deleteAll']);
    Route::resource('ad-pos', App\Http\Controllers\Admin\AdposController::class);
    Route::post('delete-all-ad-pos', [App\Http\Controllers\Admin\AdposController::class, 'deleteAll']);

    // Route::resource('brand',App\Http\Controllers\Admin\BrandController::class);

    Route::resource('department', App\Http\Controllers\Admin\DepartmentController::class);
    Route::resource('information', App\Http\Controllers\Admin\AdminController::class);
    Route::get('/admin-information', [App\Http\Controllers\Admin\AdminController::class, 'information']);
    Route::post('/admin-logout', [App\Http\Controllers\Admin\AdminController::class, 'logout']);
});

Route::post('member/register', [App\Http\Controllers\Member\MemberController::class, 'register']);
Route::match(['get', 'post'], 'member/login', [App\Http\Controllers\Member\MemberController::class, 'login']);

Route::get('test-db', [App\Http\Controllers\Admin\SeoController::class, 'testdb']);

Route::group(['prefix' => 'member'], function () {
    //Hiển thị bộ lọc sản phẩm
    Route::get('/category-option-filter', [App\Http\Controllers\Member\FilterCategoryController::class, 'index_filter']);

    //Lấy ra sản phẩm bạn có thể quan tâm
    Route::get('/get-product-related', [App\Http\Controllers\Member\ProductController::class, 'getProductRelated']);

    Route::get('/get-product-technology/{productId}', [App\Http\Controllers\Member\ProductController::class, 'getProductTechnology']);
    Route::get('/get-product-description/{productId}', [App\Http\Controllers\Member\ProductController::class, 'getDescription']);

    Route::post('delete-group-product-cart/{id}', [App\Http\Controllers\Member\CartController::class, 'deleteGroupProduct']);

    Route::post('get-product-combo', [App\Http\Controllers\Member\ProductController::class, 'getComboProduct']);
    Route::post('add-group-product-cart/{id}', [App\Http\Controllers\Member\CartController::class, 'addGroupProductCart']);
    Route::post('repurchase/{id}', [App\Http\Controllers\Member\CartController::class, 'repurchase']);

    //candidateInfo
    //deleteAll
    Route::post('/create-candidate', [App\Http\Controllers\Admin\HirePostController::class, 'createCandidates']);
    Route::get('/candidate-information', [App\Http\Controllers\Admin\HirePostController::class, 'candidateInfo']);
    Route::get('/hire-post/{slug}', [App\Http\Controllers\Admin\HirePostController::class, 'detail']);
    Route::get('/get-meta-hire-post/{slug}', [App\Http\Controllers\Admin\HirePostController::class, 'getMetaHirePost']);
    //Route::get('/',[App\Http\Controllers\Admin\HirePostController::class,'showHirePost']);
    Route::get('/show-hire-related', [App\Http\Controllers\Admin\HirePostController::class, 'showHireRelated']);
    Route::get('/show-hire-post', [App\Http\Controllers\Admin\HirePostController::class, 'showHirePost']);

    Route::get('/show-hire-category', [App\Http\Controllers\Admin\HireCategoryController::class, 'showHireCategory']);

    Route::get('show-product-advertise-special', [App\Http\Controllers\Admin\ProductAdvertiseSpecialController::class, 'showAllProductAdvertiseSpecial']);
    //ConfigController
    Route::get('show-config', [App\Http\Controllers\Member\ConfigController::class, 'showConfig']);

    Route::post('add-contact-qoute', [App\Http\Controllers\Admin\ContactQouteController::class, 'addContactQoute']);

    //SettingController
    Route::get('show-all-staff', [App\Http\Controllers\Admin\ContactStaffController::class, 'showAllContactStaff']);
    Route::post('add-contact', [App\Http\Controllers\Admin\ContactController::class, 'addContactShow']);

    Route::get('test-mail', [App\Http\Controllers\Admin\SeoController::class, 'testMail']);

    Route::post('post-mail', [App\Http\Controllers\Admin\MailListController::class, 'addGmail']);

    Route::get('show-icon', [App\Http\Controllers\Admin\IconController::class, 'showIcon']);
    Route::get('show-contact-config', [App\Http\Controllers\Admin\ContactConfigController::class, 'showContactConfig']);

    Route::get('show-guide', [App\Http\Controllers\Admin\GuideController::class, 'showAllGuide']);
    Route::get('detail-guide/{slug}', [App\Http\Controllers\Admin\GuideController::class, 'showDetailGuide']);

    Route::get('show-detail-faqs/{url}', [App\Http\Controllers\Admin\FaqsController::class, 'showDetailFaqs']);
    Route::post('add-faqs', [App\Http\Controllers\Admin\FaqsController::class, 'addFaqs']);

    Route::get('show-all-faqsCate', [App\Http\Controllers\Admin\FaqsCategoryController::class, 'showAllFaqsCate']);
    Route::get('show-all-service', [App\Http\Controllers\Admin\ServiceController::class, 'showServiceUser']);
    // /showAbout

    Route::get('detail-service/{slug}', [App\Http\Controllers\Admin\ServiceController::class, 'showDetailService']);
    Route::get('detail-about/{slug}', [App\Http\Controllers\Admin\AboutController::class, 'showDetailAbout']);
    Route::get('show-all-about', [App\Http\Controllers\Admin\AboutController::class, 'showAbout']);

    Route::post('add-comment', [App\Http\Controllers\Admin\CommentController::class, 'addCommentForUser']);
    Route::any('/forget-password', [App\Http\Controllers\Member\MemberController::class, 'forgetPassword']);
    Route::any('/forget-password-change', [App\Http\Controllers\Member\MemberController::class, 'forgetPasswordChange']);

    Route::post('/change-password/{memberId}', [App\Http\Controllers\Member\MemberController::class, 'changePassword']);

    Route::get('/category-menu', [App\Http\Controllers\Member\CategoryController::class, 'menu']);

    Route::get('products/export/technology', [App\Http\Controllers\Admin\ImportExportController::class, 'exportTechnologyExcel']);
    Route::get('products-export-properties', [App\Http\Controllers\Admin\ImportExportController::class, 'exportAllProductProperties']);

    Route::get('export-statistics-excel', [App\Http\Controllers\Admin\ImportExportController::class, 'exportStatisticsPagesExcel']);

    Route::get('export-order-excel', [App\Http\Controllers\Admin\ImportExportController::class, 'exportOrder']);

    //Take 5 news
    Route::get('take-5-news', [App\Http\Controllers\Member\NewsController::class, 'take5news']);

    Route::get('news-by-views', [App\Http\Controllers\Member\NewsController::class, 'showNewsbyViews']);
    Route::get('news/{slug}', [App\Http\Controllers\Member\NewsController::class, 'index']);
    //CategoryNewProdut
    Route::get('new-relate-product', [App\Http\Controllers\Member\NewsController::class, 'CategoryNewProdut']);
    Route::get('news-search', [App\Http\Controllers\Member\NewsController::class, 'search']);
    Route::get('news-category', [App\Http\Controllers\Member\NewsCategoryController::class, 'index']);
    Route::get('meta-news-category/{slug}', [App\Http\Controllers\Member\NewsCategoryController::class, 'getMetaCategoryNews']);
    Route::get('news-detail/{urlCat}/{slug}', [App\Http\Controllers\Member\NewsController::class, 'detail']);

    Route::get('meta-news-detail/{urlCat}/{slug}', [App\Http\Controllers\Member\NewsController::class, 'getMetaDetail']);
    //relatedNew
    Route::get('related-new/{urlCat}/{slug}', [App\Http\Controllers\Member\NewsController::class, 'relatedNew']);

    Route::get('/export-product-sap', [App\Http\Controllers\Member\ProductController::class, 'exportProductSAP']);
    Route::get('/category-option', [App\Http\Controllers\Member\FilterCategoryController::class, 'index']);

    Route::get('/filter-category', [App\Http\Controllers\Member\FilterCategoryController::class, 'filter']);
    Route::get('/get-name-category', [App\Http\Controllers\Member\FilterCategoryController::class, 'getNameCategory']);

    Route::get('show-advertise/{pos?}', [App\Http\Controllers\Admin\AdvertiseController::class, 'showAdvertise']);

    //Route::get('/filter-category',[App\Http\Controllers\Member\FilterCategoryController::class,'filter']);

    Route::get('build-pc', [App\Http\Controllers\Member\BuildPCController::class, 'index'])->name('build-pc');
    Route::get('filter-build-pc', [App\Http\Controllers\Member\BuildPCController::class, 'filterBuildPc']);
    Route::get('export-excel-pc', [App\Http\Controllers\Member\BuildPCController::class, 'exportExcelPC']);

    Route::resource('list-cart', App\Http\Controllers\Member\CartController::class);
    Route::get('show-cart', [App\Http\Controllers\Member\CartController::class, 'showCart']);
    Route::post('add-update-cart/{id}', [App\Http\Controllers\Member\CartController::class, 'addOrUpdateCart']);
    Route::post('update-cart/{Cartid}', [App\Http\Controllers\Member\CartController::class, 'updateCart']);
    Route::post('delete-cart', [App\Http\Controllers\Member\CartController::class, 'deleteCart']);
    Route::post('add-array-cart/{id}', [App\Http\Controllers\Member\CartController::class, 'addArrayCart']);
    //addArrayCart
    //inforOrder
    Route::get('show-order-status', [App\Http\Controllers\Member\CheckoutController::class, 'showOrderStatus']);
    Route::get('show-shipping-method', [App\Http\Controllers\Member\CheckoutController::class, 'showShippingMethod']);
    Route::get('show-payment-method', [App\Http\Controllers\Member\CheckoutController::class, 'showPaymentMethod']);
    Route::post('checkout', [App\Http\Controllers\Member\CheckoutController::class, 'checkout']);
    //Sửa lại checkout
    Route::get('checkout-new', [App\Http\Controllers\Member\CheckoutController::class, 'checkoutNew']);

    Route::get('infor-order/{orderId}/{userId?}', [App\Http\Controllers\Member\CheckoutController::class, 'inforOrder']);
    Route::post('payment', [App\Http\Controllers\Member\CheckoutController::class, 'updateOrder']);

    Route::resource('test', App\Http\Controllers\Member\MemberController::class);
    Route::get('/information/{id}', [App\Http\Controllers\Member\MemberController::class, 'information']);
    Route::post('/upload-information-member/{id}', [App\Http\Controllers\Member\MemberController::class, 'updateInfoMember']);
    Route::post('/upload-address-member/{id}', [App\Http\Controllers\Member\MemberController::class, 'updateAddressMember']);
    Route::get('/show-address-member/{id}/{status}', [App\Http\Controllers\Member\MemberController::class, 'showAddressMember']);
    Route::get('/delete-address-member/{id}', [App\Http\Controllers\Member\MemberController::class, 'deleteAddressMember']);
    //updateInfoMember
    Route::get('/statistics-category', [App\Http\Controllers\Member\CategoryController::class, 'statisticsCategory']);
    Route::get('/category', [App\Http\Controllers\Member\CategoryController::class, 'index']);
    Route::get('/show-category', [App\Http\Controllers\Member\CategoryController::class, 'showCategory']);
    Route::get('/show-category-header', [App\Http\Controllers\Member\CategoryController::class, 'showCategoryHeader']);
    Route::get('/category-parent', [App\Http\Controllers\Member\CategoryController::class, 'categoryParentName']);

    Route::get('/brand-list/{idCategory}', [App\Http\Controllers\Member\BrandController::class, 'listBrand']);
    Route::get('/compare-product-search', [App\Http\Controllers\Member\BrandController::class, 'searchCategoryProduct']);
    Route::get('compare-products', [App\Http\Controllers\Member\BrandController::class, 'compareProducts']);

    Route::get('/check-gift-promotion/{id}', [App\Http\Controllers\Member\ProductController::class, 'checkGiftPromotion']);
    Route::get('/check-coupon/{id}', [App\Http\Controllers\Member\ProductController::class, 'checkCoupon']);
    Route::get('/check-present/{id}', [App\Http\Controllers\Member\ProductController::class, 'checkPresent']);
    Route::get('/product-detail/{slug}', [App\Http\Controllers\Member\ProductController::class, 'detail']);
    //Route API này có thể lỗi
    Route::get('/product-detail-new/{slug}', [App\Http\Controllers\Member\ProductController::class, 'detail-new']);
    Route::get('/get-name-product', [App\Http\Controllers\Member\ProductController::class, 'getProductName']);
    Route::get('/group-product/{slug}', [App\Http\Controllers\Member\ProductController::class, 'groupProduct']);
    Route::get('/relate-product', [App\Http\Controllers\Member\ProductController::class, 'relatedProduct']);
    Route::get('/relate-product-technology', [App\Http\Controllers\Member\ProductController::class, 'relatedProductTechnology']);
    Route::get('/top-sale-product', [App\Http\Controllers\Member\ProductController::class, 'topSaleProduct']);

    Route::get('show-all-flash-sale', [App\Http\Controllers\Member\ProductController::class, 'showAllProductFlashSale']);
    Route::get('show-flash-sale-for-category', [App\Http\Controllers\Member\ProductController::class, 'showProductFlashSaleForCategory']);
    //showProductHotForCategory
    Route::get('show-hot-for-category', [App\Http\Controllers\Member\ProductController::class, 'showProductHotForCategory']);

    Route::get('/search-product', [App\Http\Controllers\Member\ProductController::class, 'searchProduct']);
    Route::get('/product-hot', [App\Http\Controllers\Member\ProductController::class, 'productHot']);
    Route::get('/recommend-product', [App\Http\Controllers\Member\ProductController::class, 'recommendProduct']);
    Route::get('/check-product-piture', [App\Http\Controllers\Member\ProductController::class, 'checkProductHaveImage']);

    Route::get('promotion', [App\Http\Controllers\Member\PromotionController::class, 'index']);
    Route::get('promotion-show', [App\Http\Controllers\Member\PromotionController::class, 'show']);
    Route::get('/promotion/{slug}', [App\Http\Controllers\Member\PromotionController::class, 'detail'])->name('promotion-detail');

    Route::get('promotion-list', [App\Http\Controllers\Member\PromotionController::class, 'showPromotion']);

    Route::get('coupon', [App\Http\Controllers\Member\CouponController::class, 'index']);

    Route::get('show-order', [App\Http\Controllers\Member\OrderController::class, 'showOrder']);
    Route::get('detail-order/{orderId}/{userId}', [App\Http\Controllers\Member\OrderController::class, 'detailOrder']);

});
Route::resource('collaborators', App\Http\Controllers\Admin\CollaboratorController::class);
Route::get('/collaborator-information', [App\Http\Controllers\Admin\AdminController::class, 'information']);
// Route::get('/select-name-admin',[App\Http\Controllers\Admin\AdminController::class,'showSelectAdmin']);
