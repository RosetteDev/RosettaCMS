<?php

namespace App\Services;

/**
 * Akismet-lite spam filter for contact-form submissions.
 *
 * Heuristic scoring (0-100) based on structural signals, not reputation
 * lists. Honeypot hits are applied by the caller (they are definitive).
 */
class SpamFilter
{
    /**
     * Keywords typical of low-quality / spam messages.
     */
    protected const KEYWORDS = [
        'viagra', 'cialis', 'pharmacy', 'buy now', 'cheap pills',
        'casino', 'slots', 'lottery', 'winner',
        'crypto', 'bitcoin', 'investment opportunity', 'guaranteed profit',
        'loan', 'credit card offer', 'no credit check',
        'seo services', 'backlink', 'rank on google', 'buy followers',
        'work from home', 'earn money', 'make money',
        'click here', 'limited time offer', 'act now',
        'free trial', '100% free', '100% legit',
    ];

    /**
     * Analyze a submission.
     *
     * @param  array{name?: ?string, email?: ?string, subject?: ?string, message?: ?string, custom_fields?: array}  $data
     * @return array{score: int, reason: ?string}
     */
    public function analyze(array $data): array
    {
        $text = mb_strtolower(trim(implode(' ', array_filter([
            $data['subject'] ?? null,
            $data['message'] ?? null,
            implode(' ', array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $data['custom_fields'] ?? [])),
        ]))));

        if ($text === '') {
            return ['score' => 0, 'reason' => null];
        }

        $score = 0;
        $reasons = [];

        // URL stuffing: more than 3 links is a strong signal.
        $urlCount = preg_match_all('/https?:\/\/|www\./i', $text);
        if ($urlCount > 3) {
            $score += 40;
            $reasons[] = 'excessive links (' . $urlCount . ')';
        } elseif ($urlCount > 1) {
            $score += 15;
            $reasons[] = 'links in message (' . $urlCount . ')';
        }

        // Keyword hits.
        $keywordHits = [];
        foreach (self::KEYWORDS as $keyword) {
            if (mb_strpos($text, $keyword) !== false) {
                $keywordHits[] = $keyword;
            }
        }
        if ($keywordHits !== []) {
            $score += min(50, count($keywordHits) * 12);
            $reasons[] = 'suspicious keywords (' . implode(', ', array_slice($keywordHits, 0, 3)) . ')';
        }

        // Excessive caps (shouting).
        $letters = preg_replace('/[^A-Za-z]/', '', $text);
        if (strlen($letters) >= 20) {
            $caps = strlen(preg_replace('/[^A-Z]/', '', $letters)) / strlen($letters);
            if ($caps > 0.6) {
                $score += 25;
                $reasons[] = 'excessive capitalization';
            }
        }

        // Very short / empty-ish message.
        $wordCount = str_word_count($text);
        if ($wordCount < 5 && strlen($text) > 0) {
            $score += 20;
            $reasons[] = 'very short message';
        }

        // Excessive punctuation / repeated characters.
        if (preg_match('/(.)\1{10,}/u', $text)) {
            $score += 30;
            $reasons[] = 'repeated characters';
        }

        // Excessive commas / exclamation marks (classic spam punctuation).
        $excess = substr_count($text, '!') + substr_count($text, '?') + substr_count($text, ',');
        if ($excess > 12) {
            $score += 15;
            $reasons[] = 'excessive punctuation';
        }

        $score = min(100, $score);

        return [
            'score' => $score,
            'reason' => $reasons === [] ? null : implode('; ', array_slice($reasons, 0, 3)),
        ];
    }
}
