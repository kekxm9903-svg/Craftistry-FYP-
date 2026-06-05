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
        $apiKey    = config('services.facepp.key');
        $apiSecret = config('services.facepp.secret');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-us.faceplusplus.com/facepp/v3/compare');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'api_key'     => $apiKey,
            'api_secret'  => $apiSecret,
            'image_file1' => new \CURLFile($icPath),
            'image_file2' => new \CURLFile($selfiePath),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        Log::debug('KYC Face++ raw response', ['response' => $response]);

        $data = json_decode($response, true);

        if (isset($data['error_message'])) {
            return ['success' => false, 'error' => $data['error_message']];
        }

        if (!isset($data['confidence'])) {
            return ['success' => false, 'error' => 'Face verification failed. Please try again.'];
        }

        return ['success' => true, 'similarity' => $data['confidence']];
    }
}