<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth =$currentMonth->copy()->endOfMonth();

        $totalTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->count();

        $activeTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                                    ->where('status', '!=', 'resolved')->count();

        $resolvedTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                                    ->where('status', 'resolved')->count();
        
        $avgResolutionTime = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                                    ->where('status', 'resolved')
                                    ->whereNotNull('completed_at')
                                    ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_time'))
                                    ->value('avg_time') ?? 0;

        $totalMinutes = (int) $avgResolutionTime;

        $days = floor($totalMinutes / 1440); 
        $hours = floor(($totalMinutes % 1440) / 60);
        $minutes = $totalMinutes % 60;

        if ($days > 0) {
            $formattedAvgResolutionTime = "{$days} hari {$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            $formattedAvgResolutionTime = "{$hours} jam {$minutes} menit";
        } else {
            $formattedAvgResolutionTime = "{$minutes} menit";
        }
        
        $statusDistribution = [
            'open' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'open')->count(),
            'on_progress' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'onprogress')->count(),
            'resolved' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'resolved')->count(),
            'rejected' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'rejected')->count(),
        ];

        $dashboardData = [
            'total_tickets' => $totalTickets,
            'active_tickets' => $activeTickets,
            'resolved_tickets' => $resolvedTickets,
            'avg_resolution_time' => $formattedAvgResolutionTime,
            'status_distribution' => $statusDistribution
        ];

        return response()->json([
            'message' => 'Dashboard statistic fetched successfully',
            'data' => new DashboardResource($dashboardData)
        ]);
    }
}
