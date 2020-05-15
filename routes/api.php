<?php
/**
 *
 */

Route::group([
    'namespace' => 'Api'
],function (){

    //Cruise
    Route::get('Cruise','CruiseController@index');
    Route::post('Cruise','CruiseController@index');
    Route::any('Cruise/Search','CruiseController@search');
    Route::any('Cruise/SearchFields','CruiseController@searchFields');
    Route::post('Cruise/{id}/bookingPrice','BookingController@bookingPrice');
    Route::post('Cruise/{id}/booking','BookingController@booking');
    Route::get('Cruise/id/{id}','CruiseController@getById');
    Route::get('Cruise/vendorId/{vendor}','CruiseController@getByVendorId');
    Route::get('Cruise/shipId/{shipId}','CruiseController@getByShipId');
    Route::get('Cruise/directionId/{directionId}','CruiseController@getByDirectionId');

    //CruiseCabin
    Route::get('CruiseCabin/id/{cabinId}','CruiseCabinController@getById');
    Route::get('CruiseCabin/cruiseId/{cruiseId}','CruiseCabinController@getByCruiseId');
    Route::get('CruiseCabin/cruiseId/{cruiseId}/categoryId/{categoryId}','CruiseCabinController@getByCategoryId');

    //CruiseRoute
    Route::get('CruiseRoute/cruiseId/{cruiseId}','CruiseRouteController@getByCruiseId');

    //CruiseDirection
    Route::get('CruiseDirection','CruiseDirectionController@index');

    //Ship
    Route::get('Ship','ShipController@index');
    Route::get('Ship/id/{id}','ShipController@getById');

    Route::get('ShipCabin/shipId/{shipId}','ShipCabinController@getByShipId');
    Route::get('ShipCabin/shipId/{shipId}/deckId/{deckId}','ShipCabinController@getByDeckId');
    Route::get('ShipCabin/shipId/{shipId}/categoryId/{categoryId}','ShipCabinController@getByCategoryId');

    //ShipCabinCategory
    Route::get('ShipCabinCategory/shipId/{shipId}','ShipCabinCategoryController@getByShipId');
    Route::get('ShipCabinCategory/id/{categoryId}','ShipCabinCategoryController@getById');

    //ShipCabinCategoryPhoto
    Route::get('ShipCabinCategoryPhoto/categoryId/{categoryId}','ShipCabinCategoryPhotoController@getAllByCategoryId');

    //ShipCabinPlace
    Route::get('ShipCabinPlace/shipCabinId/{cabinId}','ShipCabinPlaceController@getByShipCabinId');

    //ShipDeck
    Route::get('ShipDeck/id/{deckId}','ShipDeckController@getById');
    Route::get('ShipDeck/shipId/{shipId}','ShipDeckController@getByShipId');
    Route::get('ShipDeck/shipId/{shipId}/id/{id}','ShipDeckController@getById');

    //ShipPhoto
    Route::get('ShipPhoto/shipId/{shipId}','ShipPhotoController@getByShipId');

    //Crm hooks
    Route::any('/crm/deal-update','CrmController@crmDealUpdate');
    Route::any('/crm/deal-delete','CrmController@crmDealDelete');

    //Admin hooks
    Route::any('/Cruise/Update', 'CruiseController@updateAll');
});
