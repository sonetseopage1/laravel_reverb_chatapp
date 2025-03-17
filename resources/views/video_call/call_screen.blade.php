@extends('layouts.app')

@section('content')
    <video id="localVideo" autoplay playsinline></video>
    <video id="remoteVideo" autoplay playsinline></video>
    <button id="startCall">Start Call</button>
@endsection

@push('script')
    <script>
        const userId = {{ auth()->user()->id }};
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        let peerConnection;
        let localStream;

        const signalingChannel = new BroadcastChannel('video-chat');

        async function startCall() {
            localStream = await navigator.mediaDevices.getUserMedia({
                // video: true,
                audio: true
            });
            localVideo.srcObject = localStream;

            peerConnection = new RTCPeerConnection({
                iceServers: [{
                    urls: 'stun:stun.l.google.com:19302'
                }]
            });

            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

            peerConnection.ontrack = event => {
                remoteVideo.srcObject = event.streams[0];
            };

            peerConnection.onicecandidate = event => {
                if (event.candidate) {
                    sendSignal({
                        type: 'candidate',
                        candidate: event.candidate
                    });
                }
            };

            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            sendSignal({
                type: 'offer',
                offer
            });
        }

        function sendSignal(data) {
            fetch('/video/signal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    data,
                    receiverId: 3
                }) // Replace USER_ID with actual ID
            });
        }

        // Listen for Laravel Reverb messages
        // Echo.channel('video-chat.USER_ID') // Replace with actual user ID
        //     .listen('.video.signal', (event) => {
        //         handleSignal(event.data);
        //     });

        window.Echo.channel('video-chat.' + 3)
            .listen('.video.signal', (e) => {
                handleSignal(e.data);
            });


        async function handleSignal(data) {

            console.log('Signal', data);

            if (data.type === 'offer') {
                peerConnection = new RTCPeerConnection({
                    iceServers: [{
                        urls: 'stun:stun.l.google.com:19302'
                    }]
                });

                peerConnection.ontrack = event => {
                    remoteVideo.srcObject = event.streams[0];
                };

                peerConnection.onicecandidate = event => {
                    if (event.candidate) {
                        sendSignal({
                            type: 'candidate',
                            candidate: event.candidate
                        });
                    }
                };

                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));

                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                sendSignal({
                    type: 'answer',
                    answer
                });

            } else if (data.type === 'answer') {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));

            } else if (data.type === 'candidate') {
                if (peerConnection) {
                    await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                }
            }
        }

        document.getElementById('startCall').addEventListener('click', startCall);
    </script>
@endpush
