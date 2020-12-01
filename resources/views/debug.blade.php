@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Debug') }}</div>

                    <div class="card-body">
                        <ol>
                            @forelse($output as $item)
                                <li>{{ $item }}</li>
                            @empty
                                {{ 'nothing to show' }}
                            @endforelse
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
