<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Generic;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class MergeGenericsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'data:merge-generics';

    /**
     * The console command description.
     */
    protected $description = 'Merges duplicate Generics (by name) into a single main record and points all related Brands to the main Generic.';

    public function handle()
    {
        $this->info("Analyzing generic names for duplicates...");

        DB::beginTransaction();
        try {
            // Group all generics by case-insensitive name
            $generics = Generic::all();
            $groups = $generics->groupBy(function ($generic) {
                return strtolower(trim($generic->name));
            });

            $duplicateGroups = $groups->filter(function ($group) {
                return $group->count() > 1;
            });

            if ($duplicateGroups->isEmpty()) {
                $this->info("No duplicate generics found. Everything is clean!");
                return 0;
            }

            $this->info("Found " . $duplicateGroups->count() . " generic names with duplicates.");
            
            $totalDeleted = 0;
            $totalBrandsMoved = 0;

            foreach ($duplicateGroups as $name => $group) {
                // Sort by ID to ensure we consistently keep the oldest/first record as the 'main' one
                $sortedGroup = $group->sortBy('id')->values();
                
                $mainGeneric = $sortedGroup->first();
                $duplicateIds = $sortedGroup->slice(1)->pluck('id')->toArray();

                // Move brands from duplicates to the main generic
                $brandsMovedThisTime = Brand::whereIn('generic_id', $duplicateIds)
                                            ->update(['generic_id' => $mainGeneric->id]);
                
                $totalBrandsMoved += $brandsMovedThisTime;

                // Delete the duplicate generics
                Generic::whereIn('id', $duplicateIds)->delete();
                $totalDeleted += count($duplicateIds);

                $this->line("Merged '$name': Kept ID {$mainGeneric->id}, Deleted IDs [" . implode(', ', $duplicateIds) . "], Moved $brandsMovedThisTime Brands.");
            }

            DB::commit();
            
            $this->newLine();
            $this->info("✅ Merge Complete!");
            $this->info("Total Duplicate Generics Deleted: $totalDeleted");
            $this->info("Total Brands Remapped to Main Generic: $totalBrandsMoved");
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to merge generics. Rolled back changes.");
            $this->error($e->getMessage());
            return 1;
        }
    }
}
