@extends('base')

@section('head')

@endsection

@section('footer')

    @if (Request::get('verified'))
    <script>
    window.addEventListener('load', function() {
    toastr["success"]("E-mail validado com sucesso!");
    })
    </script>
    @endif
    
@endsection

@section('content')


@endsection
