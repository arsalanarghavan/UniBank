<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Experience\StoreExperienceRequest;
use App\Http\Requests\Experience\UpdateExperienceRequest;
use App\Http\Resources\ExperienceAttachmentResource;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use App\Models\ExperienceAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExperienceController extends Controller
{
    private array $with = ['field', 'major', 'course', 'professor', 'university', 'faculty', 'degreeLevel', 'attachments'];

    public function index(Request $request): JsonResponse
    {
        $query = Experience::query()->with($this->with);

        if ($request->user()->isAdmin() && $request->boolean('all')) {
            if ($status = $request->string('status')->toString()) {
                $query->where('status', $status);
            }
        } else {
            $query->where('user_id', $request->user()->id);
        }

        $items = $query->latest()->paginate($request->integer('per_page', 15));

        return ExperienceResource::collection($items)->additional([
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ])->response();
    }

    public function store(StoreExperienceRequest $request): JsonResponse
    {
        $experience = Experience::query()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => Experience::STATUS_PENDING,
        ]);

        $experience->load($this->with);

        return response()->json([
            'message' => __('experiences.created'),
            'data' => new ExperienceResource($experience),
        ], 201);
    }

    public function show(Request $request, Experience $experience): JsonResponse
    {
        $this->authorizeView($request, $experience);
        $experience->load([...$this->with, 'user']);

        return response()->json(['data' => new ExperienceResource($experience)]);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): JsonResponse
    {
        if ($experience->user_id !== $request->user()->id) {
            abort(403, __('auth.forbidden'));
        }

        if ($experience->status !== Experience::STATUS_REJECTED && ! $request->user()->isAdmin()) {
            abort(422, __('experiences.only_rejected_editable'));
        }

        $experience->update([
            ...$request->validated(),
            'status' => Experience::STATUS_PENDING,
            'rejection_reason' => null,
        ]);

        $experience->load($this->with);

        return response()->json([
            'message' => __('experiences.updated'),
            'data' => new ExperienceResource($experience),
        ]);
    }

    public function destroy(Request $request, Experience $experience): JsonResponse
    {
        if ($experience->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, __('auth.forbidden'));
        }

        $experience->delete();

        return response()->json(['message' => __('experiences.deleted')]);
    }

    public function uploadAttachment(Request $request, Experience $experience): JsonResponse
    {
        if ($experience->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, __('auth.forbidden'));
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,zip,rar,txt'],
        ]);

        $file = $request->file('file');
        $path = $file->store("experiences/{$experience->id}", 'public');
        $attachment = $experience->attachments()->create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        return response()->json([
            'message' => __('experiences.attachment_uploaded'),
            'data' => new ExperienceAttachmentResource($attachment),
        ], 201);
    }

    public function destroyAttachment(Request $request, Experience $experience, ExperienceAttachment $attachment): JsonResponse
    {
        abort_unless($attachment->experience_id === $experience->id, 404);
        if ($experience->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, __('auth.forbidden'));
        }

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => __('experiences.attachment_deleted')]);
    }

    private function authorizeView(Request $request, Experience $experience): void
    {
        if ($experience->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403, __('auth.forbidden'));
        }
    }
}
