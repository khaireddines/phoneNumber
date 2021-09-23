<?php

namespace App\Constants;

# Constant file that contain all the variables used throughout the code
class Constants
{
    const BANDWIDTH_USER = 'steve@newcall.us';
    const BANDWIDTH_Password = 'M@dhouse501!!';
    const BANDWIDTH_BASE_URL = 'https://dashboard.bandwidth.com/api/';
    const BANDWIDTH_ACCOUNT_ID = '5007438';
    # Constant that fixate all the acceptable Parameters for Phone Number Search
    const QUERY_PARAMS = [
        'areaCode',
        'npaNxx',
        'npaNxxx',
        'rateCenter',
        'state',
        'city',
        'zip',
        'lata',
        'localVanity',
        'tollFreeVanity',
        'tollFreeWildCardPattern',
        'quantity',
        'enableTNDetail',
        'LCA',
        'endsIn',
        'orderBy',
        'protected'
    ];
    # Constant that fixate all the acceptable Parameters for Sub-Account Address
    const ADDRESS = [
        'HouseNumber',
        'HousePrefix',
        'HouseSuffix',
        'StreetName',
        'StreetSuffix',
        'AddressLine2',
        'City',
        'StateCode',
        'Zip',
        'PlusFour',
        'County',
        'Country',
        'AddressType'
    ];
}
