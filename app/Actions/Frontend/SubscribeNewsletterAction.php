<?php

namespace App\Actions\Frontend;

use App\Models\NewsletterSubscriber;

class SubscribeNewsletterAction
{
    /**
     * Subscribe an email address to the newsletter.
     *
     * @param string $email
     * @param string|null $ipAddress
     * @return array{success: bool, already_subscribed: bool, message: string}
     */
    public function execute(string $email, ?string $ipAddress = null): array
    {
        $normalizedEmail = strtolower(trim($email));

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $normalizedEmail],
            [
                'ip_address' => $ipAddress,
                'status'     => 1,
            ]
        );

        if (!$subscriber->wasRecentlyCreated) {
            return [
                'success'            => true,
                'already_subscribed' => true,
                'message'            => 'আপনি ইতিমধ্যে আমাদের নিউজলেটারে সাবস্ক্রাইব করেছেন।',
            ];
        }

        return [
            'success'            => true,
            'already_subscribed' => false,
            'message'            => 'ধন্যবাদ! আমাদের নিউজলেটারে সফলভাবে সাবস্ক্রাইব করা হয়েছে।',
        ];
    }
}
