<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Generic;
use App\Models\Brand;
use Illuminate\Support\Facades\Schema;

class SyncMedexlyData extends Command
{
    protected $signature = 'data:sync-medexly';
    protected $description = 'Syncs scraped SQLite database to Laravel MySQL without deleting existing records';

    public function handle()
    {
        $sqlitePath = 'c:\Users\Ahad\Desktop\P\My Personal\uhdscrap\database.db';
        if (!file_exists($sqlitePath)) {
            $this->error("SQLite database not found at: $sqlitePath");
            return 1;
        }

        $this->info("Connecting to $sqlitePath...");
        $pdo = new \PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Fetch Generics
        $this->info("Syncing Generics...");
        $stmt = $pdo->query("SELECT * FROM generics");
        $generics = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $syncedGenerics = 0;
        foreach ($generics as $gen) {
            if (Generic::where('id', $gen['id'])->exists()) {
                continue;
            }
            Generic::create([
                'id' => $gen['id'],
                'name' => $gen['name'] ?? ''
            ]);
            $syncedGenerics++;
        }
        $this->info("Successfully inserted $syncedGenerics new generics.");

        // Fetch Brands
        $this->info("Syncing Brands...");
        $stmt = $pdo->query("SELECT * FROM brands");
        $brands = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $brandColumns = Schema::getColumnListing('brands');
        
        $syncedBrands = 0;
        foreach ($brands as $brandRaw) {
            if (Brand::where('id', $brandRaw['id'])->exists()) {
                continue;
            }

            $updateData = ['id' => $brandRaw['id']];
            foreach ($brandRaw as $key => $value) {
                if (in_array($key, $brandColumns) && $key !== 'id') {
                    $updateData[$key] = $value;
                }
            }
            
            // Fix slug requirement
            if (empty($updateData['slug'])) {
                $name = $updateData['name'] ?? 'unknown';
                $dosage = $updateData['dosage_form'] ?? '';
                $updateData['slug'] = \Illuminate\Support\Str::slug($name . '-' . $dosage) . '-' . $brandRaw['id'];
            }

            // Ensure boolean correctly parsed if needed
            if (isset($updateData['is_antibiotic'])) {
                $updateData['is_antibiotic'] = (bool) $updateData['is_antibiotic'];
            }

            // Insert new record
            Brand::create($updateData);
            $syncedBrands++;
        }
        $this->info("Successfully inserted $syncedBrands new brands.");

        $this->info("Sync completed successfully!");
        return 0;
    }
}
