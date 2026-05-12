<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if ($user->kyc_status === 'passed') {
            return redirect()->route('dashboard');
        }
        return view('kyc', ['user' => $user]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'ic_image'     => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'selfie_image' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $user = Auth::user();

        // ── Save IC ────────────────────────────────────────────────────────
        $icPath     = 'kyc/ic/' . $user->id . '_' . time() . '.jpg';
        $icFullPath = Storage::disk('local')->path($icPath);
        Storage::disk('local')->makeDirectory('kyc/ic');
        $this->saveAsHighQualityJpeg($request->file('ic_image')->getRealPath(), $icFullPath);

        // ── Save selfie ────────────────────────────────────────────────────
        $selfiePath     = 'kyc/selfie/' . $user->id . '_' . time() . '.jpg';
        $selfieFullPath = Storage::disk('local')->path($selfiePath);
        Storage::disk('local')->makeDirectory('kyc/selfie');
        $this->saveAsHighQualityJpeg($request->file('selfie_image')->getRealPath(), $selfieFullPath);

        // ── Run Face++ comparison ──────────────────────────────────────────
        $result = $this->compareFaces($icFullPath, $selfieFullPath);

        Log::debug('KYC Face++ result', $result);

        if (!$result['success']) {
            Storage::disk('local')->delete([$icPath, $selfiePath]);
            return back()
                ->withErrors(['kyc' => $result['error']])
                ->with('kyc_similarity', 0);
        }

        $similarity = $result['similarity'];
        $passed     = $similarity >= 80.0;

        $user->update([
            'kyc_ic_path'     => $icPath,
            'kyc_selfie_path' => $selfiePath,
            'kyc_status'      => $passed ? 'passed' : 'failed',
            'kyc_similarity'  => $similarity,
            'kyc_verified_at' => $passed ? now() : null,
        ]);

        if ($passed) {
            return redirect()->route('dashboard')
                ->with('success', 'Identity verified successfully! Welcome to Craftistry.');
        }

        return redirect()->route('kyc.show')
            ->with('kyc_failed', true)
            ->with('kyc_similarity', round($similarity, 1));
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function saveAsHighQualityJpeg(string $sourcePath, string $destPath): void
    {
        $mime = mime_content_type($sourcePath);

        $src = match ($mime) {
            'image/png'  => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default      => imagecreatefromjpeg($sourcePath),
        };

        if (!$src) {
            copy($sourcePath, $destPath);
            return;
        }

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif        = @exif_read_data($sourcePath);
            $orientation = $exif['Orientation'] ?? 1;
            $src = match ($orientation) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => $src,
            };
        }

        imagejpeg($src, $destPath, 95);
        imagedestroy($src);
    }

    private function compareFaces(string $icPath, string $selfiePath): array
    {
        $scriptPath = base_path('compare_faces.py');

        if (!file_exists($scriptPath)) {
            return ['success' => false, 'error' => 'KYC script not found. Please contact support.'];
        }

        $command = "\"C:\\laragon\\bin\\python\\python-3.10\\python.exe\" "
                 . escapeshellarg($scriptPath) . ' '
                 . escapeshellarg($icPath)     . ' '
                 . escapeshellarg($selfiePath) . ' 2>&1';

        $output = shell_exec($command);

        Log::debug('KYC Python output', ['output' => $output]);

        if (empty($output)) {
            return ['success' => false, 'error' => 'Face verification service unavailable. Please try again.'];
        }

        $json = null;
        foreach (array_reverse(explode("\n", trim($output))) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '{')) {
                $json = $line;
                break;
            }
        }

        if (!$json) {
            Log::error('KYC: No JSON in Python output', ['output' => $output]);
            return ['success' => false, 'error' => 'Face verification failed. Please try again.'];
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Face verification failed. Please try again.'];
        }

        return $data;
    }
}