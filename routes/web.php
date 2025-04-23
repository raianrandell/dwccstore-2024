<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountingController;

// Public Routes
Route::GET('/', function () {
    return redirect()->route('admin_login');
});

// SuperAdmin Routes
Route::GET('/superadminlogin', [SuperAdminController::class, 'superadminlogin'])->name('superadminlogin');
Route::GET('/superadminregistration', [SuperAdminController::class, 'superadminregistration']);
Route::POST('/register_superadmin', [SuperAdminController::class, 'register_superadmin'])->name('register_superadmin');
Route::POST('/superadminlogin', [SuperAdminController::class, 'login_superadmin'])->name('login_superadmin');
Route::POST('/superadminlogout', [SuperAdminController::class, 'logout'])->name('logout');

Route::prefix('superadmin')->group(function () {


    Route::middleware('superadminAuth')->group(function () {
        Route::GET('/superadmin_dashboard', [SuperAdminController::class, 'superadmin_dashboard'])->name('superadmin.dashboard');
        Route::GET('/user_management', [SuperAdminController::class, 'user_management'])->name('superadmin.usermanagement');
        Route::POST('/superadmin/add_user', [SuperAdminController::class, 'add_user'])->name('superadmin.add_user');
        Route::PUT('/superadmin/user/{id}/edit', [SuperAdminController::class, 'edit_user'])->name('superadmin.edit_user');
        Route::PUT('/superadmin/user/{id}/change_password', [SuperAdminController::class, 'change_password'])->name('superadmin.change_password');
    });
});

