<?php

namespace App\Jobs;

use Image;
use File;
use App\Models\Design;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $design;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = $this->design->disk;
        $original_file = storage_path('uploads/original/'.$this->design->image);

        try {
            //Create the large image and save to tmp disk
            Image::make($original_file)
            ->fit(800 , 600 , function ($constraint) {

                $constraint->aspectRatio();
            })
            ->save($large = storage_path('uploads/large/'.$this->design->image));

            //Create the thumbnail image and save to tmp disk
            Image::make($original_file)
            ->fit(250 , 200 , function ($constraint) {

                $constraint->aspectRatio();
            })
            ->save($thumbnail = storage_path('uploads/thumbnail/'.$this->design->image));

            //Store image to permanent disk
            //original image

          if(Storage::disk($disk)
            ->put('/uploads/designs/original/'. $this->design->image , fopen($original_file,'r+'))) {

                File::delete($original_file);

            }
            if(  Storage::disk($disk)
            ->put('/uploads/designs/large/'. $this->design->image , fopen($large,'r+'))) {

                File::delete($large);

            }
            if( Storage::disk($disk)
            ->put('/uploads/designs/thumbnail/'. $this->design->image , fopen($thumbnail,'r+'))) {

                File::delete($thumbnail);

            }

            $this->design->update([

                "upload_successful" => true
            ]);

        } catch (\Exception $e) {

            \Log::error($e->getMessage());
        }
    }
}
