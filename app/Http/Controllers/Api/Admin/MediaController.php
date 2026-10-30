<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $collection = $request->string('collection')->toString();
        $media = Media::query()
            ->with('uploader:id,name')
            ->when($collection !== '', fn ($q) => $q->where('collection', $collection))
            ->latest()
            ->paginate(30);

        return response()->json(['media' => $media]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'collection' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = Media::create([
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'collection' => $data['collection'] ?? 'general',
        ]);

        return response()->json(['media' => $media], 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['message' => 'Media dihapus.']);
    }
}
