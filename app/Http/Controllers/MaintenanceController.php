<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MaintenanceController extends Controller
{
    /**
     * Fixed allowlist of commands permitted via the no-SSH maintenance route (Section 17).
     *
     * @var array<string, array{command: string, params: array<string, mixed>}>
     */
    private const ALLOWED_COMMANDS = [
        'migrate' => ['command' => 'migrate', 'params' => ['--force' => true]],
        'optimize' => ['command' => 'optimize', 'params' => []],
        'optimize-clear' => ['command' => 'optimize:clear', 'params' => []],
        'cache-clear' => ['command' => 'cache:clear', 'params' => []],
        'responsecache-clear' => ['command' => 'responsecache:clear', 'params' => []],
        'sitemap-generate' => ['command' => 'sitemap:generate', 'params' => []],
        'storage-link' => ['command' => 'storage:link', 'params' => []],
    ];

    public function run(Request $request, string $action): JsonResponse
    {
        if (! config('maintenance.enabled', false)) {
            abort(404);
        }

        // Verify IP allowlist
        $allowedIps = config('maintenance.allowed_ips', []);
        if ($allowedIps === [] || ! in_array($request->ip(), $allowedIps, true)) {
            AuditLog::record('maintenance.denied_ip', null, null, ['action' => $action, 'ip' => $request->ip()]);

            return response()->json(['error' => 'Forbidden (IP not allowlisted).'], 403);
        }

        // Verify expiration timestamp
        $expiresAt = config('maintenance.expires_at');
        if (empty($expiresAt) || Carbon::parse($expiresAt)->isPast()) {
            AuditLog::record('maintenance.denied_expired', null, null, ['action' => $action]);

            return response()->json(['error' => 'Forbidden (maintenance token expired or unset).'], 403);
        }

        // Verify long secret token (>= 32 chars)
        $expectedToken = (string) config('maintenance.token', '');
        $providedToken = (string) ($request->header('X-Maintenance-Token') ?: $request->query('token', ''));

        if (strlen($expectedToken) < 32 || ! hash_equals($expectedToken, $providedToken)) {
            AuditLog::record('maintenance.denied_token', null, null, ['action' => $action]);

            return response()->json(['error' => 'Forbidden (invalid token).'], 403);
        }

        if (! isset(self::ALLOWED_COMMANDS[$action])) {
            return response()->json(['error' => 'Command not allowlisted.'], 422);
        }

        $spec = self::ALLOWED_COMMANDS[$action];
        $exitCode = Artisan::call($spec['command'], $spec['params']);
        $output = trim(Artisan::output());

        AuditLog::record('maintenance.executed', null, null, [
            'action' => $action,
            'command' => $spec['command'],
            'exit_code' => $exitCode,
        ], notes: substr($output, 0, 1000));

        return response()->json([
            'action' => $action,
            'exit_code' => $exitCode,
            'output' => $output,
        ]);
    }
}
