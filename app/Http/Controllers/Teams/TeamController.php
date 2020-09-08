<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Repositories\Contracts\IInvitation;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;

class TeamController extends Controller
{
    protected $teams;
    protected $users;
    protected $invitations;

    public function __construct(ITeam $teams , IUser $users,IInvitation $invitations)
    {
        $this->teams = $teams;
        $this->users = $users;
        $this->invitations = $invitations;
    }

    public function index(Request $request)
    {

    }

    public function store(Request $request)
    {
        $this->validate($request , [

            'name' => ['required','string','max:80', 'unique:teams,name']

        ]);

        $team = $this->teams->create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);


    }

    public function update(Request $request,$id)
    {

        $team = $this->teams->find($id);

        $this->authorize('update',$team);

        $this->validate($request,[

            'name' => ['required' , 'string' ,'max:80', 'unique:teams,name,'.$id]
        ]);

       $team =   $this->teams->update($id,[
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }
    public function findById($id)
    {
        $team = $this->teams->find($id);

        return new TeamResource($team);
    }
    public function fetchUserTeams()
    {

        $team = $this->teams->fetchUserTeams();

        return TeamResource::collection($team);
    }

    public function findBySlug($slug)
    {

    }
    public function destroy($id)
    {

    }

    public function invite(Request $request)
    {

    }

    public function removeFromTeam($team_id ,$user_id)
    {

        //get team

        $team = $this->teams->find($team_id);

        $user = $this->users->find($user_id);

        if($user->isOwnerOfTeam($team))
        {
            return response()->json(['message' => 'You are team owner']);
        }

        if(!auth()->user()->isOwnerOfTeam($team) && auth()->id() !== $user->id)
        {

            return response()->json(['message' => 'You are not allowed to do this']);
        }

        $this->invitations->removeUserFromTeam($team,$user);

        return response()->json(['message' => 'success']);
    }
}
