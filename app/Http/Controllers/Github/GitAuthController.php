<?php

namespace App\Http\Controllers\Github;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;
use Redirect;
use App\Model\User;
use App\Model\Repo;
use Illuminate\Support\Facades\Input;
use Validator;
use GuzzleHttp\Client;

class GitAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return Repo::all();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        $user = Socialite::driver('github')->user();

        // OAuth One Providers
        $token = $user->token;

        $refreshToken = $user->refreshToken; // not always provided
        $expiresIn = $user->expiresIn;


        // All Providers
        $getUser = [
            'userId' => $user->getId(),
            'login' => $user->getNickname(),
            'name' => $user->getName(),
            'email' => $user->getEmail()
        ];


        $validator = Validator::make($getUser, [
            'email' => 'email|unique:users',
            'userId' => 'required|unique:users',
        ]);

        if (!$validator->fails()) {

            $user = new User;
            $user->name = $getUser['name'];
            $user->email = $getUser['email'];
            $user->login = $getUser['login'];
            $user->userId = $getUser['userId'];
            $user->weeklyCommits = 0;
            $user->totalCommits = 0;

            $user->save();
        }

        $user = User::where('userId', $getUser['userId'])->first();

        $weeklyCommits = $this->api($user);

        User::find($user->id)->update(['weeklyCommits' => $weeklyCommits]);

        return Redirect::to('http://redjedi.surge.sh/');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        foreach (User::all() as $key => $user) {

            Repo::where(['userId' => $user->id])->update(['weeklyCommits' => 0, 'totalWeeklyCommits' => 0]);

            $weeklyCommits = $this->api($user);

            User::find($user->id)->update(['weeklyCommits' => $weeklyCommits]);
        }
        return Redirect::to('/');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function api($user)
    {
        $weeklyCommits = 0;
        $client = new Client(['base_uri' => 'https://api.github.com/']);

        $params = [
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET')
        ];

        $api = [
            0 => 'users/'.$user->login.'/repos?type=owner&sort=pushed',
            1 => 'users/'.$user->login.'/repos?type=member&sort=pushed'
        ];


        foreach ($api as $keys => $url) {

            $res = $client->request(
                'GET', $url.'&'.http_build_query($params)
            );

            $result = $res->getBody();

            $result = json_decode($result, true);

            foreach ($result as $key => $value) {

                $identifier = $value['id'].":".$user->id;
                $validator = Validator::make(
                    [
                    'identifier' => $identifier,
                    ],
                    [
                    'identifier' => 'required|unique:repos',
                    ]
                );

                if (!$validator->fails()) {
                    $repo = new Repo;
                    $repo->userId = $user->id;
                    $repo->repoId = $value['id'];
                    $repo->identifier = $identifier;
                } else {
                    $repo = Repo::where(['identifier' => $identifier])->first();
                }


                $repo->name = $value['name'];
                $repo->fullName = $value['full_name'];
                $repo->description = $value['description'];
                $repo->stars = $value['stargazers_count'];
                $repo->forks = $value['forks_count'];
                $repo->language = $value['language'];

                $res = $client->request(
                    'GET', 'repos/'.$value['full_name'].'/stats/contributors?'.http_build_query($params)
                );

                $commits = $res->getBody();
                $commits = json_decode($commits, true);

                $totalCommits = 0;
                if (!empty($commits)) {
                    foreach ($commits as $option => $check) {
                        if ($check['author']['id'] == $user->userId) {
                            $repo->weeklyCommits = end($check['weeks'])['c'];
                            $weeklyCommits += $repo->weeklyCommits;
                        }

                        $totalCommits += end($check['weeks'])['c'];
                    }
                }

                $repo->totalWeeklyCommits = $totalCommits;

                $repo->save();
            }
        }
        return $weeklyCommits;
    }

}
