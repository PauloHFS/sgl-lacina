<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgaoEmissor;
use Illuminate\Http\Request;

class OrgaoEmissorController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:50|unique:orgaos_emissores,sigla',
        ]);

        OrgaoEmissor::create($validated);

        return redirect()->back()->with('success', 'Órgão emissor criado com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgaoEmissor $orgaoEmissor)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:50|unique:orgaos_emissores,sigla,'.$orgaoEmissor->id,
        ]);

        $orgaoEmissor->update($validated);

        return redirect()->back()->with('success', 'Órgão emissor atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgaoEmissor $orgaoEmissor)
    {
        $orgaoEmissor->delete();

        return redirect()->back()->with('success', 'Órgão emissor excluído com sucesso.');
    }

    /**
     * Search for issuing bodies.
     */
    public function search(Request $request)
    {
        $term = $request->input('q');

        $results = OrgaoEmissor::search($term)->get();

        return response()->json($results);
    }
}
