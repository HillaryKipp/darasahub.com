<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DownloadController extends Controller
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|bingpreview|googleother|ahrefs|semrush|mj12bot|dotbot|petalbot|bytespider|curl|wget|python-requests|httpclient|go-http-client|headlesschrome/i';

    public function download(Request $request, int $paperId): RedirectResponse
    {
        $paper = Paper::query()->verifiedReady()->findOrFail($paperId);

        if (empty($paper->file_url)) {
            abort(404);
        }

        $userAgent = (string) $request->userAgent();
        $isBot = $userAgent === '' || preg_match(self::BOT_PATTERN, $userAgent) === 1;

        if (! $isBot) {
            $cooldownKey = 'download_hit:'.$paper->id.':'.sha1((string) $request->ip());
            if (Cache::add($cooldownKey, true, now()->addMinutes(10))) {
                $paper->increment('download_count');
            }
        }

        return redirect()->away($paper->file_url, 302)
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
