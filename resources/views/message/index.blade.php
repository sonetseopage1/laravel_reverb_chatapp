@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                @include('layouts.inc.userList')
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body vh-50">
                        <h5 class="Text-center">Start Converstion</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="module">
        const userId = {{ auth()->user()->id }};

        window.Echo.channel("messages." + userId).listen(".create", (e) => {
            console.log(e);
            var note = document.getElementById("notification");
            note.insertAdjacentHTML('afterbegin', `<div class="alert alert-success">${e.message}</div>`);
        });
    </script>
@endsection
