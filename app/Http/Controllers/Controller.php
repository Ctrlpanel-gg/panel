<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use Exception;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $client = null;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        try {
            $this->client = new PterodactylClient($ptero_settings);
        }
        catch (Exception $exception) {
            
        }
    }
}
