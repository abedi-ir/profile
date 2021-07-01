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

    /**
     * @param int|string $id
     */
    public function findByID(Request $request, $id): Response
    {
        if (!is_numeric($id)) {
            throw new NotFoundHttpException();
        }
        $this->api->forUser($request->user());

        $profile = $this->api->find((int) $id);
        if (!$profile) {
            throw new NotFoundHttpException();
        }

        return response(array(
            "status" => true,
            "profile" => $profile,
        ));
    }

    /**
     * @param int|string $id
     */
    public function edit(Request $request, $id): Response
    {
        if (!is_numeric($id)) {
            throw new NotFoundHttpException();
        }
        $this->api->forUser($request->user());

        $profile = $this->api->edit((int) $id, $request->all());

        return response(array(
            "status" => true,
            "profile" => $profile,
        ));
    }

    /**
     * @param int|string $id
     */
    public function delete(Request $request, $id): Response
    {
        if (!is_numeric($id)) {
            throw new NotFoundHttpException();
        }
        $this->api->forUser($request->user());

        $this->api->delete((int) $id);

        return response(["status" => true]);
    }
}
