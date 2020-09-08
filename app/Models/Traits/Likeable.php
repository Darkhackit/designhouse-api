<?php

namespace App\Models\Traits;

use App\Models\Like;

trait Likeable
{

    public static function bootLikeable()
    {

        static::deleting(function($model){
            $model->removeLikes();
        });
    }

    //Delete Likes when model is deleted

    public function removeLikes()
    {

        if($this->likes()->count())
        {
            $this->likes()->delete();
        }
    }

    public function likes()
    {
        return $this->morphMany(Like::class,'likeable');
    }

    public function like()
    {

        //Check if user is authenticated
        if(! auth()->check())
        {
            return;
        }

        //Checking if user has already like the Model

        if($this->isLikedByUser(auth()->id()))
        {
            return;
        }

        $this->likes()->create([

            'user_id' => auth()->id()
        ]);

    }

    public function unlike()
    {
         //Check if user is authenticated
         if(! auth()->check())
         {
             return;
         }
          //Checking if user has already like the Model

        if(! $this->isLikedByUser(auth()->id()))
        {
            return;
        }

        return $this->likes()->where('user_id',auth()->id())->delete();


    }

    public function isLikedByUser($user_id)
    {

        return (bool)$this->likes()->where('user_id', $user_id)->count();
    }
}
