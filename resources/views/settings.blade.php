@extends('statamic::layout')

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">GraphQL Thumbnails</h1>
    </div>

    <div>
        <publish-form
            title="Settings"
            action="{{ cp_route('legrisch.gql-thumbnails.update') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
        />
    </div>
@stop