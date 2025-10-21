<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportToCollection implements ToCollection
{
    public function collection(Collection $collection): Collection
    {
        return $collection;
    }
}
