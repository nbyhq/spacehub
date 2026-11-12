<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::withCount('spaces')->orderBy('name')->get();

        return view('admin.facilities.index', compact('facilities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:facilities,name'],
        ]);

        Facility::create($data);

        return back()->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('facilities', 'name')->ignore($facility)],
        ]);

        $facility->update($data);

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return back()->with('success', 'Fasilitas berhasil dihapus.');
    }
}
