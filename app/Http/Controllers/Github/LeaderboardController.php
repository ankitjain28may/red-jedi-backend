<?php

namespace App\Http\Controllers\Github;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Repo;
use Illuminate\Support\Facades\Input;


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
        $user = User::orderBy('weeklyCommits', 'DESC')->take($limit)->get();;
        return $user;
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
        $repo = Repo::orderBy('totalWeeklyCommits', 'DESC')->orderBy('stars', 'DESC')->orderBy('forks', 'DESC')->take($limit)->get();
        return $repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allUsers()
    {
        $repo = User::get(['name', 'userId']);
        return $repo;
    }
}
