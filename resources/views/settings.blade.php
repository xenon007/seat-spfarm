@extends('web::layouts.app')

@section('content')
<div class="container-fluid my-4">
    <h1 class="mb-4">SP Farming Settings</h1>
    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    <form action="{{ route('seat-spfarm.settings.store') }}" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header"><strong>General</strong></div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="show_idle_table" name="show_idle_table" {{ $userSetting->show_idle_table ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_idle_table">
                        {{ __('Show non‑farm characters in idle monitor table') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Character Settings</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Is Farm</th>
                                <th>PI Enabled</th>
                                <th>Plan Text (optional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($characters as $character)
                                @php
                                    $charId = $character->character_id ?? $character->id;
                                    $entry = $entries[$charId] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $character->name ?? $character->character_name ?? __('Unknown') }}</td>
                                    <td>
                                        <input type="checkbox" name="characters[{{ $charId }}][is_farm]" {{ $entry && $entry->is_farm ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="characters[{{ $charId }}][pi_enabled]" {{ $entry && $entry->pi_enabled ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="characters[{{ $charId }}][plan_text]" value="{{ old('characters.'.$charId.'.plan_text', $entry->plan_text ?? '') }}" placeholder="Rigging Plan, etc...">
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ __('No characters found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
        </div>
    </form>
</div>
@endsection