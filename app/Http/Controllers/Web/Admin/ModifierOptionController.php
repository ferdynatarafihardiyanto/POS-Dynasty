<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModifierOption;
use App\Http\Requests\StoreModifierOptionRequest;
use App\Http\Requests\UpdateModifierOptionRequest;

class ModifierOptionController extends Controller
{
    public function store(StoreModifierOptionRequest $request)
    {
        $data = $request->validated();
        $data['aktif'] = $request->has('aktif') ? $request->boolean('aktif') : true;
        ModifierOption::create($data);
        return redirect()->route('admin.modifier-groups.edit', $data['modifier_group_id'])->with('success', 'Opsi Modifier berhasil ditambahkan.');
    }

    public function update(UpdateModifierOptionRequest $request, ModifierOption $modifier_option)
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif');
        $modifier_option->update($data);
        return redirect()->route('admin.modifier-groups.edit', $modifier_option->modifier_group_id)->with('success', 'Opsi Modifier berhasil diperbarui.');
    }

    public function destroy(ModifierOption $modifier_option)
    {
        // Since we don't track modifier_option direct relationship to transaction (it's snapshot), it's safe to delete, or restrict if we want.
        // But for safe architecture, we can just delete it.
        $group_id = $modifier_option->modifier_group_id;
        $modifier_option->delete();
        return redirect()->route('admin.modifier-groups.edit', $group_id)->with('success', 'Opsi Modifier berhasil dihapus.');
    }
}
