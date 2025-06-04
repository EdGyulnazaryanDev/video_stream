@props(['uid', 'name', 'token', 'app_id'])
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<div class="bg-gray-900 text-white p-6 min-h-screen">
    <h1 class="text-xl mb-4">Agora Video Call</h1>
    <h2>Username: {{ $name }} </h2>
    <div class="mb-4">
        <button id="startCall" class="bg-green-600 px-4 py-2 rounded mr-2">Start Call</button>
        <button id="leaveCall" class="bg-red-600 px-4 py-2 rounded">Leave Call</button>
    </div>

    <div class="mb-4">
        <button id="toggleMic" class="bg-blue-600 px-4 py-2 rounded mr-2">Toggle Mic</button>
        <button id="toggleCamera" class="bg-yellow-600 px-4 py-2 rounded">Toggle Camera</button>
    </div>

    <div id="video-container" class="grid grid-cols-2 gap-4 mt-4"></div>

    <div id="chat-container" class="grid grid-cols-2 gap-4 mt-4">

    </div>

{{--    <form id="message-form">--}}
{{--        <div id="chat-box" class="chat-box border p-2">--}}
{{--            <div id="chat-messages" class="h-40 overflow-y-auto mb-2 border p-2 bg-white text-gray-900"></div>--}}
{{--            <input type="text" id="chat-input" placeholder="Type a message..." class="w-full p-1 border text-gray-900" />--}}
{{--            <button type="submit" class="mt-2 bg-blue-500 text-white px-3 py-1">Send</button>--}}
{{--        </div>--}}
{{--    </form>--}}

    <div id="chat-box" style="border:1px solid #ccc; padding:10px; height:200px; overflow-y:scroll;">
        <!-- Messages will appear here -->
    </div>
    <form id="chat-form">
        <input type="text" id="message" placeholder="Type your message" required class="text-gray-900">
        <button type="submit">Send</button>
    </form>


    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

    <script>
        const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
        let localTracks = {
            videoTrack: null,
            audioTrack: null
        };
        let localUid = null;

        async function startCall() {
            const channelName = "testRoom";

            const res = await fetch(`/agora/token?channelName=${channelName}`);
            const data = await res.json();

            localUid = data.uid;

            await client.join(data.appId, channelName, data.token, localUid);

            localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();

            const player = `<div id="player-${localUid}" class="w-full h-60 bg-black"></div>`;
            document.getElementById("video-container").insertAdjacentHTML("beforeend", player);

            localTracks.videoTrack.play(`player-${localUid}`);

            await client.publish(Object.values(localTracks));
        }

        async function leaveCall() {
            for (let trackName in localTracks) {
                if (localTracks[trackName]) {
                    localTracks[trackName].stop();
                    localTracks[trackName].close();
                }
            }
            await client.leave();
            document.getElementById("video-container").innerHTML = "";
        }

        document.getElementById("startCall").onclick = startCall;
        document.getElementById("leaveCall").onclick = leaveCall;

        document.getElementById("toggleMic").onclick = () => {
            localTracks.audioTrack.setMuted(!localTracks.audioTrack.muted);
        };

        document.getElementById("toggleCamera").onclick = () => {
            localTracks.videoTrack.setMuted(!localTracks.videoTrack.muted);
        };

        client.on("user-published", async (user, mediaType) => {
            await client.subscribe(user, mediaType);
            if (mediaType === "video") {
                const player = `<div id="player-${user.uid}" class="w-full h-60 bg-black"></div>`;
                document.getElementById("video-container").insertAdjacentHTML("beforeend", player);
                user.videoTrack.play(`player-${user.uid}`);
            }
            if (mediaType === "audio") {
                user.audioTrack.play();
            }
        });

        client.on("user-unpublished", user => {
            document.getElementById(`player-${user.uid}`)?.remove();
        });
    </script>

    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script>

        // document.getElementById('chat-form').addEventListener('submit', function(e) {
        //     e.preventDefault();
        //     let message = document.getElementById('chat-input').value;
        //     axios.post('/send-message', { message });
        //     document.getElementById('chat-input').value = '';
        //
        // });
        // var pusher = new Pusher('6541cffdd36c25e50b25', {
        //     cluster: 'ap2',
        //     authEndpoint: '/broadcasting/auth',
        //     auth: {
        //         headers: {
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        //         }
        //     }
        // });
        //
        // const channel = pusher.subscribe('private-chat');
        // channel.bind('App\\Events\\MessageSent', function(data) {
        //     const box = document.getElementById('chat-box');
        //     box.innerHTML += `<p><strong>${data.user}:</strong> ${data.message}</p>`;
        //     box.scrollTop = box.scrollHeight;
        // });


        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let message = document.getElementById('message').value;
            axios.post('/send-message', { message });
            document.getElementById('message').value = '';
        });

        Pusher.logToConsole = true;
        const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }
        });

        const channel = pusher.subscribe('private-chat');
        channel.bind('MessageSent', function(data) {
            alert(1)
            const box = document.getElementById('chat-box');
            box.innerHTML += `<p><strong>${data.user}:</strong> ${data.message}</p>`;
            box.scrollTop = box.scrollHeight;
        });

    </script>
</div>
