<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Login2FAController extends Controller
{
    private $secret;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(Request $request, User $user)
    {
        $google2fa = app(Google2FA::class);

        $valid = $google2fa->verifyKey('6TZBNEJT5I4VKTHN', $request->input('one_time_password'));
        logger()->info('2FA Valid: ' . $valid);
        if ($valid) {
            // Authentication passed...
            logger()->info('2FA Passed: ' . $request->input('one_time_password'));
            return redirect()->route('home');
        }
        else {
            //throw error
            logger()->info('2FA Failed: ' . $request->input('one_time_password'));
            return redirect()->back()->withErrors([
                'one_time_password' => 'The one time password is invalid.'
            ]);
        }
    }

    // Create a new secret and display the QR code
    public function Attempt2FA()
    {
        $google2fa = app(Google2FA::class);
        $this->secret = $google2fa->generateSecretKey();

        logger()->info('2FA Secret: ' . $this->secret);
        $g2faUrl = $google2fa->getQRCodeUrl(
            'pragmarx',
            'google2fa@pragmarx.com',
            '6TZBNEJT5I4VKTHN'
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            )
        );

        $qrcode_image = base64_encode($writer->writeString($g2faUrl));

        return view('auth.2fa-secret')->with([
            'qrcode_image' => $qrcode_image,
            'secret' => '6TZBNEJT5I4VKTHN'
        ]);
    }
}
