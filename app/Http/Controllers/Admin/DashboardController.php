<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Specialty;
use App\Models\Brand;
use App\Models\PageVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Counts
        $totalDoctors    = Doctor::count();
        $totalLocations  = Location::count();
        $totalSpecialties = Specialty::count();
        $totalMedicines  = Brand::count();

        // Visitor stats
        $today     = Carbon::today()->toDateString();
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $yearStart = Carbon::now()->startOfYear()->toDateString();

        $visitorsToday   = PageVisit::where('visited_date', $today)->count();
        $visitorsWeek    = PageVisit::where('visited_date', '>=', $weekStart)->count();
        $visitorsYear    = PageVisit::where('visited_date', '>=', $yearStart)->count();
        $visitorsTotal   = PageVisit::count();

        // Last 7 days chart data
        $last7 = PageVisit::select(
                DB::raw('visited_date as date'),
                DB::raw('count(*) as total')
            )
            ->where('visited_date', '>=', Carbon::now()->subDays(6)->toDateString())
            ->groupBy('visited_date')
            ->orderBy('visited_date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->toDateString();
            $chartLabels[] = Carbon::parse($d)->format('M d');
            $chartData[]   = $last7->get($d)?->total ?? 0;
        }

        // Recent doctors
        $recentDoctors = Doctor::with('specialty','location')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Top specialties
        $topSpecialties = DB::table('doctors')
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
            ->select('specialties.name', DB::raw('count(*) as total'))
            ->groupBy('specialties.id', 'specialties.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalDoctors','totalLocations','totalSpecialties','totalMedicines',
            'visitorsToday','visitorsWeek','visitorsYear','visitorsTotal',
            'chartLabels','chartData','recentDoctors','topSpecialties'
        ));
    }
}
