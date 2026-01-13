<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Users\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $options=$request->only(keys: ['name','email']);
        $users=$this->userService->getAll($options);
        return $this->SuccessMessage([
            'data'=>$users,
        ],'Successfully Get All Users.',200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $data=$this->userService->get($user);
       return $this->SuccessMessage([
            'data'=>$data,
        ],'Successfully Get User.',200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(User $user,UpdateUserRequest $request)
    {

        $data=$this->userService->update($user,$request->validated());
        return $this->SuccessMessage([
               'data'=>$user->load(['permissions','roles'])
        ],'Successfully Update User .',200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $data= $this->userService->destroy( $user );
        return $this->SuccessMessage([
            true
        ],'Successfully Delete User',200);
    }
}
