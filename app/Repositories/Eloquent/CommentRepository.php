<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Contracts\IComment as ContractsIComment;
use App\Repositories\Contracts\IDesign;
class CommentRepository extends BaseRepository implements ContractsIComment
{

    public function model()
    {

        return Comment::class;
    }
}


