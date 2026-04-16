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
}
