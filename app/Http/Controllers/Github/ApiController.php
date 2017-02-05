<?php

namespace App\Http\Controllers\Github;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Repo;
use Illuminate\Support\Facades\Input;
use Validator;
use GuzzleHttp\Client;
use Redirect;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        foreach (User::all() as $key => $user) {

            $weeklyCommits = 0;

            $client = new Client();

            $api = [
            0 => 'https://api.github.com/users/'.$user->login.'/repos?type=owner&sort=pushed&client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET')
            // 1 => 'https://api.github.com/users/'.$user->login.'/repos?type=member&sort=pushed&client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET')
            ];

            foreach ($api as $keys => $url) {

                $res = $client->request(
                    'GET', $url
                );

                $result = $res->getBody();

                $result = json_decode($result, true);

                Repo::where(['userId' => $user->id])->update(['weeklyCommits' => 0, 'totalWeeklyCommits' => 0]);

                Repo::where(['userId' => $user->id])->get();

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
                        $repo->userId = $user->id;
                        $repo->repoId = $value['id'];
                    } else {
                        $repo = Repo::where(['repoId' =>$value['id'], 'userId' => $user->id])->first();
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
            }
            User::find($user->id)->update(['weeklyCommits' => $weeklyCommits]);
        }
        return Redirect::to('/');
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
        //
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
}
