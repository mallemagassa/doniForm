<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProgramController;
use App\Models\Program;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('dashboard', function () {
    return Inertia::render('admin/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Template Page
/*
 Page d'Accueil
*/
Route::get('/home', function(){
     $programs = Program::all();
        return Inertia::render('Template/Home/Index', [
            'programs' => $programs,
        ]);
})->name('home');
/*
 Page des Programmes
*/


Route::get('/programs', [ProgramController::class, 'index'])->name('program.index');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('program.show');
Route::get('/programs/{program}/apply', [ProgramController::class, 'apply'])->name('apply');
Route::post('/programs/{program}/apply', [ProgramController::class, 'submit'])->name('apply.submit');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
