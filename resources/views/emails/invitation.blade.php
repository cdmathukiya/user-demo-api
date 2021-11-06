@extends('layouts.mail')

<!-- Page Title -->
@section('title') Contact Request To DES-CANADA @endsection

<!-- Page Specific Css Start -->
@section('styles')
@endsection
<!-- Page Specific Css End -->

@section('content')
    <section>
        <div class="text-center">
            <p>Hello Sir/Mam</p>

            <p class="mt-5">You are inviting to register our portal</p>
            <a class="btn" href="{{$data['invitationLink']}}">Register</a>
        </div>
    </section>
@endsection