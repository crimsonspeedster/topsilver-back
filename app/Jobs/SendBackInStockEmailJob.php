<?php
namespace App\Jobs;

use App\Mail\ProductBackInStockMail;
use App\Models\ProductSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendBackInStockEmailJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue;

    public function __construct(
        public int $subscriber_id
    ) {}

    public function handle(): void
    {
        $subscriber = ProductSubscriber::with('product')
            ->find($this->subscriber_id);

        if (!$subscriber) {
            return;
        }

        Mail::to($subscriber->email)
            ->send(new ProductBackInStockMail($subscriber->product));

        $subscriber->delete();
    }
}
