<?php

namespace App\Livewire\Shared;

use App\Models\IncomingDocument;
use App\Models\IncomingRequest;
use App\Models\RefIncomingDocumentCategory;
use App\Models\RefIncomingRequestCategory;
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

    public ?string $yearSummaryType = null; // 'requests' | 'documents'

    public $yearSummaryStartDate;

    public $yearSummaryEndDate;

    public $yearSummaryCategoryFilter;

    public $yearSummarySortField = 'date'; // date | category | memo_no

    public $yearSummarySortDirection = 'desc'; // asc | desc

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
                'total_incoming_requests_this_year' => $this->loadTotalIncomingRequestsThisYear(),
                'total_incoming_documents_this_year' => $this->loadTotalIncomingDocumentsThisYear(),
                'year_summary_items' => $this->loadYearSummaryItems(),
                'year_summary_request_categories' => RefIncomingRequestCategory::withoutGlobalScopes()->get(),
                'year_summary_document_categories' => RefIncomingDocumentCategory::withoutGlobalScopes()->get(),
            ]
        );
    }

    public function loadTotalIncomingRequestsThisYear()
    {
        return IncomingRequest::withoutGlobalScopes()->whereYear('date_requested', now()->year)->count();
    }

    public function loadTotalIncomingDocumentsThisYear()
    {
        return IncomingDocument::withoutGlobalScopes()->whereYear('date', now()->year)->count();
    }

    public function openRequestsYearSummary(): void
    {
        $this->yearSummaryType = 'requests';
        $this->yearSummaryStartDate = now()->startOfYear()->format('Y-m-d');
        $this->yearSummaryEndDate = now()->endOfYear()->format('Y-m-d');
        $this->yearSummaryCategoryFilter = null;
        $this->yearSummarySortField = 'date';
        $this->yearSummarySortDirection = 'desc';
        $this->dispatch('show-year-summary-modal');
    }

    public function openDocumentsYearSummary(): void
    {
        $this->yearSummaryType = 'documents';
        $this->yearSummaryStartDate = now()->startOfYear()->format('Y-m-d');
        $this->yearSummaryEndDate = now()->endOfYear()->format('Y-m-d');
        $this->yearSummaryCategoryFilter = null;
        $this->yearSummarySortField = 'date';
        $this->yearSummarySortDirection = 'desc';
        $this->dispatch('show-year-summary-modal');
    }

    public function sortYearSummaryBy(string $field): void
    {
        if ($this->yearSummarySortField === $field) {
            $this->yearSummarySortDirection = $this->yearSummarySortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->yearSummarySortField = $field;
            $this->yearSummarySortDirection = 'desc';
        }
    }

    protected function loadYearSummaryItems()
    {
        if (! $this->yearSummaryType) {
            return collect();
        }

        $direction = $this->yearSummarySortDirection === 'asc' ? 'asc' : 'desc';

        if ($this->yearSummaryType === 'requests') {
            $query = IncomingRequest::query()
                ->withoutGlobalScopes()
                ->with(['category' => fn ($query) => $query->withoutGlobalScopes()])
                ->when($this->yearSummaryStartDate && $this->yearSummaryEndDate, function ($query) {
                    $query->whereBetween('date_requested', [
                        Carbon::parse($this->yearSummaryStartDate)->startOfDay(),
                        Carbon::parse($this->yearSummaryEndDate)->endOfDay(),
                    ]);
                })
                ->when($this->yearSummaryCategoryFilter, function ($query) {
                    $query->where('ref_incoming_request_category_id', $this->yearSummaryCategoryFilter);
                });

            match ($this->yearSummarySortField) {
                'category' => $query->orderBy(
                    RefIncomingRequestCategory::select('incoming_request_category_name')
                        ->whereColumn('id', 'incoming_requests.ref_incoming_request_category_id'),
                    $direction
                ),
                'memo_no' => $query->orderBy('memo_no', $direction),
                default => $query->orderBy('date_requested', $direction),
            };

            return $query->get(['id', 'ref_incoming_request_category_id', 'category_no', 'memo_no', 'date_requested']);
        }

        $query = IncomingDocument::query()
            ->withoutGlobalScopes()
            ->with(['category' => fn ($query) => $query->withoutGlobalScopes()])
            ->when($this->yearSummaryStartDate && $this->yearSummaryEndDate, function ($query) {
                $query->whereBetween('date', [
                    Carbon::parse($this->yearSummaryStartDate)->startOfDay(),
                    Carbon::parse($this->yearSummaryEndDate)->endOfDay(),
                ]);
            })
            ->when($this->yearSummaryCategoryFilter, function ($query) {
                $query->where('ref_incoming_document_category_id', $this->yearSummaryCategoryFilter);
            });

        match ($this->yearSummarySortField) {
            'category' => $query->orderBy(
                RefIncomingDocumentCategory::select('incoming_document_category_name')
                    ->whereColumn('id', 'incoming_documents.ref_incoming_document_category_id'),
                $direction
            ),
            default => $query->orderBy('date', $direction),
        };

        return $query->get(['id', 'ref_incoming_document_category_id', 'category_no', 'date']);
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
