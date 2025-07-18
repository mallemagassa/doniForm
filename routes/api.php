<?php

use App\Models\Program;
use App\Models\FormProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Resources\Admin\ApplicationResource;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// routes/api.php
Route::get('/programs/{program}/fields', function (Program $program) {
    $formProgram = FormProgram::where('program_id', $program->id)->first();
    
    if (!$formProgram) {
        return response()->json(['fields' => []]);
    }

    return response()->json([
        'fields' => $formProgram->formFields->map(function ($field) {
            return [
                'id' => $field->id,
                'label' => $field->label,
                'field_type' => $field->field_type,
                'required' => (bool)$field->required,
                'options' => $field->options
            ];
        })
    ]);
});