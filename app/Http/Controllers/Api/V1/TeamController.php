<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EmployeeResource;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Employee::query()->with(['position', 'department'])->orderBy('sort')->orderBy('last_name');
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        return EmployeeResource::collection($query->paginate(min(max((int) $request->query('per_page', 50), 1), 100)));
    }

    public function options(Request $request): JsonResponse
    {
        $settings = SiteSetting::query()->first();

        return response()->json(['data' => [
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'team_banner_url' => $settings?->team_banner
                ? $request->getSchemeAndHttpHost().Storage::url($settings->team_banner)
                : null,
        ]]);
    }

    public function store(Request $request): EmployeeResource
    {
        $this->authorizeAdmin($request);
        $data = $request->validate($this->rules());
        $employee = Employee::query()->create($this->attributes($request, $data));
        system_log('create_employee', 'Додано працівника з мобільного застосунку: '.$this->name($employee));

        return new EmployeeResource($employee->load(['position', 'department']));
    }

    public function update(Request $request, Employee $employee): EmployeeResource
    {
        $this->authorizeAdmin($request);
        $data = $request->validate($this->rules());
        $employee->fill($this->attributes($request, $data, $employee))->save();
        system_log('update_employee', 'Оновлено працівника з мобільного застосунку: '.$this->name($employee));

        return new EmployeeResource($employee->refresh()->load(['position', 'department']));
    }

    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeAdmin($request);
        $name = $this->name($employee);
        $employee->delete();
        system_log('delete_employee', 'Видалено працівника з мобільного застосунку: '.$name);

        return response()->json(['message' => 'Працівника видалено.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['employee_ids' => ['required', 'array'], 'employee_ids.*' => ['integer', 'distinct', 'exists:employees,id']]);
        foreach ($data['employee_ids'] as $sort => $id) {
            Employee::query()->whereKey($id)->update(['sort' => $sort]);
        }
        system_log('reorder_employees', 'Змінено порядок працівників з мобільного застосунку.');

        return response()->json(['message' => 'Порядок збережено.']);
    }

    public function updateBanner(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['banner' => ['nullable', 'image', 'max:10240'], 'remove_banner' => ['nullable', 'boolean']]);
        $settings = SiteSetting::query()->firstOrCreate([]);
        if ($request->boolean('remove_banner')) {
            $settings->team_banner = null;
        }
        if ($request->hasFile('banner')) {
            $settings->team_banner = $request->file('banner')->store('team', 'public');
        }
        $settings->save();
        system_log('update_team_banner', 'Оновлено банер команди з мобільного застосунку.');

        return $this->options($request);
    }

    private function attributes(Request $request, array $data, ?Employee $employee = null): array
    {
        $attributes = Arr::except($data, ['photo_file', 'remove_photo']);
        if ($request->boolean('remove_photo')) {
            $attributes['photo'] = null;
        }
        if ($request->hasFile('photo_file')) {
            $attributes['photo'] = $request->file('photo_file')->store('employees', 'public');
        }

        return $attributes;
    }

    private function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'sort' => ['nullable', 'integer', 'min:0'],
            'photo_file' => ['nullable', 'image', 'max:8192'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403, 'Керування командою доступне лише адміністраторам.');
    }

    private function name(Employee $employee): string
    {
        return trim($employee->last_name.' '.$employee->first_name);
    }
}
