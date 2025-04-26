<?php

namespace App\Http\Controllers;

use App\Settings\TermsSettings;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function index(Request $request, string $term)
    {
        $settings = app(TermsSettings::class);

        switch ($term) {
            case 'tos':
                $title = __('Terms of Service');
                $term = 'terms_of_service';
                break;
            case 'privacy':
                $title = __('Privacy Policy');
                $term = 'privacy_policy';
                break;
            case 'imprint':
                $title = __('Imprint');
                $term = 'imprint';
                break;
            default:
                abort(404);
        }

        $content = $settings->$term;

        return view('terms.index', [
            'title' => $title,
            'content' => $content,
        ]);
    }
}
