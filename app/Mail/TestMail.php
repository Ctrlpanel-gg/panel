<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $template = ['subject' => '', 'body' => ''];
    protected array $variables;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $variables)
    {
        $this->template = $template;
        $this->variables = $variables;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $subject = $this->replaceVariables($this->template['subject'], $this->variables);
        $body = $this->replaceVariables($this->template['body'], $this->variables);

        return $this->markdown('mail.test')
            ->subject($subject)
            ->with('body', $body);
    }

    protected function replaceVariables($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{" . $key . "}", $value, $content);
        }
        return $content;
    }
}
