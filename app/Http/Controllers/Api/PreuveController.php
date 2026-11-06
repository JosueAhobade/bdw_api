<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssocieMouvement;
use App\Models\Depenses;
use App\Models\Preuve;
use App\Models\Recettes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Preuves (PDF / images) attachées aux recettes et dépenses.
 * Stockées sur le disque privé : accessibles uniquement via l'API authentifiée.
 */
class PreuveController extends Controller
{
    public const MAX_SIZE_KB = 10240; // 10 Mo par fichier

    public function storeForDepense(Request $request, Depenses $depense)
    {
        return $this->store($request, $depense, 'depenses');
    }

    public function storeForRecette(Request $request, Recettes $recette)
    {
        return $this->store($request, $recette, 'recettes');
    }

    public function storeForMouvement(Request $request, AssocieMouvement $mouvement)
    {
        return $this->store($request, $mouvement, 'associes');
    }

    protected function store(Request $request, Model $operation, string $folder)
    {
        $request->validate([
            'fichiers' => 'required|array|min:1|max:10',
            'fichiers.*' => [
                'required',
                'file',
                'max:' . self::MAX_SIZE_KB,
                'mimes:pdf,jpg,jpeg,png,webp',
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp',
            ],
        ], [
            'fichiers.*.mimes' => 'Seuls les fichiers PDF, JPG, PNG ou WEBP sont acceptés.',
            'fichiers.*.mimetypes' => 'Seuls les fichiers PDF, JPG, PNG ou WEBP sont acceptés.',
            'fichiers.*.max' => 'Chaque fichier doit faire au maximum 10 Mo.',
        ]);

        $preuves = [];
        foreach ($request->file('fichiers') as $file) {
            // Nom aléatoire + extension déduite du contenu réel du fichier
            $path = $file->store("preuves/{$folder}/{$operation->getKey()}", Preuve::DISK);

            $preuves[] = $operation->preuves()->create([
                'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return response()->json([
            'message' => count($preuves) > 1 ? 'Preuves ajoutées.' : 'Preuve ajoutée.',
            'data' => $preuves,
        ], 201);
    }

    public function download(Preuve $preuve)
    {
        abort_unless(Storage::disk(Preuve::DISK)->exists($preuve->path), 404, 'Fichier introuvable.');

        return Storage::disk(Preuve::DISK)->download(
            $preuve->path,
            $preuve->original_name,
            [
                'Content-Type' => $preuve->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function destroy(Preuve $preuve)
    {
        $preuve->delete();

        return response()->json(['message' => 'Preuve supprimée.']);
    }
}
