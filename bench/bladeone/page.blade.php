@extends('layout')

@section('body')
<h1>{{ $title }}</h1>

<ul>
    @foreach ($array as $item)
    <li>{{ $item }}</li>
    @endforeach
</ul>

{!! $htmlval !!}

@include('insert')
@endsection

@section('script')
<script>
    console.log('templates');
</script>
@endsection
