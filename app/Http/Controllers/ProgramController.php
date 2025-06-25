<?php
namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    /**
     * Affiche la liste des programmes disponibles.
     */
    public function index()
    {
        $programs = Program::query()
        ->orderBy('created_at', 'desc')
        ->paginate(10); 
        return Inertia::render('Template/Programs/Index', [
            'programs' => $programs,
        ]);
    }

    /**
     * Affiche les détails d’un programme spécifique.
     */
  public function show(Program $program)
{
    return Inertia::render('Template/Programs/Show', [
        'program' => $program->only([
            'id', 'title', 'description', 'start_date', 'end_date', 'status'
        ]),
    ]);
}

    /**
     * Affiche le formulaire de candidature dynamique pour un programme.
     */
    public function apply(Program $program)
    {
        // Simule des champs dynamiques associés au programme
        $fields = [
            ['label' => 'Nom complet', 'type' => 'text', 'required' => true],
            ['label' => 'Email', 'type' => 'email', 'required' => true],
            ['label' => 'Secteur d\'activité', 'type' => 'select', 'required' => true, 'options' => ['Agritech', 'EdTech', 'Fintech']],
            ['label' => 'Lettre de motivation', 'type' => 'textarea', 'required' => true],
            ['label' => 'Business Plan (PDF)', 'type' => 'file', 'required' => true],
        ];

        return Inertia::render('Template/Programs/Apply', [
            'program' => $program,
            'fields' => $fields,
        ]);
    }

    /**
     * Traitement de la soumission de candidature.
     */
    public function submit(Request $request, Program $program)
    {
        // Exemple de validation générique
        $validated = $request->validate([
            'Nom complet' => 'required|string|max:255',
            'Email' => 'required|email',
            'Secteur d\'activité' => 'required|string',
            'Lettre de motivation' => 'required|string',
            'Business Plan (PDF)' => 'required|file|mimes:pdf|max:2048',
        ]);


        return redirect()->route('template.program.show', $program->id)
            ->with('success', 'Votre candidature a bien été soumise.');
    }
}
