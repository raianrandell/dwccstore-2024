<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountingController;

// Public Routes
Route::GET('/', function () {
    return view('welcome');
});

// SuperAdmin Routes
Route::GET('/superadminlogin', [SuperAdminController::class, 'superadminlogin'])->name('superadminlogin');
Route::GET('/superadminregistration', [SuperAdminController::class, 'superadminregistration']);
Route::POST('/register_superadmin', [SuperAdminController::class, 'register_superadmin'])->name('register_superadmin');
Route::POST('/superadminlogin', [SuperAdminController::class, 'login_superadmin'])->name('login_superadmin');
Route::GET('/superadmin_dashboard', [SuperAdminController::class, 'superadmin_dashboard'])->name('superadmin.dashboard');
Route::POST('/superadminlogout', [SuperAdminController::class, 'logout'])->name('logout');
Route::GET('/user_management', [SuperAdminController::class, 'user_management'])->name('superadmin.usermanagement');
Route::POST('/superadmin/add_user', [SuperAdminController::class, 'add_user'])->name('superadmin.add_user');
Route::PUT('/superadmin/user/{id}/edit', [SuperAdminController::class, 'edit_user'])->name('superadmin.edit_user');
Route::DELETE('/superadmin/user/{id}/delete', [SuperAdminController::class, 'delete_user'])->name('superadmin.delete_user');

