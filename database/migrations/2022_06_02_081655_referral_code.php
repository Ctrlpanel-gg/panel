<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReferralCode extends Migration
{
    
    public function generateCode($userid){
        
            $random = STR::random(8);
            if (User::where('referral_code', '=', $random)->doesntExist()) {
                DB::table("users")
                    ->where("id", "=", $userid)
                    ->update(['referral_code' => $random]);
            }else{
                $this->generateCode($userid);
            }
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->lenght(8)->nullable();
        });

        $existing_user = User::where('referral_code', '')->orWhere('referral_code', NULL)->get();

        foreach ($existing_user as $user) {
            $this->generateCode($user->id);
            }
        }
    


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_code');
        });
    }

}
