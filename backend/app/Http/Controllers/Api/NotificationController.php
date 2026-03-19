<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('lu')) {
            $query->where('lu', $request->lu === 'true');
        }

        return response()->json($query->paginate(20));
    }

    public function marquerLu(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['lu' => true]);
        return response()->json($notification);
    }

    public function marquerToutLu(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }

    public function nonLues(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('lu', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function destroy(string $id)
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['message' => 'Notification supprimée.']);
    }
}