<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        return view('profile.index')->with([
            'user' => Auth::user(),
            'credits_reward_after_verify_discord' => Settings::getValueByKey('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD'),
            'force_email_verification' => Settings::getValueByKey('SETTINGS::USER:FORCE_EMAIL_VERIFICATION'),
            'force_discord_verification' => Settings::getValueByKey('SETTINGS::USER:FORCE_DISCORD_VERIFICATION'),
        ]);
    }

    /** Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        //prevent other users from editing a user
        if ($id != Auth::user()->id) dd(401);
        $user = User::findOrFail($id);

        //update password if necessary
        if (!is_null($request->input('new_password'))){

            //validate password request
            $request->validate([
                'current_password' => [
                    'required' ,
                    function ($attribute, $value, $fail) use ($user) {
                        if (!Hash::check($value, $user->password)) {
                            $fail('The '.$attribute.' is invalid.');
                        }
                    },
                ],
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password'
            ]);

            //update password
            $user->update([
                'password' => Hash::make($request->input('new_password')),
            ]);
        }

        //validate request
        $request->validate([
            'name' => 'required|min:4|max:30|alpha_num|unique:users,name,'.$id.',id',
            'email' => 'required|email|max:64|unique:users,email,'.$id.',id',
            'avatar' => 'nullable'
        ]);

        //update avatar
        if(!is_null($request->input('avatar'))){
            $avatar = json_decode($request->input('avatar'));
            if ($avatar->input->size > 3000000) abort(500);

            $user->update([
                'avatar' => $avatar->output->image,
            ]);
        } else {
            $user->update([
                'avatar' => null,
            ]);
        }

        //update name and email
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        return redirect()->route('profile.index')->with('success' , __('Profile updated'));
    }
}