// Inventory Routes (using 'inventoryAuth' middleware)
Route::prefix('inventory')->group(function () {

    // // Inventory Login Authentication (Accessible without middleware)
    // Route::GET('/inventorylogin', [InventoryController::class, 'inventorylogin'])->name('inventory.login');
    // Route::POST('/authenticate', [InventoryController::class, 'authenticate'])->name('inventory.authenticate');
     

    Route::middleware(['inventoryAuth'])->group(function () {
        // Inventory Routes (Protected by middleware)
        Route::get('/dashboard', [InventoryController::class, 'inventoryDashboard'])->name('inventory.dashboard');
        Route::get('/stock_management', [InventoryController::class, 'stockManagement'])->name('inventory.stockmanagement');
        Route::get('/section_management', [InventoryController::class, 'sectionManagement'])->name('inventory.sectionmanagement');
         Route::post('/section_management', [InventoryController::class, 'addSections'])->name('inventory.addsection');
        Route::get('/category_management', [InventoryController::class, 'categoryManagement'])->name('inventory.categorymanagement');
         Route::post('/category_management/add', [InventoryController::class, 'addCategory'])->name('inventory.addcategory');
         Route::post('/category_management', [InventoryController::class, 'itemsStore'])->name('inventory.itemstore');
         Route::get('/stockmanagement/additem', [InventoryController::class, 'addItem'])->name('inventory.additem');
        Route::get('edititem/{id}', [InventoryController::class, 'editItem'])->name('inventory.edititem');
        Route::put('updateitem/{id}', [InventoryController::class, 'updateItem'])->name('inventory.itemupdate');
        Route::post('/update-stock/{id}', [InventoryController::class, 'updateStock'])->name('inventory.updateStock');
         Route::get('/items', [InventoryController::class, 'getItems'])->name('inventory.items');
        Route::get('/damage_transaction', [InventoryController::class, 'damageTransaction'])->name('inventory.damagetransaction');
        Route::post('/damage-transaction', [InventoryController::class, 'storeDamageTransaction'])->name('inventory.storeDamageTransaction');
        Route::get('/category-items/{categoryId}', [InventoryController::class, 'getCategoryItemsAjax'])->name('inventory.getCategoryItemsAjax');
       Route::get('/total-item-report', [InventoryController::class, 'totalItemReport'])->name('inventory.total_item_report');
       Route::get('/total-item-report/pdf', [InventoryController::class, 'exportTotalItemReportPdf'])->name('inventory.total_item_report.pdf');
        Route::get('/total-item-report/export/{type}', [InventoryController::class, 'export'])->name('inventory.total_item_report_export');
        Route::get('/total-item-report/view/{id}', [InventoryController::class, 'view'])->name('inventory.total_item_report_view');
        Route::get('/damage-item-report/view/{id}', [InventoryController::class, 'view'])->name('inventory.damage_item_report_view');
        Route::get('/damage-item-report', [InventoryController::class, 'damageItemReport'])->name('inventory.damage_item_report');
        Route::get('/damage-item-report/pdf', [InventoryController::class, 'exportDamageItemReportPdf'])->name('inventory.damage_item_report.pdf');
        Route::get('/damage-item-report/excel', [InventoryController::class, 'exportDamageItemReportExcel'])->name('inventory.damage_item_report.excel');
        Route::get('/total-item-report/excel', [InventoryController::class, 'exportTotalItemReport'])->name('inventory.total_item_report.excel');

           //expired item report route
         Route::get('/expired-item-report', [InventoryController::class, 'expiredItemReport'])->name('inventory.expired_item_report');
         Route::get('/expired-item-report/pdf', [InventoryController::class, 'exportExpiredItemReportPdf'])->name('inventory.expired_item_report.pdf');
         Route::get('/expired-item-report/excel', [InventoryController::class, 'exportExpiredItemReportExcel'])->name('inventory.expired_item_report.excel');

        Route::post('/modify-expiration-date', [InventoryController::class, 'modifyExpirationDate'])->name('inventory.modifyExpirationDate');

         Route::get('/user/profile', [InventoryController::class, 'profile'])->name('inventory.userprofile');
          Route::post('/user/profile/update-password', [InventoryController::class, 'changePassword'])->name('inventory.changePassword');
        Route::get('/damageitems', [InventoryController::class, 'damageItems'])->name('inventory.damageitems');
        Route::get('/sections', [InventoryController::class, 'sectionManagement'])->name('sectionmanagement');
         Route::post('/sections/add', [InventoryController::class, 'addSections'])->name('addsection');
        Route::get('/sections/{id}/categories', [InventoryController::class, 'getSectionCategories'])->name('inventory.sections.categories');
         Route::get('/category/{id}/items', [InventoryController::class, 'getCategoryItems'])->name('inventory.getCategoryItems');
        Route::get('/low-stock-item-report', [InventoryController::class, 'lowStockItemReport'])->name('inventory.low_stock_item_report');
        Route::get('/low-stock-item-report/pdf', [InventoryController::class, 'exportLowStockItemReportPdf'])->name('inventory.low_stock_item_report.pdf');
        Route::get('/low-stock-item-report/excel', [InventoryController::class, 'exportLowStockItemReportExcel'])->name('inventory.low_stock_item_report.excel');
       Route::get('/get-stock-logs/{item}', [InventoryController::class, 'getStockLogs']);
      //Transfer Logs
       Route::post('/transfer-item/{id}', [InventoryController::class, 'transferItem'])->name('inventory.transferItem');
       Route::get('/get-higher-priced-items/{itemId}', [InventoryController::class, 'getSimilarItems']);

        Route::get('/togaRenting', [InventoryController::class, 'togaRenting'])->name('inventory.toga_renting');
       Route::post('/add-item-for-rent', [InventoryController::class, 'storeRentItem'])->name('inventory.add_item_for_rent');
        Route::post('/items/{id}/add-stock', [InventoryController::class, 'addStock'])->name('items.addStock');
        Route::get('/items/{id}', [InventoryController::class, 'getItem']);
        Route::GET('services', [InventoryController::class, 'Services'])->name('inventory.services');
         Route::post('/services', [InventoryController::class, 'storeService'])->name('inventory.store_service');  // Route to update an existing service
        Route::put('/services/{id}', [InventoryController::class, 'updateService'])->name('inventory.update_service');
         Route::put('/damage_transactions/{id}', [InventoryController::class, 'updateDamageItem'])->name('inventory.damage_transactions_update');
          Route::post('/logout', [InventoryController::class, 'inventorylogout'])->name('inventory.logout');

             // Route for Price Update
             Route::get('/inventory/price-update', [InventoryController::class, 'showPriceUpdate'])->name('inventory.priceUpdate');

          // Route for transferItems
            Route::get('/inventory/transferItems', [InventoryController::class, 'showTransferItemsPrice'])->name('inventory.transferItemsPrice');     
    });
});

