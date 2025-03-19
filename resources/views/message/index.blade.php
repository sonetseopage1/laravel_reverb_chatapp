@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                @include('layouts.inc.userList')
            </div>

            <div class="col-md-8">

                <div class="card" id="inbox">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body vh-50">
                        <h5 class="Text-center">Start Converstion</h5>
                    </div>
                </div>

                <div class="row">
                    <video class="col-md-6" id="localVideo" autoplay playsinline></video>
                    <video class="col-md-6" id="remoteVideo" autoplay playsinline></video>
                </div>

            </div>
        </div>
    </div>
@endsection
