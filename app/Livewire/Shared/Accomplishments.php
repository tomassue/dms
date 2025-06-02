<?php

namespace App\Livewire\Shared;

use App\Models\Accomplishment;
use App\Models\Apo\Accomplishment as ApoAccomplishment;
use App\Models\PdfAsset;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefSignatories;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Accomplishments')]
class Accomplishments extends Component
{
    use WithPagination;

    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date;
    public $accomplishmentId;
    public $ref_accomplishment_category_id,
        $date,
        $details;
    public $pdf,
        $prepared_by,
        $conforme,
        $approved;

    //* begin::APO
    public $sub_category,
        $start_date,
        $end_date,
        $next_steps;
    public $report = "accomplishments";
    //* end:: APO

    /**
     * dehydrate()
     * he dehydrate() method in Livewire is triggered after every component request cycle, including on the request that occurs during logout. 
     * At that moment, auth()->user() no longer exists because the session has been cleared or is in the process of being destroyed — which results in null, 
     * and then trying to access ->name causes the 404 (or sometimes a null property access error, depending on your config).
     * * Add a guard against this by checking if the user is still authenticated before accessing auth()->user()->name. 
     */
    public function dehydrate()
    {
        if (auth()->check()) {
            $this->prepared_by = auth()->user()->name;
        }
    }


    public function rules()
    {
        if (auth()->user()->hasRole('APOO')) {
            $rules = [
                'ref_accomplishment_category_id' => 'required|exists:ref_accomplishment_categories,id',
                'details' => 'required',
                'sub_category' => 'required',
                'start_date' => 'required|date|before_or_equal:end_date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ];
        } else {
            $rules = [
                'ref_accomplishment_category_id' => 'required|exists:ref_accomplishment_categories,id',
                'details' => 'required',
                'date' => 'required|date'
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'ref_accomplishment_category_id' => 'accomplishment category',
        ];
    }

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->resetExcept('start_date', 'end_date');
        $this->resetValidation();
    }

    //* Listens for an event from MenuFilterComponent (child component)
    #[On('filter')]
    public function filter($start_date, $end_date)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
    }

    // public function updated($property)
    // {
    //     if ($property === "filter_start_date" || $property === "filter_end_date") {
    //         dd('wew');
    //         /**
    //          * getCollection(): Returns a collection of the query results
    //          * The loadAccomplishments() method returns a collection of Accomplishment objects, which is a collection of Accomplishment models.
    //          * The getCollection() method returns a collection of the query results, which is a collection of Accomplishment objects.
    //          * * The getCollection() removes pagination to use foreach loop.
    //          * 
    //          * Then we dispatch an event to the GeneratePdfComponent class with the accomplishments collection.
    //          */
    //         $accomplishments = $this->loadAccomplishments()->getCollection();
    //         $this->dispatch('filtered-accomplishments', accomplishments: $accomplishments)->to(GeneratePdfComponent::class);
    //     }
    // }

    public function render()
    {
        return view(
            'livewire.shared.accomplishments',
            [
                'accomplishments' => $this->loadAccomplishments(),
                'accomplishment_categories' => $this->loadAccomplishmentCategories(), // Accomplishment Category dropdown
                'signatories' => $this->loadSignatories(), // Signatories dropdown
                'conformees_signatories' => $this->loadConformeesSignatories(), // Signatories dropdown
                'approved_by_signatories' => $this->loadApprovedBySignatories() // Signatories dropdown
            ]
        );
    }

    public function loadSignatories()
    {
        return RefSignatories::get();
    }

    public function loadConformeesSignatories()
    {
        return RefSignatories::withinDivision()->get();
    }

