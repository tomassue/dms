<?php

namespace App\Livewire\Components;

use App\Models\Apo\Meeting;
use App\Models\Apo\MinutesOfMeeting;
use App\Models\PdfAsset;
use Dompdf\Dompdf;
use Dompdf\Options;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class MinutesOfAMeeting extends Component
{
    #[Reactive]
    public $show; //* Opposite from parent component
    public $editMode;
    public $apoMinutesOfMeetingId;

    #[Reactive]
    public $apoMeetingId;
    public $activity, $point_person, $expected_output, $agreements;
    public $pdf;

    public function rules()
    {
        return [
            'activity' => [
                'required'
            ],
            'point_person' => [
                'sometimes'
            ],
            'expected_output' => [
                'sometimes'
            ],
            'agreements' => [
                'sometimes'
            ],
        ];
    }

    /**
     * goBack()
     * Emits and event to parent component
     * The purpose of this method is to emit an event to the parent component then there's a listener to it that performs an action
     * to clear the $apoMeetingId that will close the child component.
     */
    public function goBack()
    {
        $this->dispatch('clear');
    }

    public function cancel()
    {
        $this->resetExcept('apoMeetingId', 'show');
        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.components.minutes-of-a-meeting',
            [
                'apo_meeting' => $this->loadApoMeeting($this->apoMeetingId),
                'minutes_of_meeting' => $this->loadMinutesofAMeeting(new MinutesOfMeeting()),
            ]
        );
    }

    public function loadApoMeeting($apoMeetingId)
    {
        return Meeting::find($apoMeetingId);
    }

    public function loadMinutesofAMeeting(MinutesOfMeeting $minutes)
    {
        return $minutes->where('apo_meeting_id', $this->apoMeetingId)->get();
    }

    public function saveMinutes()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                MinutesOfMeeting::updateOrCreate(
                    [
                        'id' => $this->apoMinutesOfMeetingId
                    ],
                    [
                        'apo_meeting_id' => $this->apoMeetingId,
                        'activity' => $this->activity,
                        'point_person' => $this->point_person,
                        'expected_output' => $this->expected_output,
                        'agreements' => $this->agreements
                    ]
                );

                $this->cancel();
                $this->dispatch('success', message: 'Minutes successfully saved.');
            });
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editMinute(MinutesOfMeeting $minutesOfMeeting)
    {
        try {
            $this->apoMinutesOfMeetingId = $minutesOfMeeting->id;

            $this->activity = $minutesOfMeeting->activity;
            $this->point_person = $minutesOfMeeting->point_person;
            $this->expected_output = $minutesOfMeeting->expected_output;
            $this->agreements = $minutesOfMeeting->agreements;

            $this->dispatch('scroll-to-top');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function removeMinute(MinutesOfMeeting $minutesOfMeeting)
    {
        try {
            $minutesOfMeeting->delete();
            $this->dispatch('success', message: 'Minutes successfully removed.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function checkFileExists(Meeting $meeting): bool
    {
        if ($meeting->file) {
            $this->dispatch('error', message: 'Minutes already exported.');
            return true; // File exists
        }

        return false; // File does not exist
    }

    public function printMinutesOfMeeting($apoMeetingId)
    {
        $meeting = Meeting::find($apoMeetingId);

        // ✅ Check and exit if file already exists
        if ($this->checkFileExists($meeting)) {
            return;
        }

        // Prepare image data with proper base64 format
        $data = [];

        // Get all header assets
        $pdf_asset_headers = PdfAsset::header()->get();
        foreach ($pdf_asset_headers as $asset) {
            // Ensure the base64 string has the proper data URI format
            $data[$asset->title] = $this->formatBase64Image($asset->file);
        }

        $viewData = [
            'apo_meeting' => $this->loadApoMeeting($apoMeetingId),
            'minutes_of_meeting' => $this->loadMinutesofAMeeting(new MinutesOfMeeting()),
            'cdo_seal' => $data['cdo seal'] ?? null,
            'bagong_pilipinas' => $data['bagong pilipinas'] ?? null,
            'golden_cdo' => $data['cdo city of golden friendship'] ?? null
        ];

        $htmlContent = view('livewire.apo.reports.pdf.minutes-of-a-meeting', $viewData)->render();

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

    public function exportAndUploadPDF()
    {
        $apo_meeting = Meeting::find($this->apoMeetingId);

        // ✅ Check and exit if file already exists
        if ($this->checkFileExists($apo_meeting)) {
            return;
        }

        try {
            // Check if approved by and noted by is not null
            if ($apo_meeting->time_end && $apo_meeting->approvedBy && $apo_meeting->notedBy) {
                $apo_meeting->file = $this->pdf;
                $apo_meeting->save();

                $this->dispatch('hide-pdf-modal');
                $this->dispatch('success', message: 'Minutes successfully uploaded.');
            } else {
                $this->dispatch('error', message: "The minutes should include sections for 'Time End', 'Approved by', and 'Noted by'.");
            }
        } catch (\Throwable $th) {
            throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function viewExportedMinutesOfMeeting(Meeting $apoMeeting)
    {
        $this->pdf = $apoMeeting->file;
        $this->dispatch('show-view-pdf-modal');
    }
}
