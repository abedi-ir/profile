<?php

namespace Jalno\Profile\Http\Controllers;

use Jalno\Userpanel\API;
use Jalno\Profile\API\Users;
use Jalno\Profile\Models\User;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\{Request, Response};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsersController extends Controller
{

    protected Users $api;
    protected API\Users $userpanelAPI;

    public function __construct(Users $api, API\Users $userpanelAPI)
    {
        $this->api = $api;
        $this->userpanelAPI = $userpanelAPI;
    }

    public function byUser(Request $request): Response
    {
        return $this->findByID($request, $request->user()->id);
    }

    public function findByID(Request $request, $id): Response
    {
        $this->api->forUser($request->user());

        $profile = $this->api->find($id);
        if (!$profile) {
            throw new NotFoundHttpException();
        }

        return response(array(
            "status" => true,
            "profile" => $profile,
        ));
    }

    public function edit(Request $request, $id): Response
    {
        $this->api->forUser($request->user());

        $profile = $this->api->edit(array_merge(["user" => $id], $request->all()));

        return response(array(
            "status" => true,
            "profile" => $profile,
        ));
    }

    public function delete(Request $request, $id): Response
    {
        $this->api->forUser($request->user());

        $this->api->delete($id);

        return response(["status" => true]);
    }
}
