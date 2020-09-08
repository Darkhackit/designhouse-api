<?php

namespace App\Http\Controllers\Design;

use App\Models\Design;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\Criteria\EagerLoad;
use App\Repositories\Eloquent\Criteria\ForUser;
use App\Repositories\Eloquent\Criteria\IsLive;
use App\Repositories\Eloquent\Criteria\LatestFirst;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{

    protected $designs;

    public function __construct(IDesign  $designs)
    {
        $this->designs = $designs;

    }

    public function index() {

        $designs = $this->designs->withCriteria([
            // new LatestFirst(),
            // new IsLive(),
            // new ForUser(1)
            new EagerLoad(['user','comments'])
        ])->all();

        return DesignResource::collection($designs);
    }

    public function findDesign($id)
    {
        $design = $this->designs->find($id);

        return new DesignResource($design);
    }

    public function update(Request $request, $id)
    {
        // $design = Design::findOrFail($id);
        $design = $this->designs->find($id);

        $this->authorize('update',$design);

        $this->validate($request , [

            'title' => ['required','unique:designs,title,'.$id],
            'description' => ['required','string','min:20','max:140'],
            'tags' => ['required','array'],
            'team' => ['required_if:assign_to_team,true']
        ]);





        $this->designs->update($id,[

            'team_id' => $request->team,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => !$design->upload_successful ? false : $request->is_live,

        ]);

        $this->designs->applyTags($id,$request->tags);


        return new DesignResource($design);

        // return response()->json($design,200);

    }

    public function destroy($id)
    {

        $design = $this->designs->find($id);

        $this->authorize('delete',$design);



        //Deleting files associated with the record

        foreach(['thumbnail','large','original'] as $size)
        {
            if(Storage::disk($design->disk)->exists("uploads/designs/{$size}/".$design->image))
            {
                Storage::disk($design->disk)->delete("uploads/designs/{$size}/".$design->image);
            }
        }

        $this->designs->delete($id);

        return response()->json(['message' => 'record deleted']);
    }

    public function like($id)
    {
        $this->designs->like($id);

        return response()->json(['message' => 'successful']);
    }

    public function checkIfUserHasLiked($designId)
    {

        $isLikes = $this->designs->isLikedByUser($designId);

        return response()->json(['liked' => $isLikes]);
    }

    public function search(Request $request)
    {

        $design = $this->designs->search($request);

        return DesignResource::collection($design);
    }

    public function findBySlug($slug)
    {

        $design = $this->designs->withCriteria(new IsLive())->findWhereFirst('slug',$slug);

        return new DesignResource($design);
    }

    public function getForTeam($teamId)
    {
        $designs = $this->designs->withCriteria(new IsLive())->findWhere('team_id',$teamId);

        return  DesignResource::collection($designs);
    }

    public function getForUser($userId)
    {

        $designs = $this->designs->withCriteria(new IsLive())->findWhere('user_id',$userId);

        return  DesignResource::collection($designs);
    }

    public function userOwnsDesign($id)
    {
        $design = $this->designs->withCriteria([new ForUser(auth()->id())])->findWhereFirst('id',$id);

        return new DesignResource($design);
    }
}
