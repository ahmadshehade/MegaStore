<?php

namespace App\Http\Controllers\Api\V1\Adress;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Adress\StoreAdressRequest;
use App\Http\Requests\Api\V1\Adress\UpdateAdressRequest;
use App\Models\Address;
use App\Services\AddressSevice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AddressController extends Controller
{

    use AuthorizesRequests;
    protected $address;

    /**
     * Summary of __construct
     * @param \App\Services\AddressSevice $address
     */
    public function __construct(AddressSevice $address)
    {
        $this->address = $address;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Address::class);
        $filters = $request->only(['country', 'city', 'address', 'postal_code', 'phone']);
        $data = $this->address->getAll($filters);
        return $this->SuccessMessage(['Addresses' => $data], 'Suceessfully Get All Address .', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdressRequest $request)
    {
        $this->authorize('create', Address::class);
        $address = $this->address->store($request->validated());
        return $this->SuccessMessage(['address' => $address], 'Successfully Make New Adderss.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        $this->authorize('view', $address);
        $adress = $this->address->get($address);
        return $this->SuccessMessage(['address' => $address], 'Successfully Get Address .', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdressRequest $request, Address $address)
    {
        $this->authorize('update', $address);
        $address = $this->address->update($request->validated(), $address);
        return $this->SuccessMessage(['address' => $address], 'Sucessfully Update Address .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        $this->authorize('delete', $address);
        $data = $this->address->destroy($address);
        return $this->SuccessMessage(['status' => true], 'Successfully Delete Address.', 200);
    }
}
