<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppAnnouncementResource;
use App\Jobs\SendAppAnnouncementPush;
use App\Models\AppAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class AppAnnouncementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeAdmin($request);

        return AppAnnouncementResource::collection(
            AppAnnouncement::query()->latest()->paginate(50),
        );
    }

    public function store(Request $request): AppAnnouncementResource
    {
        $this->authorizeAdmin($request);
        $data = $request->validate($this->rules());
        $announcement = new AppAnnouncement($this->attributes($request, $data));
        $announcement->created_by = $request->user()->id;
        $announcement->updated_by = $request->user()->id;
        $announcement->save();
        $this->dispatchPushIfRequested($request, $announcement);

        system_log('create_app_announcement', 'Створено оголошення для застосунку: '.$announcement->title);

        return new AppAnnouncementResource($announcement);
    }

    public function update(Request $request, AppAnnouncement $announcement): AppAnnouncementResource
    {
        $this->authorizeAdmin($request);
        $data = $request->validate($this->rules());
        $announcement->fill($this->attributes($request, $data, $announcement));
        $announcement->updated_by = $request->user()->id;
        $announcement->save();
        $this->dispatchPushIfRequested($request, $announcement);

        system_log('update_app_announcement', 'Оновлено оголошення для застосунку: '.$announcement->title);

        return new AppAnnouncementResource($announcement->refresh());
    }

    public function destroy(Request $request, AppAnnouncement $announcement): JsonResponse
    {
        $this->authorizeAdmin($request);
        $title = $announcement->title;
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        $announcement->delete();
        system_log('delete_app_announcement', 'Видалено оголошення для застосунку: '.$title);

        return response()->json(['message' => 'Оголошення видалено.']);
    }

    private function attributes(
        Request $request,
        array $data,
        ?AppAnnouncement $announcement = null,
    ): array {
        $attributes = Arr::except($data, ['image', 'remove_image', 'send_push']);

        if ($request->boolean('remove_image') && $announcement?->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
            $attributes['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($announcement?->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $attributes['image_path'] = $request->file('image')->store('app-announcements', 'public');
        }

        if ($request->boolean('send_push') && ! $announcement?->push_requested_at) {
            $attributes['push_requested_at'] = now();
        }

        return $attributes;
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:8192'],
            'remove_image' => ['nullable', 'boolean'],
            'link_url' => ['nullable', 'url:http,https', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:80'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['required', 'date', 'after:now'],
            'is_active' => ['required', 'boolean'],
            'send_push' => ['nullable', 'boolean'],
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403, 'Керування оголошеннями доступне лише адміністраторам.');
    }

    private function dispatchPushIfRequested(Request $request, AppAnnouncement $announcement): void
    {
        if ($request->boolean('send_push') && $announcement->push_requested_at && ! $announcement->push_sent_at) {
            SendAppAnnouncementPush::dispatch($announcement->id)->afterCommit();
        }
    }
}
