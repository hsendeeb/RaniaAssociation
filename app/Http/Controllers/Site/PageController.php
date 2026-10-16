<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('site/About');
    }

    public function contact(): Response
    {
        return Inertia::render('site/Contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Phase 6 (Notifications) will deliver this by email/SMS.
        Log::info('New contact message', $validated);

        return back()->with('success', 'تم استلام رسالتك بنجاح، وسنتواصل معك في أقرب وقت.');
    }
}
