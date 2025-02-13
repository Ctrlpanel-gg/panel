<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Qirolab\Theme\Theme;


class LegalController extends Controller
{
    const READ_PERMISSION = "admin.legal.read";
    const WRITE_PERMISSION = "admin.legal.write";
    /**
     * Display
     *
     * @return View
     */
    public function index()
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::WRITE_PERMISSION]);

        $tos = File::get(Theme::path($path = 'views', "default") . '/information/tos-content.blade.php');
        $privacy = File::get(Theme::path($path = 'views', "default") . '/information/privacy-content.blade.php');
        $imprint = File::get(Theme::path($path = 'views', "default") . '/information/imprint-content.blade.php');

        return view('admin.legal.index')->with([
            "tos" => $tos,
            "privacy" => $privacy,
            "imprint" => $imprint,
            ]);
    }

    public function update(Request $request){
        $this->checkPermission(self::WRITE_PERMISSION);

        $tos = $request->tos;
        $privacy = $request->privacy;
        $imprint = $request->imprint;

        File::put(Theme::path($path = 'views', "default") . '/information/tos-content.blade.php', $tos);
        File::put(Theme::path($path = 'views', "default") . '/information/privacy-content.blade.php', $privacy);
        File::put(Theme::path($path = 'views', "default") . '/information/imprint-content.blade.php', $imprint);

        return back()->with("success",__("Legal pages updated"));
    }
}
