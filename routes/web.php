<?php

use Inertia\Inertia;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ProgramController;
use App\Resources\Admin\ApplicationResource;
use App\Resources\Admin\ProgramResource;

// // Accueil
// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('welcome');


// Routes FilePond avec préfixe 'admin/filepond' et middleware 'auth'
Route::put('/evaluation-items/{id}/toggle', [ApplicationResource::class, 'toggle'])->name('evaluation-items.toggle');
Route::post('/note-items', [ApplicationResource::class, 'storeNote'])->name('note-items.store');
Route::put('/note-items/{noteItem}', [ApplicationResource::class, 'updateNote'])->name('note-items.update');
Route::post('/import', [ProgramResource::class, 'import'])->name('import.process');
Route::post('/import-app', [ApplicationResource::class, 'import'])->name('import.process.app');
Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {


    Route::prefix('filepond')->name('filepond.')->group(function () {

        // [1] Process (upload standard)
        Route::post('/process', function (Request $request) {
            $request->validate(['file_path' => 'required|file|max:10240']); // max 10MB
            $file = $request->file('file_path');
            $path = $file->store('filepond-temp', 'local');

            return response()->json([
                'id' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'file' => $path,
            ]);
        })->name('process');

        // [2] Patch (upload par chunks — optionnel)
        Route::patch('/patch/{id}', function (Request $request, $id) {
            $path = 'filepond-temp/' . $id;

            Storage::disk('local')->append($path, $request->getContent());

            return response()->json([
                'offset' => $request->header('Upload-Offset') + strlen($request->getContent())
            ]);
        })->name('patch');

        // [3] Revert (annulation d’upload)
        Route::delete('/revert', function (Request $request) {
            Storage::disk('local')->delete($request->getContent());
            return response()->noContent();
        })->name('revert');

        // [4] Restore (affichage fichier temporaire — prévisualisation)
        Route::get('/restore/{id}', function ($id) {
            if (!Storage::disk('local')->exists($id)) {
                abort(404);
            }

            return response()->file(storage_path('app/' . $id));
        })->where('id', '.*')->name('restore');

        // [5] Load (chargement fichier déjà stocké)
        Route::get('/load/{path}', function ($path) {
            $path = urldecode($path);
            if (!Storage::disk('public')->exists($path)) {
                abort(404);
            }

            return Storage::disk('public')->response($path);
        })->where('path', '.*')->name('load');

        // [6] Fetch (métadonnées fichier distant — type HEAD)
        Route::match(['HEAD'], '/fetch/{path}', function ($path) {
            $path = urldecode($path);
            if (!Storage::disk('public')->exists($path)) {
                abort(404);
            }

            return response()->noContent()
                ->header('Upload-Name', basename($path))
                ->header('Upload-Length', Storage::disk('public')->size($path))
                ->header('Content-Type', Storage::disk('public')->mimeType($path));
        })->where('path', '.*')->name('fetch');

        // [7] Remove (supprimer fichier manuellement)
        Route::delete('/remove', function (Request $request) {
            $request->validate(['path' => 'required']);
            Storage::disk('public')->delete($request->input('path'));
            return response()->noContent();
        })->name('remove');
    });
});


// Template Page
/*
 Page d'Accueil
*/

Route::middleware(['verified'])->group(function () {
    Route::get('/', function(){
        $programs = Program::latest()->take(6)->get();
            return Inertia::render('Template/Home/Index', [
                'programs' => $programs,
            ]);
    })->name('home');

});
/*
 Page des Programmes
*/



Route::get('/programs', [ProgramController::class, 'index'])->name('program.index');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('program.show');
Route::get('/programs/{program}/apply', [ProgramController::class, 'apply'])->name('apply');
Route::post('/programs/{program}/apply', [ProgramController::class, 'submit'])->name('apply.submit');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

