<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        $spaces = Space::with('facilities')
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $keyword)->orWhere('location', 'like', $keyword));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $stats = [
            'total' => Space::count(),
            'active' => Space::where('is_active', true)->count(),
        ];

        return view('admin.spaces.index', compact('spaces', 'stats'));
    }

    public function create()
    {
        $facilities = Facility::orderBy('name')->get();

        return view('admin.spaces.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $facilityIds = $data['facilities'] ?? [];
        unset($data['facilities']);

        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('spaces', 'public');
        }

        $space = Space::create($data);
        $space->facilities()->sync($facilityIds);

        return redirect()->route('admin.spaces.index')->with('success', 'Ruang berhasil ditambahkan.');
    }

    public function edit(Space $space)
    {
        $space->load('facilities');
        $facilities = Facility::orderBy('name')->get();

        return view('admin.spaces.edit', compact('space', 'facilities'));
    }

    public function update(Request $request, Space $space)
    {
        $data = $request->validate($this->rules());
        $facilityIds = $data['facilities'] ?? [];
        unset($data['facilities']);

        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('photo')) {
            if ($space->photo) {
                Storage::disk('public')->delete($space->photo);
            }
            $data['photo'] = $request->file('photo')->store('spaces', 'public');
        }

        $space->update($data);
        $space->facilities()->sync($facilityIds);

        return redirect()->route('admin.spaces.index')->with('success', 'Ruang berhasil diperbarui.');
    }

    public function destroy(Space $space)
    {
        $space->delete();

        return redirect()->route('admin.spaces.index')->with('success', 'Ruang berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Space::TYPES))],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'location' => ['required', 'string', 'max:255'],
            'price_per_hour' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
        ];
    }
}
