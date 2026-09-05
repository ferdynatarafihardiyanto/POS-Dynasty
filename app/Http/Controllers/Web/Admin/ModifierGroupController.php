<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use App\Http\Requests\StoreModifierGroupRequest;
use App\Http\Requests\UpdateModifierGroupRequest;

class ModifierGroupController extends Controller
{
    public function index()
    {
        $groups = ModifierGroup::withCount('options')->latest()->paginate(10);
        return view('admin.modifier_group.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.modifier_group.create');
    }

    public function store(StoreModifierGroupRequest $request)
    {
        $data = $request->validated();
        $data['wajib_diisi'] = $request->boolean('wajib_diisi');
        $data['aktif'] = $request->boolean('aktif');
        ModifierGroup::create($data);
        return redirect()->route('admin.modifier-groups.index')->with('success', 'Modifier Group berhasil ditambahkan.');
    }

    public function edit(ModifierGroup $modifier_group)
    {
        $modifier_group->load('options');
        return view('admin.modifier_group.edit', compact('modifier_group'));
    }

    public function update(UpdateModifierGroupRequest $request, ModifierGroup $modifier_group)
    {
        $data = $request->validated();
        $data['wajib_diisi'] = $request->boolean('wajib_diisi');
        $data['aktif'] = $request->boolean('aktif');
        $modifier_group->update($data);
        return redirect()->route('admin.modifier-groups.index')->with('success', 'Modifier Group berhasil diperbarui.');
    }

    public function destroy(ModifierGroup $modifier_group)
    {
        if ($modifier_group->produks()->count() > 0) {
            return redirect()->route('admin.modifier-groups.index')->with('error', 'Group tidak dapat dihapus karena sudah terhubung dengan produk. Pertimbangkan untuk menonaktifkannya saja.');
        }
        $modifier_group->delete();
        return redirect()->route('admin.modifier-groups.index')->with('success', 'Modifier Group berhasil dihapus.');
    }
}