// Grouping all authenticated routes
Route::middleware(['auth'])->group(function () {

    // Inventory Routes
    Route::GET('/inventory_dashboard', [InventoryController::class, 'inventoryDashboard'])->name('inventory.dashboard');
    Route::GET('/inventory_stockmanagement', [InventoryController::class, 'stockManagement'])->name('inventory.stockmanagement');
    Route::GET('/inventory_sectionmanagement', [InventoryController::class, 'sectionManagement'])->name('inventory.sectionmanagement');
    Route::POST('/inventory_sectionmanagement', [InventoryController::class, 'addSections'])->name('inventory.addsection');
    Route::GET('/inventory_categorymanagement', [InventoryController::class, 'categoryManagement'])->name('inventory.categorymanagement');
    Route::POST('/inventory_categorymanagement/add', [InventoryController::class, 'addCategory'])->name('inventory.addcategory');
    Route::POST('/inventory_categorymanagement', [InventoryController::class, 'itemsStore'])->name('inventory.itemstore');
    Route::GET('/inventory_stockmanagement/additem', [InventoryController::class, 'addItem'])->name('inventory.additem'); 
    Route::get('inventory/edititem/{id}', [InventoryController::class, 'editItem'])->name('inventory.edititem');
    Route::PUT('inventory/updateitem/{id}', [InventoryController::class, 'updateItem'])->name('inventory.itemupdate');
    Route::post('/inventory/update-stock/{id}', [InventoryController::class, 'updateStock'])->name('inventory.updateStock');
    Route::get('/inventory/items', [InventoryController::class, 'getItems'])->name('inventory.items');
    Route::GET('/inventory_damagetransaction', [InventoryController::class, 'damageTransaction'])->name('inventory.damagetransaction');
    Route::POST('/damage-transaction', [InventoryController::class, 'storeDamageTransaction'])->name('inventory.storeDamageTransaction');
    Route::get('/inventory/category-items/{categoryId}', [InventoryController::class, 'getCategoryItemsAjax'])->name('inventory.getCategoryItemsAjax');
    Route::get('/inventory/total-item-report', [InventoryController::class, 'totalItemReport'])->name('inventory.total_item_report');
    Route::get('/inventory/total-item-report/pdf', [InventoryController::class, 'exportTotalItemReportPdf'])->name('inventory.total_item_report.pdf');
    Route::get('/inventory/total-item-report/export/{type}', [InventoryController::class, 'export'])->name('inventory.total_item_report_export');
    Route::get('/inventory/total-item-report/view/{id}', [InventoryController::class, 'view'])->name('inventory.total_item_report_view');
    Route::get('/inventory/damage-item-report/view/{id}', [InventoryController::class, 'view'])->name('inventory.damage_item_report_view');
    Route::GET('/inventory/damage-item-report', [InventoryController::class, 'damageItemReport'])->name('inventory.damage_item_report');
    Route::get('/inventory/damage-item-report/pdf', [InventoryController::class, 'exportDamageItemReportPdf'])->name('inventory.damage_item_report.pdf');
    Route::get('/inventory/damage-item-report/excel', [InventoryController::class, 'exportDamageItemReportExcel'])->name('inventory.damage_item_report.excel');
    Route::GET('/inventory/total-item-report/excel', [InventoryController::class, 'exportTotalItemReport'])->name('inventory.total_item_report.excel');
    
    //expired item report route
    Route::GET('/inventory/expired-item-report', [InventoryController::class, 'expiredItemReport'])->name('inventory.expired_item_report');
    Route::get('/inventory/expired-item-report/pdf', [InventoryController::class, 'exportExpiredItemReportPdf'])->name('inventory.expired_item_report.pdf');
    Route::get('/inventory/expired-item-report/excel', [InventoryController::class, 'exportExpiredItemReportExcel'])->name('inventory.expired_item_report.excel');

    Route::post('/inventory/modify-expiration-date', [InventoryController::class, 'modifyExpirationDate'])->name('inventory.modifyExpirationDate');

    Route::GET('/user/profile', [InventoryController::class, 'profile'])->name('inventory.userprofile');
    Route::post('/user/profile/update-password', [InventoryController::class, 'updatePassword'])->name('user.updatePassword');
    Route::GET('/inventory_damageitems', [InventoryController::class, 'damageItems'])->name('inventory.damageitems');
    Route::POST('/inventorylogout', [InventoryController::class, 'inventorylogout'])->name('inventory.logout');
    Route::GET('/sections', [InventoryController::class, 'sectionManagement'])->name('sectionmanagement');
    Route::POST('/sections/add', [InventoryController::class, 'addSections'])->name('addsection');
    Route::get('/inventory/sections/{id}/categories', [InventoryController::class, 'getSectionCategories'])->name('inventory.sections.categories');
    Route::get('/inventory/category/{id}/items', [InventoryController::class, 'getCategoryItems'])->name('inventory.getCategoryItems');
    Route::GET('/inventory/low-stock-item-report', [InventoryController::class, 'lowStockItemReport'])->name('inventory.low_stock_item_report');
    Route::get('/inventory/low-stock-item-report/pdf', [InventoryController::class, 'exportLowStockItemReportPdf'])->name('inventory.low_stock_item_report.pdf');
    Route::get('/inventory/low-stock-item-report/excel', [InventoryController::class, 'exportLowStockItemReportExcel'])->name('inventory.low_stock_item_report.excel');
    Route::get('/inventory/get-stock-logs/{item}', [InventoryController::class, 'getStockLogs']);
    //Transfer Logs
    Route::post('/inventory/transfer-item/{id}', [InventoryController::class, 'transferItem'])->name('inventory.transferItem');
    Route::get('/inventory/get-higher-priced-items/{itemId}', [InventoryController::class, 'getSimilarItems']);

    Route::GET('/inventory_togaRenting', [InventoryController::class, 'togaRenting'])->name('inventory.toga_renting');
    Route::post('/inventory/add-item-for-rent', [InventoryController::class, 'storeRentItem'])->name('inventory.add_item_for_rent');
    Route::post('/items/{id}/add-stock', [InventoryController::class, 'addStock'])->name('items.addStock');
    Route::get('/items/{id}', [InventoryController::class, 'getItem']);
    Route::GET('inventory/services', [InventoryController::class, 'Services'])->name('inventory.services');
    Route::post('/inventory/services', [InventoryController::class, 'storeService'])->name('inventory.store_service');  // Route to update an existing service
    Route::put('/services/{id}', [InventoryController::class, 'updateService'])->name('inventory.update_service');

    // Route to delete a service
    Route::delete('/services/{id}', [InventoryController::class, 'deleteService'])->name('inventory.delete_service');

    // Cashier Routes
    Route::GET('/cashierdashboard', [CashierController::class, 'cashierDashboard'])->name('cashier.cashier_dashboard');
    Route::POST('/cashierlogout', [CashierController::class, 'cashierlogout'])->name('cashier.logout');
    Route::GET('/cashier_sales', [CashierController::class, 'fetchItem'])->name('cashier.sales');
    Route::GET('/cashier_saleshistory', [CashierController::class, 'salesHistory'])->name('cashier.sales_history');
    Route::POST('/transactions/save', [CashierController::class, 'saveTransaction'])->name('cashier.save_transaction');
    Route::POST('/cashier/save_items_session', [CashierController::class, 'saveItemsSession'])->name('cashier.save_items_session');
    Route::GET('/cashier/fetch-item-by-barcode', [CashierController::class, 'fetchItemByBarcode'])->name('cashier.fetch_item_by_barcode');
    
    Route::GET('/cashier_voidrecords', [CashierController::class, 'voidRecords'])->name('cashier.void_records');
    Route::post('/cashier/save_void_records', [CashierController::class, 'saveVoidRecords'])->name('cashier.save_void_records');

    Route::POST('/cashier/update-stock', [CashierController::class, 'updateStock'])->name('cashier.update_stock');
    Route::GET('/cashier_credit', [CashierController::class, 'credit'])->name('cashier.credit');
    Route::GET('/cashier_fines', [CashierController::class, 'fines'])->name('cashier.fines');
    Route::GET('/cashier_fineshistory', [CashierController::class, 'finesHistory'])->name('cashier.fines_history');
    Route::GET('/cashier_returns', [CashierController::class, 'returns'])->name('cashier.returns');

    Route::GET('/cashier_salesreport', [CashierController::class, 'sales_report'])->name('cashier.sales_report');
    Route::get('/inventory/sales-report/pdf', [CashierController::class, 'exportSalesReportPdf'])->name('cashier.sales_report.pdf');
    Route::get('/inventory/sales-report/excel', [CashierController::class, 'exportSalesReportExcel'])->name('cashier.sales_report.excel');

    Route::GET('/cashier_voidreport', [CashierController::class, 'void_report'])->name('cashier.void_report');
    Route::get('/inventory/void-item-report/pdf', [CashierController::class, 'exportVoidItemReportPdf'])->name('cashier.void_item_report.pdf');
    Route::get('/inventory/void-item-report/excel', [CashierController::class, 'exportVoidItemReportExcel'])->name('cashier.void_item_report.excel');
    Route::GET('/cashier_userprofile', [CashierController::class, 'userProfile'])->name('cashier.userprofile'); 
    Route::GET('/cashier/profile/edit', [CashierController::class, 'editProfile'])->name('cashier.editProfile');
    Route::PUT('/cashier/profile/edit', [CashierController::class, 'updateProfile'])->name('cashier.updateProfile');
    Route::get('/cashier/getTransactionItems', [CashierController::class, 'getTransactionItems'])->name('cashier.getTransactionItems');

    Route::post('/process-fine-payment', [CashierController::class, 'processFinePayment'])->name('processFinePayment');
    Route::post('/return/fetch-items', [CashierController::class, 'fetchTransactionItems'])->name('return.fetch_items');
    Route::post('/return/process', [CashierController::class, 'processReturn'])->name('return.processReturn');
    Route::post('/cashier/save_credit_transaction', [CashierController::class, 'saveCreditTransaction'])->name('cashier.save_credit_transaction');  
    Route::GET('/cashier_returnreport', [CashierController::class, 'returnReport'])->name('cashier.return_item_report');
    Route::get('/cashier/returned-item-report/pdf', [CashierController::class, 'exportReturnedItemReportPdf'])->name('cashier.returned_item_report_pdf');
    Route::get('/cashier/returned-item-report/excel', [CashierController::class, 'exportReturnedItemReportExcel'])->name('cashier.returned_item_report_excel');
    Route::GET('/cashier_storesalesreport', [CashierController::class, 'store_sales_report'])->name('cashier.store_sales_report');
    Route::get('/services', [CashierController::class, 'services'])->name('cashier.services');
    Route::post('/save_services', [CashierController::class, 'saveServices'])->name('cashier.save_services');
    // Services History Routes
    Route::get('/services-history', [CashierController::class, 'servicesHistory'])->name('cashier.services_history');
    Route::get('/services-history/getTransactionItems', [CashierController::class, 'getServiceTransactionItems'])->name('cashier.getServiceTransactionItems');
    
    // Admin Routes
    Route::GET('/admindashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');

    Route::GET('/admin_reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::GET('/admin_toga&fines', [AdminController::class, 'togaFines'])->name('admin.toga_fines');
    Route::GET('/admin_userprofile', [AdminController::class, 'userProfile'])->name('admin.userprofile');
    Route::POST('/adminlogout', [AdminController::class, 'adminlogout'])->name('admin.logout');

    Route::post('/admin/add-borrower', [AdminController::class, 'addBorrower'])->name('admin.addBorrower');
    Route::post('/admin/returnBorrower/{id}', [AdminController::class, 'returnBorrower'])->name('admin.returnBorrower');
    


    // Accounting Routes
    Route::GET('/accountingdashboard', [AccountingController::class, 'accountingDashboard'])->name('accounting.dashboard');
    Route::GET('/accounting_sales', [AccountingController::class, 'salesReport'])->name('accounting.sales_report');
    Route::GET('/accounting_returns', [AccountingController::class, 'returnReport'])->name('accounting.returned_items');
    Route::GET('/accounting_voids', [AccountingController::class, 'void_report'])->name('accounting.void_report');

    Route::POST('/accountinglogout', [AccountingController::class, 'accountinglogout'])->name('accounting.logout');
    Route::GET('/accounting_userprofile', [AccountingController::class, 'userprofile'])->name('accounting.userprofile');

    Route::GET('/chargetransaction', [AccountingController::class, 'chargeTransaction'])->name('accounting.chargeTransaction');
    Route::get('/get-transaction-details/{id}', [AccountingController::class, 'getTransactionDetails']);
    Route::post('/update-transaction-status/{id}', [AccountingController::class, 'updateTransactionStatus']);

    Route::GET('/accounting_damageitems', [AccountingController::class, 'damageitems'])->name('accounting.damage_items');
});

// Inventory Login Authentication
Route::GET('/inventorylogin', [InventoryController::class, 'inventorylogin'])->name('inventory.login');
Route::POST('/inventorylogin', [InventoryController::class, 'authenticate'])->name('inventory.authenticate');

// Cashier Login Authentication
Route::GET('/cashierlogin', [CashierController::class, 'cashierlogin'])->name('cashier_login');
Route::POST('/cashierlogin', [CashierController::class, 'authenticate'])->name('cashier.authenticate');

// Admin Login Authentication
Route::GET('/adminlogin', [AdminController::class, 'adminlogin'])->name('admin_login');
Route::POST('/adminlogin', [AdminController::class, 'authenticate'])->name('admin.authenticate');


// Accounting Login Authentication
Route::GET('/accountinglogin', [AccountingController::class, 'accountinglogin'])->name('accounting_login');
Route::POST('/accountinglogin', [AccountingController::class, 'authenticate'])->name('accounting.authenticate');