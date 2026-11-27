<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\FrontendController;
use App\Mail\FormSubmissionMessage;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Public-facing form renderer / submission handler.
 *
 * Reuses the frontend renderer (theme resolution, menus, localizeData) so
 * forms look like the rest of the site.
 */
class FormController extends FrontendController
{
    /**
     * Show a single form.
     */
    public function show(string $slug)
    {
        $form = $this->scoped(Form::query())
            ->where('slug', $slug)
            ->firstOrFail();

        abort_if(!$form->enabled, 404);

        return $this->render('form', [
            'form' => $form,
            'seo' => $this->seo([
                'title' => $form->name,
                'description' => $form->description ?: null,
                'canonical' => url('/forms/' . $form->slug),
            ]),
        ]);
    }

    /**
     * Handle a form submission.
     */
    public function store(Request $request, string $slug)
    {
        $form = $this->scoped(Form::query())
            ->where('slug', $slug)
            ->where('enabled', true)
            ->firstOrFail();

        $service = app('forms.service');
        $validated = $request->validate($service->validationRules($form));

        // Silently accept bots that fill the hidden honeypot field.
        $honeypot = $request->filled('website');

        $data = $service->normalizeSubmission($form, $validated);

        // Spam scoring (honeypot hits are definitive).
        $spamProtection = $form->setting('spam_protection', true);
        $spam = ['score' => 0, 'reason' => null];

        if ($honeypot) {
            $spam = ['score' => 100, 'reason' => 'honeypot'];
        } elseif ($spamProtection) {
            $spam = app(\App\Services\SpamFilter::class)->analyze($service->spamPayload($form, $data));
        }

        $isSpam = $spam['score'] >= 50;

        $site = $this->site();

        try {
            FormSubmission::create([
                'site_id' => $site?->id,
                'form_id' => $form->id,
                'data' => $data,
                'ip_hash' => $request->ip() ? hash('sha256', $request->ip() . '|' . $request->userAgent()) : null,
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'spam_score' => $spam['score'],
                'spam_reason' => $spam['reason'],
                'status' => $isSpam ? 'spam' : 'new',
            ]);
        } catch (\Throwable $e) {
            // Never block the visitor because of bookkeeping.
            report($e);
        }

        if ($honeypot) {
            return back()->with('status', $form->setting('success_message', 'Thanks! Your message has been sent.'));
        }

        $settings = $this->settings();
        $recipient = $form->setting('mail_to') ?: $settings->admin_email ?: config('mail.from.address');

        if (!$isSpam && !$recipient) {
            return back()->withInput()
                ->with('error', 'This site has not configured a form recipient yet.');
        }

        if (!$isSpam && $recipient) {
            try {
                Mail::to($recipient)->send(new FormSubmissionMessage(
                    formName: $form->name,
                    fields: $form->fields ?? [],
                    values: $data,
                    siteName: $settings->site_title ?? $site?->name ?? 'your website',
                ));
            } catch (\Throwable $e) {
                report($e);

                return back()->withInput()
                    ->with('error', 'Sorry, your submission could not be delivered. Please try again later.');
            }
        }

        return back()->with('status', $form->setting('success_message', 'Thanks! Your message has been sent.'));
    }
}
