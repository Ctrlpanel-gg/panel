<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetSettingCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'settings:get {class : Settings Class (Example: GeneralSettings)} {key} {--sameline : Outputs the result without newline, useful for implementing in scripts.}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Gets value of a setting key and decrypts it if needed.';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {

    $class = $this->argument('class');
    $key = $this->argument('key');
    $sameline = $this->option('sameline');

    try {
      $settings_class = "App\\Settings\\$class";
      $settings = new $settings_class();

      $this->output->write($settings->$key, !$sameline);

      return Command::SUCCESS;
    } catch (\Throwable $th) {
      $this->error('Error: ' . $th->getMessage());
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }
}
