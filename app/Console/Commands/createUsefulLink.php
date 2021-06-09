<?php

namespace App\Console\Commands;

use App\Models\UsefulLink;
use Illuminate\Console\Command;

class createUsefulLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:usefullink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a useful link that user can see on the Dashboard';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->alert('You can add Icons to your useful links. Go to https://fontawesome.com/v5.15/icons?d=gallery&p=2 and copy an Icon name.');

        $icon = $this->ask('Specify the class(es) of the font-awesome icon you want to use as the icon. (e.x: ad, fa fa-briefcase, fab fa-discord)', '');
        $title = $this->ask('What do you want the title to be of this message?', 'Default Useful Link Title');
        $message= $this->ask('Now please type the message you want to show. Tip: You can use HTML to format!');
        $link = $this->ask('Please fill in a valid Link. Users can click the title to go to the link', 'https://bitsec.dev');

        $usefulLink = UsefulLink::create([
            'icon' => $icon,
            'title' => $title,
            'link' => $link,
            'message' => $message
        ]);

        $this->alert('Command ran successful inserted useful link into database');

        $this->table(['Icon', 'Title', 'Link', 'Message'], [
            [$icon, $title, $link, $message]
        ]);

        return 1;
    }
}
