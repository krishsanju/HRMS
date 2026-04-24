<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchemaInfoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SchemaInfoController extends Controller
{
    /**
     * Display a listing of the database tables and migrations.
     */
    public function index(Request $request)
    {
        // AC-2: List out all the tables
        // Using Doctrine DBAL to get table names, as Schema::getTableNames() might not be available in all Laravel versions
        // or might not return all system tables depending on the driver.
        // listTableNames() is more robust for this purpose.
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        // AC-3: List out all the migrations
        $migrations = DB::table('migrations')->get();

        return new SchemaInfoResource([
            'tables' => $tables,
            'migrations' => $migrations
        ]);
    }
}