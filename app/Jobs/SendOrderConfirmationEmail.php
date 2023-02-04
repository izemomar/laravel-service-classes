<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\OrderConfirmation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected float $totalCost;
    protected User $user;

    public function __construct(User $user, float $totalCost)
    {
        $this->totalCost = $totalCost;
        $this->user = $user;
    }

    public function handle()
    {
        // you start sending the emails by uncommenting the following line
        //Mail::to($this->user->email)->send(new OrderConfirmation($this->totalCost));
    }
}
