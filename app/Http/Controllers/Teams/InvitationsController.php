<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use App\Mail\sendInvitationToJoinTeam;
use App\Models\Team;
use App\Repositories\Contracts\IInvitation;

class InvitationsController extends Controller
{
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(IInvitation $invitations,ITeam $teams,IUser $users)
    {

        $this->invitations = $invitations;
        $this->teams = $teams;
        $this->users = $users;
    }

    public function invite(Request $request,$teamId)
    {
        //Get team
        $team = $this->teams->find($teamId);

        $this->validate($request,[

            'email' => ['required','email']
        ]);

        $user = auth()->user();
        //Check if user owns the team

        if(! $user->isOwnerOfTeam($team))
        {
            return response()->json(['email' => 'You have no right to invite'],401);
        }
        //Check if email has pending invitation
        if($team->hasPendingInvite($request->email))
        {
            return response()->json(['email' => 'Email already has a pending invitation'],422);
        }

        //Get the recipient by email

        $recipient = $this->users->findByEmail($request->email);

        //If the recipient does not exist ,send an invitation to join the team

        if(! $recipient)
        {

            $this->createInvitation(false,$team,$request->email);
            return response()->json(['message'=> 'invitation sent to user'],200);
        }

        //Check if the team already has the user
        if($team->hasUser($recipient))
        {
            return response()->json(['message'=> 'This user seems to be a team member'],422);
        }

        //Send invitation to the user

        $this->createInvitation(true,$team,$request->email);
        return response()->json(['message'=> 'invitation sent to user'],200);


    }
    public function resend($id)
    {

        $invitation = $this->invitations->find($id);
        $recipient = $this->users->findByEmail($invitation->recipient_email);

        Mail::to($invitation->recipient_email)->send(new sendInvitationToJoinTeam($invitation,!is_null($recipient)));

        return response()->json(['message' => 'invitation resent successful']);
    }
    public function respond(Request $request, $id)
    {

        $this->validate($request , [

            'token' => ['required'],
            'decision' => ['required']
        ]);

        $token = $request->token;
        $decision = $request->decision;

        $invitation = $this->invitations->find($id);

        //Check if the invitation belongs to this user

        if($invitation->recipient_email !== auth()->user()->email)
        {
            return response()->json(['message' => 'this invitation doesnt belongs to you'],401);
        }

        //Check if the token is a valid token

        if($invitation->token !== $token)
        {
            return response()->json(['message' => 'Invalid token'],401);
        }

        //Check if accepted

        if( $decision !== 'deny')
        {
            $this->invitations->addUserToTeam($invitation->team,auth()->id());
            // auth()->user()->teams()->attach($invitation->team->id);
        }

        $invitation->delete();

        return response()->json(['message' => 'successful'],200);


    }

    public function destroy($id)
    {

        $invitation = $this->invitations->find($id);

        $this->authorize('delete',$invitation);

        $invitation->delete();

        return response()->json(['message' => 'successful'])
    }

    protected function createInvitation(bool $user_exist, Team $team,string $email)
    {

        $invitation = $this->invitations->create([

            'team_id' => $team->id,
            'sender_id' => auth()->id(),
            'recipient_email' => $email,
            'token' => md5(uniqid(microtime()))
        ]);

        Mail::to($email)->send(new sendInvitationToJoinTeam($invitation,$user_exist));


    }


}
