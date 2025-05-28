<!-- activityLogModal -->
<div class="modal fade" id="activityLogModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="activityLogModalLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="activityLogModalLabel">Outgoing History</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="clear"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="15%">Date</th>
                                <th>Causer</th>
                                <th>Changes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activity_log as $history)
                            <tr>
                                <td>{{ $history['created_at'] }}</td>
                                <td>{{ $history['causer'] . ' ' . $history['division'] }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @if ($history['changes'])
                                        @foreach ($history['changes'] as $change)
                                        <li>
                                            <strong>{{ $change['field'] }}:</strong>
                                            <!-- Removed the $change['old'] ➡ -->
                                            <span class="text-info">{{ $change['new'] }}</span>
                                            @if($change['new'] == 'forwarded')
                                            ➡
                                            <span class="text-warning">
                                                {{ implode(', ', array_column($forwarded_divisions->toArray(), 'division_name')) }}
                                            </span>
                                            @endif
                                        </li>
                                        @endforeach
                                        @elseif($history['file_log_description'])
                                        {{ $history['file_log_description'] }}
                                        @endif
                                    </ul>
                                </td>
                            </tr>
                            @empty
                            <tr class="text-center">
                                <td colspan="3">No changes found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="clear">Close</button>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('show-activity-log-modal', () => {
        $('#activityLogModal').modal('show');
    });
</script>
@endscript