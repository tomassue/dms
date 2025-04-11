<?php

namespace App\Livewire\Components;

use App\Models\PdfAsset;
use Dompdf\Dompdf;
use Dompdf\Options;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * * The htmlContent should be one is to one with reports.
 *   This component is for generating PDFs for reports.
 * * Create another method for different report.
 */
class GeneratePdfComponent extends Component
{
    public $pdf;
    public $report; //* Determine what report to generate
    public $accomplishments = []; //* Updated accomplishments

    public function mount($report, $accomplishments)
    {
        $this->report = $report;
        $this->accomplishments = $accomplishments;
    }

    public function clear()
    {
        $this->resetExcept('report');
    }

    public function render()
    {
        return view('livewire.components.generate-pdf-component');
    }

    public function generatePDF()
    {
        switch ($this->report) {
            case 'accomplishments':
                // Generate Accomplishment Report
                $this->generateAccomplishmentPDF();
                break;
            default:
                $this->dispatch('error', message: 'Report not found.');
        }
    }

    #[On('filtered-accomplishments')]
    public function filteredAccomplishments($accomplishments)
    {
        $this->accomplishments = $accomplishments;
    }

    public function generateAccomplishmentPDF()
    {
        // Prepare image data with proper base64 format
        $data = [];

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
            'accomplishments' => $this->accomplishments
        ];

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
