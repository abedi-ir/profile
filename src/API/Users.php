<?php
namespace Jalno\Profile\API;

use Jalno\Userpanel\API;
use Jalno\Userpanel\Models\{Log, User};
use Jalno\Profile\Models\User as Profile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @phpstan-type ProfileUpdateParameters array{"name"?:string,"lastname"?:string,"phone":string,"city"?:string,"address"?:string,"web"?:string,"social_networks"?:array<string,string>}
 */
class Users extends API\API
{

    protected API\Users $api;

    public function __construct(API\Users $api)
    {
        $this->api = $api;
    }

    /**
     * @param int|array<string,mixed> $parameters
     */
    public function find($parameters): ?Profile
    {
        $this->api->forUser($this->user());
        return $this->api->find($parameters)->profile ?? null;
    }

    /**
     * @param int|array<string,mixed> $parameters
     */
    public function edit(int $id, $parameters): Profile
    {
        $this->requireAbility("profile_edit");
        $this->api->forUser($this->user());

        $user = $this->api->find($id);
        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, $id);
        }

        /**
         * @var ProfileUpdateParameters $parameters
         */
        return $this->update($parameters, $user);
    }

    /**
     * @param int|array<string,mixed> $parameters
     */
    public function delete($parameters): void
    {
        $this->requireAbility("profile_delete");
        $this->api->forUser($this->user());

        if (is_numeric($parameters)) {
            $parameters = ["id" => ["in" => [$parameters]]];
        }

        $paginator = null;
        $logParameters = [
            "old" => [],
        ];

        do {
            $paginator = $this->api->search($parameters, 100, ['*'], 'cursor', $paginator ? $paginator->nextCursor() : null);

            if ($paginator->count() < 1) {
                throw (new ModelNotFoundException)->setModel(User::class);
            }
    
            foreach ($paginator as $item) {
                $logParameters["old"][] = $item->profile->toArray();
                $item->profile->delete();
            }

        } while($paginator->hasMorePages());

        if (!empty($logParameters["old"]) and !is_null($this->user())) {
            $log = new Log();
            $log->user_id = $this->user()->id;
            $log->type = "jalno.profile.logs.delete";
            $log->parameters = $logParameters;
            $log->save();
        }
    }

    /**
     * @param ProfileUpdateParameters $parameters
     */
    protected function update(array $parameters, User $user): Profile
    {
        $inputs = $parameters;

        $parameters = Validator::validate($parameters, array(
            'avatar' => ['sometimes', 'image'],
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email'],
            'cellphone' => ['sometimes', 'cellphone'],
            'lastname' => ['sometimes', 'string'],
            'phone' => ['sometimes', 'string'], // TODO: We need phone validator here.
            'city' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string'],
            'web' => ['sometimes', 'url'],
            'social_networks' => ['sometimes', 'array'],
            'social_networks.*' => ['string'],
        ));

        if (isset($parameters["social_networks"])) {
            foreach ($parameters["social_networks"] as $social => $username) {
                if (!is_string($social) or !$username) {
                    throw ValidationException::withMessages(["social_networks.{$social}" => "The selected social media is invalid"]);
                }
            }
        }

        $profile = $user->profile;

        $logParameters = [
            "old" => [],
            "new" => [],
        ];

        if (!$profile) {
            $profile = new Profile();
            $profile->user_id = $user->id;
        }

        if (isset($parameters["avatar"])) {
            $disk = package()->storage()->public();

            $path = "users/" . md5_file($parameters["avatar"]->path()) . "." . $parameters["avatar"]->clientExtension();

            if (!method_exists($disk, "has") or !$disk->has($path)) {

                $moved = $disk->put($path, $parameters["avatar"]->get());

                if (!$moved) {
                    throw ValidationException::withMessages(["avatar" => "The selected file could not be uploaded."]);
                }
            }
            $parameters["avatar"] = $path;
        }

        foreach ($parameters as $input => $value) {
            if (in_array($input, ["social_networks"])) {
                continue;
            }

            if ($profile->{$input} != $value) {
                $logParameters["new"][$input] = $value;
                if ($profile->id) {
                    $logParameters["old"][$input] = $profile->{$input};
                }
            }
            $profile->{$input} = $value;
        }

        if (isset($parameters["social_networks"])) {

            $socialnetworks = $profile->social_networks;

            if (empty($parameters["social_networks"])) {
                $logParameters["old"]["social_networks"] = $socialnetworks;
                $socialnetworks = [];
            } else {
                if (!is_array($socialnetworks)) {
                    $socialnetworks = [];
                }

                $logParameters["new"]["social_networks"] = [];
                foreach ($socialnetworks as $social => $username) {
                    if (!isset($parameters["social_networks"][$social])) {
                        $logParameters["old"]["social_networks"][$social] = $username;
                        unset($socialnetworks[$social]);
                    }
                }
                foreach ($parameters["social_networks"] as $social => $username) {
                    if (isset($socialnetworks[$social]) and $socialnetworks[$social] != $username) {
                        $logParameters["old"]["social_networks"][$social] = $socialnetworks[$social];
                    }
                    $logParameters["new"]["social_networks"][$social] = $username;
                    $socialnetworks[$social] = $username;
                }
            }

            $profile->social_networks = $socialnetworks;
        }

        $profile->saveOrFail();

        $isEditing = !empty($logParameters["old"]);

        if (!is_null($this->user()) and $this->user()->id != $user->id) {
            $log = new Log();
            $log->user_id = $this->user()->id;
            $log->type = "jalno.profile.logs." . ($isEditing ? "edit" : "add");
            $log->parameters = $logParameters;
            $log->save();
        }

        $log = new Log();
        $log->user_id = $user->id;
        $log->type = "jalno.profile.logs." . ($isEditing ? "update" : "add");
        $log->parameters = $logParameters;
        $log->save();

        return $profile;
    }
}
