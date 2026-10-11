<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(): Response
    {
        return Seo::page('Contact', [], 'Contact', 'Write to QuickSolve about a tool, a download, or billing. Messages are stored and emailed to the configured inbox.', '/contact');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $message = ContactMessage::query()->create($request->validated());

        Mail::to(config('mail.from.address'))->queue(new ContactMessageReceived($message));

        return back()->with('success', 'Thanks. Your message is saved and a copy is queued for the inbox.');
    }
}
