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

            $client = new Client(['base_uri' => 'https://api.github.com/']);

            $api = [
                0 => 'users/'.$user->login.'/repos?type=owner&sort=pushed&client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET'),
                1 => 'users/'.$user->login.'/repos?type=member&sort=pushed&client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET')
            ];

            Repo::where(['userId' => $user->id])->update(['weeklyCommits' => 0, 'totalWeeklyCommits' => 0]);

            foreach ($api as $keys => $url) {
                $res = $client->request(
                    'GET', $url
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

                    if ($value != null) {
                        $repo->repoId = $value['id'];
                        $repo->name = $value['name'];
                        $repo->fullName = $value['full_name'];
                        $repo->description = $value['description'];
                        $repo->stars = $value['stargazers_count'];
                        $repo->forks = $value['forks_count'];
                        $repo->language = $value['language'];

                        $res = $client->request(
                            'GET', 'repos/'.$value['full_name'].'/stats/participation?client_id='.env('GITHUB_CLIENT_ID').'&client_secret='.env('GITHUB_CLIENT_SECRET')
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
