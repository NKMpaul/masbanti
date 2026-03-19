<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pointage;
use Illuminate\Http\Request;

class PointageController extends Controller
{
    public function index(Request $request)
    {
        $query = Pointage::with('employe');

        if ($request->has('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        if ($request->has('date')) {
            $query->where('date_pointage', $request->date);
        }

        if ($request->has('agence_id')) {
            $query->whereHas('employe', function($q) use ($request) {
                $q->where('agence_id', $request->agence_id);
            });
        }

        return response()->json($query->orderBy('date_pointage', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'    => 'required|exists:employes,id',
            'date_pointage' => 'required|date',
            'heure_arrivee' => 'nullable|date_format:H:i',
            'heure_depart'  => 'nullable|date_format:H:i',
            'statut'        => 'required|in:PRESENT,ABSENT,EN_RETARD,CONGE',
        ]);

        $pointage = Pointage::create($request->all());

        return response()->json($pointage->load('employe'), 201);
    }

    public function update(Request $request, string $id)
    {
        $pointage = Pointage::findOrFail($id);

        $request->validate([
            'heure_arrivee' => 'nullable|date_format:H:i',
            'heure_depart'  => 'nullable|date_format:H:i',
            'statut'        => 'sometimes|in:PRESENT,ABSENT,EN_RETARD,CONGE',
        ]);

        $pointage->update($request->all());

        return response()->json($pointage->load('employe'));
    }

    public function show(string $id)
    {
        return response()->json(Pointage::with('employe')->findOrFail($id));
    }

    public function destroy(string $id)
    {
        Pointage::findOrFail($id)->delete();
        return response()->json(['message' => 'Pointage supprimé avec succès.']);
    }
}