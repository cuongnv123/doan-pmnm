@extends('front.layouts.app')

@section('content')
    <section class="container">
        <div class="col-md-12 text-center py-5">
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
            @endif

            <h1> {{ __('Thank You!') }}</h1>
            <p>{{ __('Your Order ID is') }}: {{ $id }}</p>
        </div>
    </section>
@endsection
