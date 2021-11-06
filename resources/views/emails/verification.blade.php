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
            <p>Hello {{$data['user_name'] ?? ''}}</p>
            <h4>Your more productive future awaits</h4>
            <div class="mt-4">
                <p class="mt-5">Before you get started, we just need to verify your account using below code.</p>
                <h1 class="p-6 bg-gray-100"> {{$data['otp']}}</h1>
            </div>
        </div>
    </section>
@endsection