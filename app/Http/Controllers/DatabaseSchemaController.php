<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseSchemaController extends Controller
{
    public function index()
    {
        $tables = DB::select('SHOW TABLES');
        $schema = [];
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $schema[$tableName] = DB::select("DESCRIBE {$tableName}");
        }
        
        return view('database-schema', compact('schema'));
    }
}