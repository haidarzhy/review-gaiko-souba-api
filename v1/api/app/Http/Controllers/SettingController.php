<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Seo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Setting::first();
        $seos = Seo::first();
        return response()->json([
            'setting' => $settings,
            'seo' => $seos
        ]);
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
        //
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

    public function formatSizeUnits($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;
    
        while ($bytes >= 1024 && $index < 4) {
            $bytes /= 1024;
            $index++;
        }
    
        return round($bytes, 2) . ' ' . $units[$index];
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
        $data = $request->all();
        if($id == 'setting') {
            $updateData = [
                'site_name' => isset($data['site_name']) ? $data['site_name']:null,
                'description' => isset($data['description']) ? $data['description']:null,
                'keywords' => isset($data['keywords']) ? $data['keywords']:null,
                'email' => isset($data['email']) ? $data['email']:null,
                'footer_text' => isset($data['footer_text']) ? $data['footer_text']:null,
            ];

            // upload file if has file
            $currentDate = Carbon::now()->format('Ymd');
            if($request->hasFile('site_logo')) {

                $directory = public_path('/setting/logo');
                $files = glob($directory . '/*');
                foreach ($files as $file) {
                    unlink($file); // Delete the image file
                }

                $file = $request->file('site_logo');
                $path = 'setting/logo';
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                }
                $fileExtension = $file->getClientOriginalExtension();
                $newFileName = $currentDate .'-'.uniqid().'.' . $fileExtension;
                $storedPath = Storage::disk('public')->putFileAs($path, $file, $newFileName);
                $updateData['site_logo'] = 'public/'.$storedPath;
            } 
            
            if($request->hasFile('icon')) {

                $directory = public_path('/setting/icon');
                $files = glob($directory . '/*');
                foreach ($files as $file) {
                    unlink($file); // Delete the image file
                }

                $file = $request->file('icon');
                $path = 'setting/icon';
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                }

                $fileExtension = $file->getClientOriginalExtension();
                $newFileName = $currentDate .'-'.uniqid().'.' . $fileExtension;
                $storedPath = Storage::disk('public')->putFileAs($path, $file, $newFileName);
                $updateData['icon'] = 'public/'.$storedPath;
            } 

            // get site size and cache size
            $currentPath = App::basePath();
            $cachePath = storage_path('framework/cache');

            if (File::exists($currentPath)) {
                $finder = new Finder();
                $finder->files()->in($currentPath);

                $totalSize = 0;

                foreach ($finder as $file) {
                    $totalSize += $file->getSize();
                }
                $humanReadableSize = $this->formatSizeUnits($totalSize);
                $updateData['site_size'] = $humanReadableSize;
            }
            if (File::exists($cachePath)) {
                $finder = new Finder();
                $finder->files()->in($cachePath);

                $totalSize = 0;

                foreach ($finder as $file) {
                    $totalSize += $file->getSize();
                }
                $humanReadableSize = $this->formatSizeUnits($totalSize);
                $updateData['cache_size'] = $humanReadableSize;
            }

            $res = Setting::updateOrCreate(['id' => 1], $updateData);
            if($res) {
                return response()->json($updateData);
            } else {
                return response()->json(0);
            }

        } else if($id == 'seo') {
            $updateData = [
                'og_type' => isset($data['type']) ? strtolower($data['type']):'',
                'og_locale' => isset($data['locale']) ? $data['locale']:null,
                'og_title' => isset($data['title']) ? $data['title']:null,
                'og_description' => isset($data['description']) ? $data['description']:null,
                'og_url' => isset($data['url']) ? $data['url']:null,
                'og_image_width' => isset($data['image_width']) ? $data['image_width']: '1200',
                'og_image_height' => isset($data['image_height']) ? $data['image_height']: '630'
            ];

            // upload file if has file
            $currentDate = Carbon::now()->format('Ymd');
            if($request->hasFile('image')) {

                $directory = public_path('/setting/seo');
                $files = glob($directory . '/*');
                foreach ($files as $file) {
                    unlink($file); // Delete the image file
                }

                $file = $request->file('image');
                $path = 'setting/seo';
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                }
                $fileExtension = $file->getClientOriginalExtension();
                $newFileName = $currentDate .'-'.uniqid().'.' . $fileExtension;
                $storedPath = Storage::disk('public')->putFileAs($path, $file, $newFileName);
                $updateData['og_image'] = 'public/'.$storedPath;
            } 

            $res = Seo::updateOrCreate(['id' => 1], $updateData);
            if($res) {
                return response()->json(1);
            } else {
                return response()->json(0);
            }

        }
        return response()->json(0);
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
