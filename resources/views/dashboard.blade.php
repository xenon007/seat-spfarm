@extends('web::layouts.app')

@section('content')
<div class="container-fluid my-4">
    <h1 class="mb-4">SP Farming Dashboard</h1>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Farm Characters</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Training Skill</th>
                            <th>% Plan</th>
                            <th>Extraction</th>
                            <th>PI</th>
                            <th>Location</th>
                            <th>Last Online</th>
                            <th>Omega</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($farmRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>
                                @if($row['is_idle'])
                                    <span class="text-danger font-weight-bold">IDLE</span>
                                @else
                                    {{ $row['training_skill'] ?? 'unknown' }}
                                @endif
                                @if($row['plan_text'])
                                    <span class="badge badge-info" data-toggle="tooltip" title="{{ $row['plan_text'] }}">Plan</span>
                                @endif
                            </td>
                            <td>{{ $row['plan_count'] }}</td>
                            <td>
                                @if($row['extraction'])
                                    {{ $row['extraction']->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-warning">{{ __('NEED ATTENTION!') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pi = $row['pi_status'];
                                    $icon = 'fa-times text-muted';
                                    $title = __('PI disabled');
                                    if ($pi === 'enabled') {
                                        $icon = 'fa-check text-success';
                                        $title = __('PI enabled');
                                    }
                                @endphp
                                <i class="fa {{ $icon }}" data-toggle="tooltip" title="{{ $title }}"></i>
                            </td>
                            <td>{{ $row['location'] }}</td>
                            <td>{{ $row['online'] }}</td>
                            <td>{{ $row['omega'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('No farm characters configured.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showIdle)
    <div class="card mb-4">
        <div class="card-header">
            <strong>Other Characters (Idle Monitor)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Training / Idle</th>
                            <th>Location</th>
                            <th>Last Online</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($idleRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>
                                @if($row['is_idle'])
                                    <span class="text-danger font-weight-bold">IDLE</span>
                                @else
                                    {{ $row['training_skill'] ?? 'unknown' }}
                                @endif
                            </td>
                            <td>{{ $row['location'] }}</td>
                            <td>{{ $row['online'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('No non‑farm characters.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Enable tooltips for plan descriptions and PI statuses
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush