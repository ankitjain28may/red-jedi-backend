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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Repo::all();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return Redirect::to('http://ankitjain28may.github.io');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function add()
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
        $id = User::where('userId', $getUser['userId'])->first();
        $weeklyCommits = 0;

        $client = new Client();
        $res = $client->request(
            'GET', 'https://api.github.com/users/'.$id->login.'/repos?type=owner&sort=pushed&client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET')
        );

        $result = $res->getBody();

        $result = json_decode($result, true);

        foreach ($result as $key => $value) {

            $validator = Validator::make(
                [
                'userId' => $value['id'],
                'fullName' => $value['full_name']
                ],
                [
                'userId' => 'required|unique:repos',
                'fullName' => 'required|unique:repos',
                ]
            );

            if (!$validator->fails()) {
                $repo = new Repo;
                $repo->userId = $id->id;
                $repo->repoId = $value['id'];
            } else {
                $repo = Repo::where(['repoId' =>$value['id'], 'userId' => $id->id])->first();
            }

            $repo->name = $value['name'];
            $repo->fullName = $value['full_name'];
            $repo->description = $value['description'];
            $repo->stars = $value['stargazers_count'];
            $repo->forks = $value['forks_count'];
            $repo->language = $value['language'];

            $res = $client->request(
                'GET', 'https://api.github.com/repos/'.$value['full_name'].'/stats/participation?client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET')
            );

            $commits = $res->getBody();
            $commits = json_decode($commits, true);

            if ($commits != null) {
                if ($commits['owner'] != null) {
                    $repo->weeklyCommits = end($commits['owner']);
                    $weeklyCommits += $repo->weeklyCommits;
                }

                if ($commits['all'] != null) {
                    $repo->totalWeeklyCommits = end($commits['all']);
                }
            }
            $repo->save();
        }

        User::find($id->id)->update(['weeklyCommits' => $weeklyCommits]);

        return Redirect::to('http://redjedi.surge.sh/');

    }

}
