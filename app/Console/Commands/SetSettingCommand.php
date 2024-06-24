<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class SetSettingCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'settings:set {class : Settings Class (Example: GeneralSettings)} {key : Unique setting key} {value : Value to set}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Set value of a setting key.';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {

    $class = $this->argument('class');
    $key = $this->argument('key');
    $value = $this->argument('value');

    try {
      $settings_class = "App\\Settings\\$class";
      $settings = new $settings_class();

      $settings->$key = $value;

      $settings->save();

      $this->info("Successfully updated '$key'.");
    } catch (\Throwable $th) {
      $this->error('Error: ' . $th->getMessage());
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }
}
