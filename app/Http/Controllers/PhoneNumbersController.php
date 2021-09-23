<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Iris\Account;
use Iris\Client;
use Iris\FieldRequiredException;

class PhoneNumbersController extends Controller
{
    # Declaring the Bandwidth Client and account
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
        # Instantiate the Bandwidth Client once
        $this->BW_CLIENT = new Client(
            Constants::BANDWIDTH_USER,
            Constants::BANDWIDTH_Password,
            ['url' => Constants::BANDWIDTH_BASE_URL]
        );
        # Instantiate the Bandwidth Account once
        $this->BW_ACCOUNT = new Account(
            Constants::BANDWIDTH_ACCOUNT_ID,
            $this->BW_CLIENT
        );
    }

    /**
     * The search method that will show all the available numbers that match the search criteria
     * @param Request $request
     * @return Application|ResponseFactory|Response
     * @throws Exception
     */
    public function search(Request $request)
    {
        /** we validate all the data before we pass it through to bandwidth system,
         * as anything can produce an error or an order failure
         * or even an account suspension cause of spam
         */
        $validator = Validator::make($request->all(),[
            'Query_Params' => ['required','array']
        ],[
            'Query_Params.required' => "The Query_Params type field is required.",
            'Query_Params.array' => "The Query_Params must be an array."
        ]);
        if ($validator->fails())
            return response($validator->errors());

        $validator->after(function ($validator) {
            foreach ($validator->getData()['Query_Params'] as $key => $value)
                if (!in_array($key,Constants::QUERY_PARAMS))
                    $validator->errors()->add($key,'Invalid Query Params, available : '.implode(" | ",Constants::QUERY_PARAMS));
        });
        if ($validator->fails())
            return response($validator->errors());
        /** Submit a search request to bandwidth after we made sure that all data are valid */
        $phoneNumbers = $this->BW_ACCOUNT->availableNumbers(request('Query_Params'));

        return response(['TelephoneNumbers'=>$phoneNumbers[0]->TelephoneNumber]);
    }

    /**
     * The order method that will make the order that will assign a number or a list of numbers to a Sub-Account
     * check "Http/Controllers/SubAccountController.php" for the method of creating the Sub-Account first before you order
     * @param Request $request
     * @return Application|ResponseFactory|Response
     * @throws FieldRequiredException
     */
    public function order(Request $request)
    {
        /** we validate all the data before we pass it through to bandwidth system,
         * as anything can produce an error or an order failure
         * or even an account suspension cause of spam
         */
        $validator = Validator::make($request->all(),[
            'order_name' => ['required','string','min:5'],
            'Site_ID' => ['required','string'],
            'TelephoneNumberList' => ['required','array'],

        ],[
            "TelephoneNumberList.array" => "The telephone number list must be an array of valid phone numbers."
        ]);
        if ($validator->fails())
            return response($validator->errors());

        /** Finally we create the order and assign the selected numbers to that specific sub-Account created */
        $order = $this->BW_ACCOUNT->orders()->create([
            "Name" => request('order_name'),
            "SiteId" => request('Site_ID'),
            "CustomerOrderId" => (string)Str::uuid(),
            "ExistingTelephoneNumberOrderType" => [
                "TelephoneNumberList" => [
                    "TelephoneNumber" => request('TelephoneNumberList')
                    ]
            ]
        ]);
        /** Get the status of the order and return it as a result to show it to the user  */
        $response = $this->BW_ACCOUNT->orders()->order($order->id, true);
        return response($response->to_array());
    }
}
