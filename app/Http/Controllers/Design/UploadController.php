<?php

namespace App\Http\Controllers\Design;

use App\Models\Design;
use App\Jobs\UploadImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{

    public function upload(Request $request)
    {


        $this->validate($request , [

            'file' => ['required'],
            'file.*' => ['mimes:jpeg,png,gif,bmp', 'max:2048'],

        ]);
         // Get image


        // $image = $request->file('image');
        // $image_path = $image->getPathname();

        //Get the original file name and replace any space with underscore

    //    $filename = time()."_". preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));
        //Move image to the temporary location

        // $tmp = $image->storeAs('uploads/original',$filename,'tmp');
        //Creating database record for the design

    //    dd($request->image);


        if($request->hasfile('file'))
        {





           foreach($request->file('file') as $image)
           {
            $name = time().'_'.uniqid().'.'.$image->extension();

            $image->move(public_path().'/files/', $name);

            $data[] = $name;

            $images = implode(",", $data);




           }

           $design = new Design;
           $design->user_id = auth()->id();
           $design->disk = config('site.upload_disk');
           $design->upload_successful = true;
           $design->image = json_encode($images);
           $design->description = $request->description;
           $design->save();

           $design->tag($request->tags);

           return response()->json($design , 200);

        }




        //dispatch a job to handle the image manipulation

        //  $this->dispatch(new UploadImage($design));



    }
}
