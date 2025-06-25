<?php

namespace App\Resources\Admin;

use Inertia\Inertia;
use App\Models\Document;
use App\Models\Application;
use App\Resources\Resource;
use App\Resources\Builders\FormBuilder;
use Illuminate\Support\Facades\Storage;
use App\Resources\Builders\TableBuilder;
use App\Resources\Concerns\HasResourceData;

class DocumentResource extends Resource
{
    use HasResourceData;
    
    protected static string $model = Document::class;
    protected static string $panel = 'admin';
    protected static string $label = 'Document';

    
    public static function applicationOptions(): array
    {
        return Application::query()
            ->select(['id', 'name'])
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }


    public static function index(): \Inertia\Response
    {
        $table = (new TableBuilder(static::$model))
        ->column('application.program.title', 'Application')
        ->column('label', 'Label')
        ->column('file_path', 'File Path')
        ->column('uploaded_at', 'Uploaded At')
        ->make();

        return Inertia::render(static::getComponentPath('index'), [
            'table' => $table,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function show($id): \Inertia\Response
    {
        $document = static::$model::with([
            'application',
        ])->findOrFail($id);
    
        return Inertia::render(static::getComponentPath('show'), [
            'document' => $document,
            'resource' => static::getResourceData($document),
        ]);
    }
    
    public static function create(): \Inertia\Response
    {
        $form = (new FormBuilder())
        ->field('application_id', 'select', [
                        'options' => Application::pluck('status', 'id'),
                        'required' => true
                    ])
        ->field('label', 'text', ['required' => true])
        ->field('file_path', 'file', ['required' => true])
        ->make();

        return Inertia::render(static::getComponentPath('create'), [
            'form' => $form,
            'resource' => static::getResourceData(),
        ]);
    }

    public static function store(): \Illuminate\Http\RedirectResponse
    {
        $data = request()->validate([
            'application_id' => 'required|exists:applications,id',
            'label' => 'required|string',
            'file_path' => 'required|file|max:10240',
        ]);

        if (request()->hasFile('file_path')) {
            $data['file_path'] = request()->file('file_path')->store('documents', 'public');
        }        

        static::$model::create($data);

        return redirect()->route(static::getRouteName('index'));
    }


    public static function edit($id): \Inertia\Response
    {
        $document = static::$model::findOrFail($id);
    
        $form = (new FormBuilder())
            ->field('application_id', 'select', [
                'options' => Application::pluck('status', 'id'),
                'value' => $document->application_id,
                'required' => true
            ])
            ->field('label', 'text', [
                'required' => true,
                'value' => $document->label
            ])
            ->field('file', 'file', [
                'required' => false,
                'value' => $document->file_path, // Assurez-vous que c'est le chemin relatif
                'current_file' => $document->file_path // Pour l'affichage
            ])
            ->make();
    
        return Inertia::render(static::getComponentPath('edit'), [
            'document' => $document,
            'form' => $form,
            'resource' => static::getResourceData($document),
        ]);
    }

    public static function update($id): \Illuminate\Http\RedirectResponse
    {
        $document = static::$model::findOrFail($id);
        
        $data = request()->validate([
            'application_id' => 'required|exists:applications,id',
            'label' => 'required|string',
            'file' => 'nullable|file|max:10240',
        ]);

        if (request()->hasFile('file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = request()->file('file')->store('documents', 'public');
        }

        $document->update($data);

        return redirect()->route(static::getRouteName('index'));
    }

    public static function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $document = static::$model::findOrFail($id);
        $document->delete();
        return redirect()->route(static::getRouteName('index'));
    }

    protected static function getComponentPath(string $view): string
    {
        return static::$panel . '/Resources/' . class_basename(static::class) . '/' . $view;
    }
}