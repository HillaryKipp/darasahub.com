<?php

namespace App\Services;

use App\Models\LinkCheck;
use App\Models\Paper;
use Illuminate\Support\Facades\Http;

class LinkCheckerService
{
    private const LOGIN_WALL_PATTERNS = [
        'accounts.google.com',
        'servicelogin',
        'sign in',
        'request access',
        'you need access',
        'login',
        '404 not found',
        'file not found',
    ];

    public function checkPaper(Paper $paper, ?int $checkedBy = null): LinkCheck
    {
        $url = (string) $paper->file_url;
        $start = microtime(true);

        $httpStatus = null;
        $contentType = null;
        $finalUrl = $url;
        $result = LinkCheck::RESULT_ERROR;
        $notes = null;

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'DarasaHubLinkChecker/1.0'])
                ->withOptions(['allow_redirects' => ['track_redirects' => true]])
                ->get($url);

            $httpStatus = $response->status();
            $contentType = strtolower((string) $response->header('Content-Type'));
            $redirectHistory = $response->header('X-Guzzle-Redirect-History');
            if ($redirectHistory) {
                $parts = array_map('trim', explode(',', $redirectHistory));
                $finalUrl = end($parts) ?: $url;
            }

            if ($httpStatus >= 400) {
                $result = LinkCheck::RESULT_BROKEN;
                $notes = "HTTP {$httpStatus} returned.";
            } elseif ($httpStatus >= 200 && $httpStatus < 300) {
                $isHtml = str_contains($contentType, 'text/html');
                $bodySnippet = strtolower(substr($response->body(), 0, 4096));
                $finalLower = strtolower($finalUrl);

                $hitLoginWall = false;
                if ($isHtml) {
                    foreach (self::LOGIN_WALL_PATTERNS as $pattern) {
                        if (str_contains($finalLower, $pattern) || str_contains($bodySnippet, $pattern)) {
                            $hitLoginWall = true;
                            break;
                        }
                    }
                }

                if ($hitLoginWall) {
                    $result = LinkCheck::RESULT_LOGIN_WALL;
                    $notes = 'HTTP 200 returned an HTML login/access-denied page rather than a downloadable document.';
                } elseif ($isHtml) {
                    $result = LinkCheck::RESULT_REDIRECT;
                    $notes = 'HTTP 200 returned HTML (requires manual verification to confirm downloadable file).';
                } else {
                    $result = LinkCheck::RESULT_OK;
                    $notes = 'Document content-type verified ('.$contentType.').';
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $result = LinkCheck::RESULT_TIMEOUT;
            $notes = substr($e->getMessage(), 0, 500);
        } catch (\Throwable $e) {
            $result = LinkCheck::RESULT_ERROR;
            $notes = substr($e->getMessage(), 0, 500);
        }

        $elapsedMs = (int) round((microtime(true) - $start) * 1000);

        $check = LinkCheck::create([
            'paper_id' => $paper->id,
            'url' => $url,
            'http_status' => $httpStatus,
            'content_type' => $contentType ? substr($contentType, 0, 120) : null,
            'final_url' => substr($finalUrl, 0, 1000),
            'result' => $result,
            'response_time_ms' => $elapsedMs,
            'notes' => $notes,
            'checked_by' => $checkedBy,
            'checked_at' => now(),
        ]);

        $updates = ['last_checked_at' => now()];
        if (in_array($result, [LinkCheck::RESULT_BROKEN, LinkCheck::RESULT_LOGIN_WALL], true)) {
            $updates['verification_status'] = Paper::VERIFICATION_BROKEN;
        }
        $paper->forceFill($updates)->saveQuietly();

        return $check;
    }
}
