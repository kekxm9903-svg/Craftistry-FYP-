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
            'ic_image'     => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'selfie_image' => 'required|string',
        ]);

        $user = Auth::user();

        // ── Save IC image ──────────────────────────────────────────────────
        $icFile     = $request->file('ic_image');
        $icPath     = $icFile->store('kyc/ic', 'local');
        $icFullPath = Storage::disk('local')->path($icPath);

        // ── Decode and save selfie ─────────────────────────────────────────
        $selfieData     = $request->input('selfie_image');
        $selfieData     = preg_replace('/^data:image\/\w+;base64,/', '', $selfieData);
        $selfieBytes    = base64_decode($selfieData);
        $selfiePath     = 'kyc/selfie/' . $user->id . '_' . time() . '.jpg';
        Storage::disk('local')->put($selfiePath, $selfieBytes);
        $selfieFullPath = Storage::disk('local')->path($selfiePath);

        // ── Run DeepFace comparison ────────────────────────────────────────
        $result = $this->compareFaces($icFullPath, $selfieFullPath);

        Log::debug('KYC DeepFace result', $result);

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

    // ── Call Python DeepFace script ───────────────────────────────────────────

    private function compareFaces(string $icPath, string $selfiePath): array
    {
        // Path to the Python script — place it in project root
        $scriptPath = base_path('compare_faces.py');

        if (!file_exists($scriptPath)) {
            return ['success' => false, 'error' => 'KYC script not found. Please contact support.'];
        }

        // Escape paths for shell
        $icEscaped      = escapeshellarg($icPath);
        $selfieEscaped  = escapeshellarg($selfiePath);
        $scriptEscaped  = escapeshellarg($scriptPath);

        // Run Python script and capture output
        $command = "\"C:\\laragon\\bin\\python\\python-3.10\\python.exe\" {$scriptEscaped} {$icEscaped} {$selfieEscaped} 2>&1";
        $output  = shell_exec($command);

        Log::debug('KYC Python output', ['output' => $output]);

        if (empty($output)) {
            return ['success' => false, 'error' => 'Face verification service unavailable. Please try again.'];
        }

        // Extract JSON from output (ignore any pip/warning lines before it)
        $lines = explode("\n", trim($output));
        $json  = null;
        foreach (array_reverse($lines) as $line) {
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