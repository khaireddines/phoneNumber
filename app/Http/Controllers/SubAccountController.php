<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Validation\Rule;
use Iris\Account;
use Iris\Client;
use Iris\FieldRequiredException;

/**
 * The Class that will take care of creating the Sub-Account(sites)
 * before you can make number any number orders
 */
class SubAccountController extends Controller
{
    /** Declaring the Bandwidth Client and account */
    /**
     * @var Client
     */
    protected $BW_CLIENT;
    /**
     * @var Account
     */
    protected $BW_ACCOUNT;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        /** Instantiate the Bandwidth Client once */
        $this->BW_CLIENT = new Client(
            Constants::BANDWIDTH_USER,
            Constants::BANDWIDTH_Password,
            ['url' => Constants::BANDWIDTH_BASE_URL]
        );
        /** Instantiate the Bandwidth Account once */
        $this->BW_ACCOUNT = new Account(
            Constants::BANDWIDTH_ACCOUNT_ID,
            $this->BW_CLIENT
        );
    }

    /**
     * The method that create the Sub-Account
     * @param Request $request
     * @return Application|ResponseFactory|Response
     * @throws FieldRequiredException
     */
    public function create(Request $request)
    {
        /** we validate all the data before we pass it through to bandwidth system,
         * as anything can produce an error or an order failure
         * or even an account suspension cause of spam
         */
        $validator = Validator::make($request->all(),
            [
                'Name' => ['required', 'string', 'max:10'],
                'Description' => ['string'],
                'CustomerProvidedID' => ['string', 'max:10'],
                'CustomerName' => ['string', 'max:50'],
                'PeerName' => ['required','string','min:10'],
                'Address' => ['required', 'array']
            ],
        [
            'PeerName.required' => 'You need to provide a default SiP Peer Name',
        ]);
        if ($validator->fails())
            return response($validator->errors());

        $validator->after(function ($validator) {
            foreach ($validator->getData()['Address'] as $key => $value)
                if (!in_array($key, Constants::ADDRESS))
                    $validator->errors()->add($key, 'Invalid ADDRESS Params, available : ' . implode(" | ", Constants::ADDRESS));
        });
        if ($validator->fails())
            return response($validator->errors());
        $validateAddress = Validator::make($request->Address,
            [
                'HouseNumber' => ['required', 'string'],
                'HousePrefix' => ['string'],
                'HouseSuffix' => ['string'],
                'StreetName' => ['required', 'string'],
                'StreetSuffix' => ['string'],
                'AddressLine2' => ['string'],
                'City' => ['required', 'string'],
                'StateCode' => ['required', 'string', 'min:2', 'max:2'],
                'Zip' => ['required', 'string', 'min:5', 'max:5'],
                'PlusFour' => ['string', 'min:4', 'max:4'],
                'County' => ['string'],
                'Country' => ['string'],
                'AddressType' => ['required', 'string', Rule::in(['Billing', 'Service'])]
            ]);
        if ($validateAddress->fails())
            return response($validateAddress->errors());

        /** Create the Sub-Account (Site) */
        $site = $this->BW_ACCOUNT->sites()->create($request->all());
        /** Each Sub-Account need to have at least one peer (default peer) also known as location in bandwidth dashboard */
        $DefaultPeer = $this->BW_ACCOUNT->sites()->site($site->get_id())->sippeers()->create([
            'PeerName' => request('PeerName'),
            'IsDefaultPeer' => true
        ]);
        /** Return the Site that has been created */
        return response($site->to_array());
    }

}
