<?php

namespace App\Http\Controllers;

use App\Facades\Currency;
use App\Models\User;
use App\Settings\UserSettings;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Settings\DiscordSettings;
use App\Settings\ReferralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    /** Display a listing of the resource. */
    public function index(UserSettings $user_settings, DiscordSettings $discord_settings, ReferralSettings $referral_settings)
    {

        return view('profile.index')->with([
            'user' => Auth::user(),
            // raw numeric value for logical checks; formatting occurs in blades when needed
            'credits_reward_after_verify_discord' => $user_settings->credits_reward_after_verify_discord,
            'force_email_verification' => $user_settings->force_email_verification,
            'force_discord_verification' => $user_settings->force_discord_verification,
            'discord_link_enabled' => !empty($discord_settings->client_id) && !empty($discord_settings->client_secret),
            'referral_enabled' => $referral_settings->enabled
        ]);
    }

    public function selfDestroyUser()
    {
        $user = Auth::user();
        if ($user->hasRole("Admin")) return back()->with("error", "You cannot delete yourself as an admin!");

        $user->delete();

        return redirect('/login')->with('success', __('Account permanently deleted!'));
    }

    /** Update the specified resource in storage.
     * @param  Request  $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        //prevent other users from editing a user
        if ($id != Auth::user()->id) {
            abort(403);
        }
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|min:4|max:30|alpha_num|unique:users,name,' . $id . ',id',
            'email' => 'required|email|max:64|unique:users,email,' . $id . ',id',
            'avatar' => 'nullable',
            'current_password' => [
                'required_with:new_password',
                function ($attribute, $value, $fail) use ($user) {
                    if (! empty($value) && ! Hash::check($value, $user->password)) {
                        $fail('The ' . $attribute . ' is invalid.');
                    }
                },
            ],
            'new_password' => 'nullable|string|min:8',
            'new_password_confirmation' => 'nullable|same:new_password',
        ]);

        $avatarValue = null;

        if (!is_null($validated['avatar'] ?? null)) {
            $avatar = json_decode($validated['avatar']);

            if (! is_object($avatar) || ! isset($avatar->input->size, $avatar->output->image)) {
                throw ValidationException::withMessages([
                    'avatar' => __('The avatar payload is invalid.'),
                ]);
            }

            if ($avatar->input->size > 3000000) {
                throw ValidationException::withMessages([
                    'avatar' => __('The avatar may not be greater than 3 MB.'),
                ]);
            }

            $avatarValue = $avatar->output->image;
        }

        //update name and email on Pterodactyl
        $pterodactylPayload = array_filter([
            'username' => $validated['name'],
            'first_name' => $validated['name'],
            'last_name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['new_password'] ?? null,
        ], static fn ($value) => $value !== null);

        $response = $this->pterodactyl->application->patch('/application/users/' . $user->pterodactyl_id, $pterodactylPayload);

        if ($response->failed()) {
            logger()->warning('Failed to update profile in Pterodactyl.', [
                'user_id' => $user->id,
                'status' => $response->status(),
            ]);

            throw ValidationException::withMessages([
                'pterodactyl_error_message' => __('Failed to update your account on Pterodactyl. Please try again later.'),
            ]);
        }

        //update name and email
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => ! empty($validated['new_password']) ? Hash::make($validated['new_password']) : $user->password,
            'avatar' => $avatarValue,
        ]);

        if ($validated['email'] != Auth::user()->email) {
            $user->reVerifyEmail();
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('profile.index')->with('success', __('Profile updated'));
    }
}