    /**
     * loadApprovedBySignatories
     * * Should be scalable for new offices.
     * Must load a separate set of options for each office (role).
     */
    public function loadApprovedBySignatories()
    {
        // Load if Role is APO
        return RefSignatories::cityAgriculturist()->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function loadAccomplishments()
    {
        return Accomplishment::query()
            ->with(['accomplishment_category', 'apo'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('details', 'like', '%' . $this->search . '%')
                        ->orWhereHas('apo', function ($q) {
                            $q->where('sub_category', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filter_start_date && $this->filter_end_date, function ($query) {
                $query->whereHas('apo', function ($q) {
                    $q->where(function ($innerQ) {
                        $innerQ->whereDate('start_date', '>=', $this->filter_start_date)
                            ->whereDate('start_date', '<=', $this->filter_end_date);
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function loadAccomplishmentCategories()
    {
        $accomplishment_categories = RefAccomplishmentCategory::get()
            ->map(function ($query) {
                return [
                    'id' => $query->id,
                    'name' => $query->accomplishment_category_name
                ];
            });

        return $accomplishment_categories;
    }

    public function saveAccomplishment()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $accomplishment = $this->saveMainAccomplishment();
                $this->saveApoAccomplishment($accomplishment); // Save APO Accomplishment

                $this->clear();
                $this->dispatch('hide-accomplishment-modal');
                $this->dispatch('success', message: 'Accomplishment successfully saved.');
            });
        } catch (\Throwable $th) {
            report($th); // Log the error
            $this->dispatch('error', message: 'Operation failed. Please try again.');
        }
    }

    protected function saveMainAccomplishment()
    {
        $data = [
            'ref_accomplishment_category_id' => $this->ref_accomplishment_category_id,
            'date' => $this->date,
            'details' => $this->details,
            'office_id' => auth()->user()->roles()->first()->id,
            'ref_division_id' => auth()->user()->user_metadata?->ref_division_id
        ];

        return Accomplishment::updateOrCreate(
            ['id' => $this->accomplishmentId ?? null],
            $data
        );
    }

    protected function saveApoAccomplishment($accomplishment)
    {
        if (!auth()->user()->hasRole('APOO')) return;

        ApoAccomplishment::updateOrCreate(
            ['accomplishment_id' => $accomplishment->id],
            [
                'sub_category' => $this->sub_category,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'next_steps' => $this->next_steps
            ]
        );
    }

    public function editAccomplishment($accomplishmentId)
    {
        try {
            $accomplishment = Accomplishment::with('apo')->findOrFail($accomplishmentId);

            $this->accomplishmentId = $accomplishmentId;
            $this->ref_accomplishment_category_id = $accomplishment->ref_accomplishment_category_id;
            $this->date = optional($accomplishment->date)->format('Y-m-d');
            $this->details = $accomplishment->details;

            if (auth()->user()->hasRole('APOO') && $accomplishment->apo) {
                $this->sub_category = $accomplishment->apo->sub_category;
                $this->start_date = optional($accomplishment->apo->start_date)->format('Y-m-d');
                $this->end_date = optional($accomplishment->apo->end_date)->format('Y-m-d');
                $this->next_steps = $accomplishment->apo->next_steps;
            }

            $this->editMode = true;
            $this->dispatch('show-accomplishment-modal');
        } catch (\Throwable $th) {
            report($th);
            $this->dispatch('error', message: 'Failed to load accomplishment data.');
        }
    }

    public function generatePDF()
    {
        // Prepare image data with proper base64 format
        $data = [];
        $viewData = [];

        // Get all header assets
        $pdf_asset_headers = PdfAsset::header()->get();
        foreach ($pdf_asset_headers as $asset) {
            // Ensure the base64 string has the proper data URI format
            $data[$asset->title] = $this->formatBase64Image($asset->file);
        }

        // Get user's division
        $user_division = auth()->user()->user_metadata->division->name ?? null;

        // Prepare view data
        $viewData = [
            'cdo_seal' => $data['cdo seal'] ?? null,
            'rise' => $data['rise'] ?? null,
            'division' => $user_division,
            'filter_start_date' => $this->filter_start_date,
            'filter_end_date' => $this->filter_end_date,
            'accomplishments' => $this->loadAccomplishments()
        ];

        if (auth()->user()->hasRole('APOO')) {
            $this->validate([
                'conforme' => 'required',
                'approved' => 'required'
            ]);

            // Get prepared_by user data
            $preparedUser = auth()->user();
            $viewData['prepared_by'] = $preparedUser
                ? $preparedUser->name
                : '';
            $viewData['prepared_by_position'] = $preparedUser->user_metadata->position->name ?? 'Admin';
            $viewData['prepared_by_division'] = $preparedUser->user_metadata->division->name ?? null;

            // Get conforme user data
            $conforme = RefSignatories::find($this->conforme);
            $viewData['conforme'] = $conforme
                ? $conforme->name
                : '';
            $viewData['conforme_position'] = null;
            $viewData['conforme_division'] = $conforme->title ?? null;

            // Get approved user data
            $approved = RefSignatories::find($this->approved);
            $viewData['approved'] = $approved
                ? $approved->name
                : '';
            $viewData['approved_position'] = null;
            $viewData['approved_division'] = $approved->title ?? null;

            // Hide the modal
            $this->dispatch('hide-accomplishment-signatories-modal');
        }

        $htmlContent = view('livewire.apo.reports.pdf.accomplishment-report', $viewData)->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true); // Important for base64 support

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $this->pdf = 'data:application/pdf;base64,' . base64_encode($dompdf->output());

        $this->dispatch('show-pdf-modal');
    }

    /**
     * Format base64 image string with proper data URI
     */
    protected function formatBase64Image($base64)
    {
        // If already formatted, return as-is
        if (str_starts_with($base64, 'data:')) {
            return $base64;
        }

        // Try to detect image type from the base64 content
        $mime = $this->detectImageMimeType($base64);

        return 'data:' . $mime . ';base64,' . $base64;
    }

    /**
     * Detect image MIME type from base64 content
     */
    protected function detectImageMimeType($base64)
    {
        // Get the first few characters of the base64 string
        $signature = substr($base64, 0, 20);

        // Detect common image formats
        if (str_starts_with($signature, '/9j/')) {
            return 'image/jpeg';
        } elseif (str_starts_with($signature, 'iVBORw')) {
            return 'image/png';
        } elseif (str_starts_with($signature, 'R0lGOD')) {
            return 'image/gif';
        } elseif (str_starts_with($signature, 'Qk2')) {
            return 'image/bmp';
        }

        // Default to JPEG if unknown
        return 'image/jpeg';
    }
}