// Inventory Routes (using 'inventoryAuth' middleware)
Route::prefix('cashier')->group(function () {
     
    Route::middleware(['cashierAuth'])->group(function () {
          // Cashier Routes
    Route::GET('/cashierdashboard', [CashierController::class, 'cashierDashboard'])->name('cashier.cashier_dashboard');
    Route::POST('/cashierlogout', [CashierController::class, 'cashierlogout'])->name('cashier.logout');
    Route::GET('/cashier_sales', [CashierController::class, 'fetchItem'])->name('cashier.sales');
    Route::GET('/cashier_saleshistory', [CashierController::class, 'salesHistory'])->name('cashier.sales_history');
    Route::POST('/transactions/save', [CashierController::class, 'saveTransaction'])->name('cashier.save_transaction');
    Route::POST('/cashier/save_items_session', [CashierController::class, 'saveItemsSession'])->name('cashier.save_items_session');
    Route::GET('/cashier/fetch-item-by-barcode', [CashierController::class, 'fetchItemByBarcode'])->name('cashier.fetch_item_by_barcode');
    // Example - Adjust controller and method name as needed
    Route::get('/cashier/credit-transaction/{id}/details', [CashierController::class, 'getCreditTransactionDetails'])
    ->name('cashier.getCreditTransactionDetails');
    Route::GET('/cashier_voidrecords', [CashierController::class, 'voidRecords'])->name('cashier.void_records');
    Route::post('/cashier/save_void_records', [CashierController::class, 'saveVoidRecords'])->name('cashier.save_void_records');

    Route::POST('/cashier/update-stock', [CashierController::class, 'updateStock'])->name('cashier.update_stock');
    Route::GET('/cashier_credit', [CashierController::class, 'credit'])->name('cashier.credit');
    Route::get('/fines-transaction', [CashierController::class, 'finesTransaction'])->name('cashier.fines_transaction');
    Route::post('/return-item', [CashierController::class, 'returnItem'])->name('cashier.returnItem');
    Route::post('/process-payment', [CashierController::class, 'processPayment'])->name('cashier.processPayment');
    Route::get('/fines-history', [CashierController::class, 'finesHistory'])->name('cashier.fines_history');
    Route::GET('/cashier_returns', [CashierController::class, 'returns'])->name('cashier.returns');
    // New route for paying late fees
Route::post('/cashier/pay-late-fees', [CashierController::class, 'payLateFees'])->name('cashier.payLateFees');
    

    Route::GET('/cashier_salesreport', [CashierController::class, 'sales_report'])->name('cashier.sales_report');
    Route::get('/cashier/sales-report/pdf', [CashierController::class, 'exportSalesReportPdf'])->name('cashier.sales_report.pdf');
    Route::get('/cashier/sales-report/excel', [CashierController::class, 'exportSalesReportExcel'])->name('cashier.sales_report.excel');

    Route::GET('/cashier_voidreport', [CashierController::class, 'void_report'])->name('cashier.void_report');
    Route::get('/cashier/void-item-report/pdf', [CashierController::class, 'exportVoidItemReportPdf'])->name('cashier.void_item_report.pdf');
    Route::get('/cashier/void-item-report/excel', [CashierController::class, 'exportVoidItemReportExcel'])->name('cashier.void_item_report.excel');
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
    Route::post('/user/profile/update-password', [CashierController::class, 'changePassword'])->name('cashier.updatePassword');


    //Fines Report
    Route::GET('/cashier/toga_fines_report', [CashierController::class, 'finesReport'])->name('cashier.toga_fines_report');
    Route::get('/cashier/toga_fines_report/pdf', [CashierController::class, 'exportFinesReportPdf'])->name('cashier.toga_fines_report_pdf');
    Route::get('/cashier/toga_fines_report/excel', [CashierController::class, 'exportFinesReportExcel'])->name('cashier.toga_fines_report_excel');
    });
});




