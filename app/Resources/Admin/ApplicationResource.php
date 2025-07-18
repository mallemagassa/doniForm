<?php

namespace App\Resources\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\Document;
use App\Models\NoteItem;
use App\Models\Evaluation;
use App\Models\Application;
use App\Resources\Resource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\ApplicationsImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EvaluationCriteriaItem;
use App\Resources\Builders\FormBuilder;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;

class ApplicationResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Application::class;
    protected static string $panel = 'admin';
    public static string $label = 'Candidature';

    
    public static function programOptions(): array
    {
        return Program::query()
            ->select(['id', 'title'])
            ->get()
            ->pluck('title', 'id')
            ->toArray();
    }


    public static function evaluationsOptions(): array
    {
        return Evaluation::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function documentsOptions(): array
    {
        return Document::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

   
    public static function index(Request $request): \Inertia\Response
    {
        $table = (new TableBuilder(Application::class))
        ->with(['program.evaluationCriteria.evaluationCriteriaItems'])
        ->column('num_candidat', 'N° Candidat')
        ->column('program.title', 'Programme')
        ->column('submitted_at', 'Date de soumission')
        ->column('status', 'Statut')
        // ->filterableColumn('num_candidat')
        // ->filterableColumn('status')
        ->columnCallback('checked_items', 'Critères validés', function ($app) {
            $items = optional($app->program->evaluationCriteria)->evaluationCriteriaItems ?? collect();
            return $items->where('is_checked', true)->count().'/'.$items->count();
        })
        ->make($request);

        // dd($table);

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'programs' => Program::all(['id', 'title']),
            'filters' => $request->only(['search', 'filters']),
            'resource' => static::getResourceData(),
        ]);
    }

    // public static function index(Request $request): \Inertia\Response
    // {
    //     // Récupération correcte des filtres
    //     $filters = $request->has('filters') ? $request->input('filters') : [];
    //     $search = $request->input('search', '');

    //     Log::info('Request params:', [
    //         'filters' => $filters,
    //         'search' => $search,
    //         'all' => $request->all()
    //     ]);

    //     $table = (new TableBuilder(static::$model))
    //         ->with(['program.evaluationCriteria.evaluationCriteriaItems'])
    //         ->column('num_candidat', 'N° candidat')
    //         ->column('program.title', 'Programme')
    //         ->column('submitted_at', 'Date soumission')
    //         ->column('status', 'Status')
    //         ->columnCallback('checked_items', 'Critère de sélection', function ($application) {
    //             $items = optional(optional($application->program)->evaluationCriteria)->evaluationCriteriaItems ?? collect();
    //             return $items->where('is_checked', true)->count().'/'.$items->count();
    //         })
    //         ->make($request);

    //     return Inertia::render(static::getComponentPath('index'), [
    //         'table' => $table,
    //         'programs' => Program::all(['id', 'title']),
    //         'resource' => static::getResourceData(),
    //         'filters' => $filters,
    //         'search' => $search,
    //         'pagination' => [
    //             'per_page' => $table['per_page'],
    //             'current_page' => $table['current_page'],
    //             'total' => $table['total'],
    //             'last_page' => $table['last_page'],
    //         ],
    //     ]);
    // }

    public function showCustomPage(string $panel, string $page, Request $request)
    {
        $table = (new TableBuilder(static::$model))
            ->query(function ($query) {
                $query->where('status', 'approved');
            })
            ->with([
                'program' => function($query) {
                    $query->with([
                        'grilleLabels.grilleItems.noteItems', // Chargement des notes
                        'region'
                    ]);
                }
            ])
            ->column('num_candidat', 'N° candidat')
            ->column('program.title', 'Programme')
            ->column('submitted_at', 'Date soumission')
            ->make($request);

        return Inertia::render("{$panel}/Pages/" . Str::studly($page), [
            'table' => $table,
            'filters' => $request->only(['search', 'filters']),
            'resource' => static::getResourceData(),
        ]);
    }

    public function storeNote(Request $request)
    {
        $validated = $request->validate([
            'note' => 'required|numeric|min:0',
            'grille_item_id' => 'required|exists:grille_items,id'
        ]);

        $noteItem = NoteItem::create([
            'note' => abs($validated['note']),
            'grille_item_id' => $validated['grille_item_id']
        ]);
        
        return back()->with([
            'success' => true,
            'noteItem' => $noteItem,
            'message' => 'Note enregistrée avec succès'
        ]);
    }

    public function updateNote(Request $request, NoteItem $noteItem)
    {
        $validated = $request->validate([
            'note' => 'required|numeric|min:0'
        ]);
        
        $noteItem->update([
            'note' => abs($validated['note'])
        ]);

        return back()->with([
            'success' => true,
            'noteItem' => $noteItem,
            'message' => 'Note mise à jour avec succès'
        ]);
    }
    // public function showCustomPage(string $panel, string $page)
    // {
    //     $table = (new TableBuilder(static::$model))
    //         ->query(function ($query) {
    //             $query->where('status', 'approved');
    //         })
    //         ->with([
    //             'user',
    //             'program' => function($query) {
    //                 $query->with([
    //                     'grilleLabels.grilleItems',
    //                     'evaluationCriteria.evaluationCriteriaItems',
    //                     'region'
    //                 ]);
    //             }
    //         ])
    //         ->column('num_candidat', 'N° candidat')
    //         ->column('user.name', 'Nom candidat')
    //         ->column('program.title', 'Programme')
    //         ->column('submitted_at', 'Date soumission')
    //         ->make();
    
    //     return Inertia::render("{$panel}/Pages/" . Str::studly($page), [
    //         'table' => $table,
    //         'resource' => static::getResourceData(),
    //     ]);
    // }


    public static function show($id): \Inertia\Response
    {
        $application = static::$model::with([
            'program.evaluationCriteria.evaluationCriteriaItems',
        ])->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'application' => $application,
            'resource' => static::getResourceData($application),
            'evaluationcriteria' => $application->program->evaluationCriteria ?? null,
        ]);
    }

    public function toggle(Request $request, $id)
    {
        $item = EvaluationCriteriaItem::findOrFail($id);
        $item->is_checked = $request->boolean('is_checked');
        $item->save();

        return redirect()->back()->with('success', 'Mise à jour effectuée.');

    }
    
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('program_id', 'select', [
                        'options' => Program::pluck('title', 'id'),
                        'required' => true
                    ])
        ->field('submitted_at', 'date', ['required' => true])
        ->field('status', 'select', 
        [
                'options' => [
                    'draft' => 'draft',
                    'submitted' => 'submitted',
                    'validated_provisional' => 'validated_provisional',
                    'validated_final' => 'validated_final',
                ],
                'required' => true
            ])
        ->field('notes', 'textarea', ['required' => true])
        ->field('form_data', 'text', ['required' => true])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'program_id' => 'required|exists:programs,id',
            'submitted_at' => 'string|required',
            'status' => 'string|required',
            'notes' => 'string|required',
            'form_data' => 'string|required',
            
        ]);

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function edit($id): \Inertia\Response
    {
        $application = static::$model::findOrFail($id);

        $form = (new FormBuilder())
        ->field('program_id', 'select', [
                        'options' => Program::pluck('title', 'id'),
                        'value' => $application->program_id,
                        'required' => true
                    ])
        ->field('submitted_at', 'text', [
                        'required' => true,
                        'value' => $application->submitted_at
                    ])
        ->field('status', 'select', 
        [
                'options' => [
                    'draft' => 'draft',
                    'submitted' => 'submitted',
                    'validated_provisional' => 'validated_provisional',
                    'validated_final' => 'validated_final',
                ],
                'required' => true
            ])
        ->field('notes', 'textarea', [
                        'required' => true,
                        'value' => $application->notes
                    ])
        ->field('form_data', 'text', [
                        'required' => true,
                        'value' => $application->form_data
                    ])
        ->make();

        return Inertia::render(static::getComponentPath('edit'), [
            'application' => $application,
            'form' => $form,
            'resource' => static::getResourceData($application),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'program_id' => 'required|exists:programs,id',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $import = new ApplicationsImport(
                $request->program_id,
                $request->status,
                $request->notes
            );
            
            Excel::import($import, $request->file('file'));
            
            $count = $import->getRowCount(); // Ajoutez cette méthode à votre classe d'import
            
            return back()->with([
                'success' => 'Importation réussie !',
                'imported_count' => $count
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $application = static::$model::findOrFail($id);
    
        $data = request()->validate([
            // 'user_id' => 'required|exists:users,id',
            // 'program_id' => 'required|exists:programs,id',
            // 'submitted_at' => 'string|required',
            'status' => 'string|required',
            // 'notes' => 'string|required',
            // 'form_data' => 'string|required',
            
        ]);
    
        $application->update($data);
    
        return redirect()->route(static::getRouteName('show'), $id);

    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $application = static::$model::findOrFail($id);
        $application->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}