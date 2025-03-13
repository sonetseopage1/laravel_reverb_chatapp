@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">



                        <form class="mb-3" action="{{ route('posts.store') }}" method="POST">
                            @csrf
                            <div class="mt-2">
                                <label>Write Message</label>
                                <input type="text" class="form-control" name="body" />
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </form>

                        <div>
                            <div id="notification">
                                @foreach ($posts as $post)
                                    <div class="alert alert-success">{{ $post->body }}</div>
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
        window.Echo.channel("messages").listen(".create", (e) => {
            console.log(e);
            var note = document.getElementById("notification");
            note.insertAdjacentHTML('afterbegin', `<div class="alert alert-success">${e.message}</div>`);
        });
    </script>
@endsection
