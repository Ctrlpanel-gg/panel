<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    public function updateIcons(Request $request){

        $request->validate([
            'favicon' => 'required',
            'icon' => 'required',
        ]);

        //store favicon
        $favicon = $request->input('favicon');
        $favicon = json_decode($favicon);
        $favicon = explode(",",$favicon->output->image)[1];
        Storage::disk('public')->put('favicon.ico' , base64_decode($favicon));

        //store dashboard icon
        $icon = $request->input('icon');
        $icon = json_decode($icon);
        $icon = explode(",",$icon->output->image)[1];

        Storage::disk('public')->put('icon.png' , base64_decode($icon));

        return redirect()->route('admin.settings.index')->with('success', 'Icons updated!');
    }

}
