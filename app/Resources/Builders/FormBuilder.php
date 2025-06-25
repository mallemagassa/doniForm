<?php

namespace App\Resources\Builders;

class FormBuilder
{
    protected array $fields = [];
    
    public function field(string $name, string $type, array $options = []): self
    {
        $this->fields[$name] = [
            'type' => $type,
            'options' => $options
        ];
        return $this;
    }
    
    public function make(): array
    {
        return $this->fields;
    }
}