<?php

namespace App\Services\Platform;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class OpportunitySubmission
{
    public function publish(Request $request, array $data, string $key, CompanyMarketplaceService $service): array
    {
        $media = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $mime = $file->getMimeType();
                $path = $file->storeAs('opportunities/company', (string) Str::uuid().'.'.$file->guessExtension(), 'public');
                if (! $path) {
                    throw new \RuntimeException('Unable to store opportunity media.');
                }
                $media[] = ['path' => $path, 'type' => str_starts_with($mime, 'video/') ? 'video' : 'image', 'mime_type' => $mime, 'size' => $file->getSize(), 'original_name' => $file->getClientOriginalName(), 'sha256' => hash_file('sha256', $file->getRealPath())];
            }
            $result = $service->publish($request->user(), $data, $key, $media);
            if (! $result['created']) {
                foreach ($media as $file) {
                    Storage::disk('public')->delete($file['path']);
                }
            }
        } catch (\Throwable $e) {
            foreach ($media as $file) {
                Storage::disk('public')->delete($file['path']);
            }throw $e;
        }

        return $result;
    }
}
