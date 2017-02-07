<?php

namespace App\Http\Controllers\Github;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Repo;
use Illuminate\Support\Facades\Input;
use GuzzleHttp\Psr7\Request as Requests;
use GuzzleHttp\Client;


class LeaderboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function topUser()
    {
        $input = Input::all();
        $limit = 10;
        if(!empty($input))
        {
            $limit = $input['limit'];
        }
        $user = User::orderBy('weeklyCommits', 'DESC')->get();;
        return $user->take($limit);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function topRepo()
    {
        $input = Input::all();
        $limit = 10;
        if(!empty($input))
        {
            $limit = $input['limit'];
        }
        $repo = Repo::orderBy('totalWeeklyCommits', 'DESC')->orderBy('weeklyCommits', 'DESC')->orderBy('stars', 'DESC')->orderBy('forks', 'DESC')->get();
        return $repo->take($limit);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allUsers()
    {
        $repo = User::get(['name', 'userId', 'login']);
        return $repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sum()
    {
        $repo = User::get(['name', 'userId', 'login']);
        return $repo;
    }


    public function check()
    {
        $client = new Client();
        $request = new Requests('GET', 'https://api.github.com/repos/ankitjain28may/openchat/stats/contributors');
        $response = $client->send($request);

        return dd($response->getBody());
    }
}
