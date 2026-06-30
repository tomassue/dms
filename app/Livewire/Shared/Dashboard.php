<?php

namespace App\Livewire\Shared;

use App\Models\IncomingDocument;
use App\Models\IncomingRequest;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithPagination;

    public $weekOffset = 0; // 0 = current week, -1 = previous week, etc. Cannot go beyond 0 (the future).

    public function render()
    {
        return view(
            'livewire.shared.dashboard',
            [
                'pending_incoming_requests' => $this->loadPendingIncomingRequests(),
                'forwarded_incoming_requests' => $this->loadForwardedIncomingRequests(),
                'completed_incoming_requests' => $this->loadCompletedIncomingRequests(),
                'total_incoming_requests' => $this->loadTotalIncomingRequests(),
                'incoming_requests' => $this->loadIncomingRequests(),
                'incoming_documents' => $this->loadIncomingDocuments(),
                'monthly_stats' => $this->getMonthlyStats(),
                'weekly_stats' => $this->getWeeklyStats(),
            ]
        );
    }

    public function loadPendingIncomingRequests()
    {
        return IncomingRequest::pending()
            ->get();
    }

    public function loadForwardedIncomingRequests()
    {
        return IncomingRequest::forwarded()
            ->get();
    }

    public function loadCompletedIncomingRequests()
    {
        return IncomingRequest::completed()
            ->get();
    }

    public function loadTotalIncomingRequests()
    {
        return IncomingRequest::count();
    }

    public function loadIncomingRequests()
    {
        return IncomingRequest::received()
            ->paginate(5, pageName: 'incoming_requests');
    }

    public function loadIncomingDocuments()
    {
        return IncomingDocument::received()
            ->paginate(5, pageName: 'incoming_documents');
    }

    public function getMonthlyStats()
    {
        $year = now()->year;

        // Get Total Requests grouped by month
        $totalRequests = IncomingRequest::withoutGlobalScopes()
            ->selectRaw('MONTH(date_requested) as month, count(*) as count')
            ->whereYear('date_requested', $year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->all();

        // Get Completed Requests grouped by month
        $completedRequests = IncomingRequest::withoutGlobalScopes()
            ->completed()
            ->selectRaw('MONTH(date_requested) as month, count(*) as count')
            ->whereYear('date_requested', $year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->all();

        $data = [
            'months' => [],
            'total' => [],
            'completed' => []
        ];

        // Fill all 12 months to ensure the chart is complete
        for ($m = 1; $m <= 12; $m++) {
            $data['months'][] = Carbon::create()->month($m)->format('M');
            $data['total'][] = $totalRequests[$m] ?? 0;
            $data['completed'][] = $completedRequests[$m] ?? 0;
        }

        // dd($data);
        return $data;
    }

    public function getWeeklyStats()
    {
        $startOfWeek = Carbon::now()->addWeeks($this->weekOffset)->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = Carbon::now()->addWeeks($this->weekOffset)->endOfWeek(Carbon::SATURDAY);

        // DAYOFWEEK() returns 1 (Sunday) through 7 (Saturday) in MySQL
        $totalRequests = IncomingRequest::withoutGlobalScopes()
            ->selectRaw('DAYOFWEEK(date_requested) as day, count(*) as count')
            ->whereBetween('date_requested', [$startOfWeek, $endOfWeek])
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $completedRequests = IncomingRequest::withoutGlobalScopes()
            ->completed()
            ->selectRaw('DAYOFWEEK(date_requested) as day, count(*) as count')
            ->whereBetween('date_requested', [$startOfWeek, $endOfWeek])
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $data = [
            'days' => [],
            'total' => [],
            'completed' => [],
            'range_label' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y'),
        ];

        // Sunday (1) through Saturday (7) - label includes the date so each week is distinguishable
        for ($d = 1; $d <= 7; $d++) {
            $data['days'][] = $startOfWeek->copy()->addDays($d - 1)->format('D, M j');
            $data['total'][] = $totalRequests[$d] ?? 0;
            $data['completed'][] = $completedRequests[$d] ?? 0;
        }

        return $data;
    }

    public function previousWeek()
    {
        $this->weekOffset--;
        $this->dispatchWeeklyStats();
    }

    public function nextWeek()
    {
        if ($this->weekOffset < 0) {
            $this->weekOffset++;
        }
        $this->dispatchWeeklyStats();
    }

    protected function dispatchWeeklyStats()
    {
        $stats = $this->getWeeklyStats();

        $this->dispatch(
            'weekly-stats-updated',
            days: $stats['days'],
            total: $stats['total'],
            completed: $stats['completed'],
            rangeLabel: $stats['range_label'],
            canGoNext: $this->weekOffset < 0,
        );
    }
}
