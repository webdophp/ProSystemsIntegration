<?php

namespace webdophp\ProSystemsIntegration\Mall;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProSystemsJobFailed extends Mailable
{
    use Queueable, SerializesModels;

    public string $errorMessage;
    public string $trace;

    public function __construct(string $errorMessage, string $trace)
    {
        $this->errorMessage = $errorMessage;
        $this->trace = $trace;
    }

    public function build(): self
    {
        return $this->subject(config('pro-systems-integration.mail_subject'))
            ->view('pro-systems-integration::failed')
            ->with([
                'errorMessage' => $this->errorMessage,
                'trace' => $this->trace,
            ]);
    }
}
