<?php

namespace App\Imports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\ToModel;

class ProgramsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Program([
            'title' => $row[0],
            'sigle' => $row[1],
            'nbr_membre_jury' => $row[2],
            'region_id' => $row[3],
            'start_date' => $row[4],
            'end_date' => $row[5],
            'status' => $row[6],
            'user_id' => $row[7],
            'description' => $row[8],
        ]);
    }
}
