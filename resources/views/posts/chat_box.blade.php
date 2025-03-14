@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <ul class="list-group" style="list-style: none">
                        @foreach ($users as $user)
                            <li>
                                <a href="/inbox/{{ $user->id }}" class="list-group-item">{{ $user->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        <form class="mb-3" action="{{ route('messages.store') }}" method="POST">
                            @csrf
                            <div class="mt-2">
                                <label>Write Message</label>
                                <input type="text" class="form-control" name="body" />
                            </div>
                            <input type="hidden" name="receiver" value="{{ $receiver->id }}">
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </form>

                        <div>
                            <div id="notification">
                                @foreach ($messages as $message)
                                    <div
                                        class="alert {{ $message->receiver == auth()->id() ? 'alert-primary' : 'alert-success' }}">
                                        {{ $message->body }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
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
            note.insertAdjacentHTML('afterbegin', `<div class="alert alert-primary">${e.message}</div>`);
        });
    </script>
@endsection
