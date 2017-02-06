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
        $user = User::orderBy('weeklyCommits', 'DESC')->get();;
        return $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function topRepo()
    {
        $repo = Repo::orderBy('totalWeeklyCommits', 'DESC')->orderBy('stars', 'DESC')->orderBy('forks', 'DESC')->get();
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