// Inventory Routes (using 'inventoryAuth' middleware)
Route::prefix('admin')->group(function () {
     
    Route::middleware(['adminAuth'])->group(function () {
        // Admin Routes
        Route::GET('/admindashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');

        Route::GET('/admin_reports', [AdminController::class, 'reports'])->name('admin.reports');
        Route::GET('/admin_userprofile', [AdminController::class, 'userProfile'])->name('admin.userprofile');

        Route::get('/toga-fines', [AdminController::class, 'togaFines'])->name('admin.toga_fines');
        Route::post('/add-borrower', [AdminController::class, 'addBorrower'])->name('admin.addBorrower');
        Route::post('/return-borrower', [AdminController::class, 'returnBorrower'])->name('admin.returnBorrower');

        Route::POST('/adminlogout', [AdminController::class, 'adminlogout'])->name('admin.logout');
        Route::post('/user/profile/update-password', [AdminController::class, 'changePassword'])->name('admin.changePassword');

        Route::get('/total-item-report', [AdminController::class, 'totalItemReport'])->name('admin.total_item_report');
        Route::get('/total-item-report/pdf', [AdminController::class, 'exportTotalItemReportPdf'])->name('admin.total_item_report.pdf');
        Route::get('/total-item-report/excel', [AdminController::class, 'exportTotalItemReport'])->name('admin.total_item_report.excel');

        Route::GET('/admin_sales', [AdminController::class, 'salesReport'])->name('admin.sales_report');
        Route::get('/admin/sales-report/pdf', [AdminController::class, 'exportSalesReportPdf'])->name('admin.sales_report.pdf');
        Route::get('/admin/sales-report/excel', [AdminController::class, 'exportSalesReportExcel'])->name('admin.sales_report.excel');

        Route::get('/damage-item-report', [AdminController::class, 'damageItemReport'])->name('admin.damage_item_report');
        Route::get('/damage-item-report/pdf', [AdminController::class, 'exportDamageItemReportPdf'])->name('admin.damage_item_report.pdf');
        Route::get('/damage-item-report/excel', [AdminController::class, 'exportDamageItemReportExcel'])->name('admin.damage_item_report.excel');

        Route::GET('/admin_voidreport', [AdminController::class, 'void_report'])->name('admin.void_report');
        Route::get('/admin/void-item-report/pdf', [AdminController::class, 'exportVoidItemReportPdf'])->name('admin.void_item_report.pdf');
        Route::get('/admin/void-item-report/excel', [AdminController::class, 'exportVoidItemReportExcel'])->name('admin.void_item_report.excel');

        Route::GET('/admin_returnreport', [AdminController::class, 'returnReport'])->name('admin.return_item_report');
        Route::get('/admin/returned-item-report/pdf', [AdminController::class, 'exportReturnedItemReportPdf'])->name('admin.returned_item_report_pdf');
        Route::get('/admin/returned-item-report/excel', [AdminController::class, 'exportReturnedItemReportExcel'])->name('admin.returned_item_report_excel');

          //Fines Report
        Route::GET('/admin/toga_fines_report', [AdminController::class, 'finesReport'])->name('admin.toga_fines_report');
        Route::get('/admin/toga_fines_report/pdf', [AdminController::class, 'exportFinesReportPdf'])->name('admin.toga_fines_report_pdf');
        Route::get('/admin/toga_fines_report/excel', [AdminController::class, 'exportFinesReportExcel'])->name('admin.toga_fines_report_excel');
    });
});

