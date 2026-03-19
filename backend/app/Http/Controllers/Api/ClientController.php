<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['user', 'agencePrincipale']);

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('telephone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('ville')) {
            $query->where('ville', $request->ville);
        }

        if ($request->has('niveau_fidelite')) {
            $query->where('niveau_fidelite', $request->niveau_fidelite);
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'      => 'required|string|max:255',
            'prenom'   => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'telephone' => 'nullable|string',
            'adresse'  => 'nullable|string',
            'ville'    => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $request->nom . ' ' . $request->prenom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('CLIENT');

        $client = Client::create([
            'user_id'   => $user->id,
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'telephone' => $request->telephone,
            'adresse'   => $request->adresse,
            'ville'     => $request->ville,
        ]);

        return response()->json($client->load('user'), 201);
    }

    public function show(string $id)
    {
        $client = Client::with(['user', 'agencePrincipale'])->findOrFail($id);
        return response()->json($client);
    }

    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'nom'       => 'sometimes|string|max:255',
            'prenom'    => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string',
            'adresse'   => 'sometimes|string',
            'ville'     => 'sometimes|string',
            'niveau_fidelite' => 'sometimes|in:BRONZE,SILVER,GOLD,PLATINUM',
        ]);

        $client->update($request->all());

        return response()->json($client->load('user'));
    }

    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès.']);
    }
}