// Inventory Routes (using 'inventoryAuth' middleware)
Route::prefix('accounting')->group(function () {
     
    Route::middleware(['accountingAuth'])->group(function () {
        // Accounting Routes
        Route::GET('/accountingdashboard', [AccountingController::class, 'accountingDashboard'])->name('accounting.dashboard');

        Route::GET('/accounting_returns', [AccountingController::class, 'returnReport'])->name('accounting.returned_items');
       

        Route::POST('/accountinglogout', [AccountingController::class, 'accountinglogout'])->name('accounting.logout');
        Route::GET('/accounting_userprofile', [AccountingController::class, 'userProfile'])->name('accounting.userprofile');

        Route::GET('/chargetransaction', [AccountingController::class, 'chargeTransaction'])->name('accounting.chargeTransaction');
    // Route for fetching transaction details
    Route::get('/get-transaction-details/{id}', [AccountingController::class, 'getTransactionDetails'])->name('accounting.getTransactionDetails');

    // Route for updating transaction status
    Route::post('/update-transaction-status/{id}', [AccountingController::class, 'updateTransactionStatus'])->name('accounting.updateTransactionStatus');


        Route::post('/user/profile/update-password', [AccountingController::class, 'changePassword'])->name('accounting.changePassword');

        Route::GET('/accounting_sales', [AccountingController::class, 'salesReport'])->name('accounting.sales_report');
        Route::get('/accounting/sales-report/pdf', [AccountingController::class, 'exportSalesReportPdf'])->name('accounting.sales_report.pdf');
        Route::get('/accounting/sales-report/excel', [AccountingController::class, 'exportSalesReportExcel'])->name('accounting.sales_report.excel');

        Route::GET('/accounting_returns', [AccountingController::class, 'returnReport'])->name('accounting.return_item_report');
        Route::get('/accounting/returned-item-report/pdf', [AccountingController::class, 'exportReturnedItemReportPdf'])->name('accounting.returned_item_report_pdf');
        Route::get('/accounting/returned-item-report/excel', [AccountingController::class, 'exportReturnedItemReportExcel'])->name('accounting.returned_item_report_excel');
        
        Route::GET('/accounting_voids', [AccountingController::class, 'void_report'])->name('accounting.void_report');
        Route::get('/accounting/void-item-report/pdf', [AccountingController::class, 'exportVoidItemReportPdf'])->name('accounting.void_item_report.pdf');
        Route::get('/accounting/void-item-report/excel', [AccountingController::class, 'exportVoidItemReportExcel'])->name('accounting.void_item_report.excel');

        Route::GET('/accounting_damageitems', [AccountingController::class, 'damageitems'])->name('accounting.damage_items');
        Route::get('/damage-item-report/pdf', [AccountingController::class, 'exportDamageItemReportPdf'])->name('accounting.damage_item_report.pdf');
        Route::get('/damage-item-report/excel', [AccountingController::class, 'exportDamageItemReportExcel'])->name('accounting.damage_item_report.excel');

        Route::get('/total-item-report', [AccountingController::class, 'totalItemReport'])->name('accounting.total_item_report');
        Route::get('/total-item-report/pdf', [AccountingController::class, 'exportTotalItemReportPdf'])->name('accounting.total_item_report.pdf');
        Route::get('/total-item-report/excel', [AccountingController::class, 'exportTotalItemReport'])->name('accounting.total_item_report.excel');

        Route::get('/damage-item-report', [AccountingController::class, 'damageItemReport'])->name('accounting.damage_item_report');
        Route::get('/damage-item-report/pdf', [AccountingController::class, 'exportDamageItemReportPdf'])->name('accounting.damage_item_report.pdf');
        Route::get('/damage-item-report/excel', [AccountingController::class, 'exportDamageItemReportExcel'])->name('accounting.damage_item_report.excel');

        //Fines Report
        Route::GET('/accounting/toga_fines_report', [AccountingController::class, 'finesReport'])->name('accounting.toga_fines_report');
        Route::get('/accounting/toga_fines_report/pdf', [AccountingController::class, 'exportFinesReportPdf'])->name('accounting.toga_fines_report_pdf');
        Route::get('/accounting/toga_fines_report/excel', [AccountingController::class, 'exportFinesReportExcel'])->name('accounting.toga_fines_report_excel');
    });
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