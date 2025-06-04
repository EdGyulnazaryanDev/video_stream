@props(['uid', 'name', 'token', 'app_id', 'userId', 'userName'])
<style>
    .chat-container {
        max-width: 600px;
        margin: 0 auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        font-family: Arial, sans-serif;
    }

    .message-box {
        height: 400px;
        overflow-y: auto;
        padding: 15px;
        background: #f9f9f9;
    }

    .message {
        margin-bottom: 15px;
        padding: 10px 15px;
        border-radius: 18px;
        max-width: 70%;
        word-wrap: break-word;
    }

    .message.sent {
        background: #007bff;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 0;
    }

    .message.received {
        background: #e9ecef;
        margin-right: auto;
        border-bottom-left-radius: 0;
    }

    .message-input {
        display: flex;
        padding: 10px;
        background: #fff;
        border-top: 1px solid #ddd;
    }

    .message-input input {
        flex-grow: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
    }

    .message-input button {
        margin-left: 10px;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
    }

    .message-input button:hover {
        background: #0056b3;
    }
</style>
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

    <div class="mt-8 border rounded-lg overflow-hidden max-w-lg mx-auto">
        <div class="p-4 bg-gray-800 text-white">
            <h3 class="text-lg font-semibold">Chat</h3>
            <div id="connection-status" class="text-sm">Status: connecting...</div>
        </div>

        <div id="message-box" class="h-64 overflow-y-auto p-4 bg-gray-100">
        </div>

        <div class="flex p-4 bg-white border-t">
            <input type="text" id="message" placeholder="Type your message..."
                   class="flex-1 px-4 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900">
            <button id="send_btn" class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700">Send</button>
        </div>
    </div>

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
            console.log('Mic toggled')
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


    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        // Initialize Pusher
        const pusher = new Pusher('6541cffdd36c25e50b25', {
            cluster: 'ap2',
            forceTLS: true
        });

        // Connection status
        pusher.connection.bind('state_change', (states) => {
            const statusEl = document.getElementById('connection-status');
            statusEl.textContent = `Status: ${states.current}`;
            statusEl.style.color = states.current === 'connected' ? 'green' : 'red';
        });

        const channel = pusher.subscribe('stream_channel');

        // Display message function
        function displayMessage(data, isSent = false) {
            const messageBox = document.getElementById('message-box');
            const messageElement = document.createElement('div');

            // Tailwind classes for message styling
            messageElement.className = `mb-3 p-3 rounded-lg max-w-xs ${isSent ?
                'ml-auto bg-blue-600 text-white rounded-br-none' :
                'mr-auto bg-gray-300 text-gray-900 rounded-bl-none'}`;

            messageElement.innerHTML = `
            <div class="font-semibold">${data.user?.name || 'User'}</div>
            <div class="text-sm">${data.message}</div>
            <div class="text-xs opacity-70 mt-1">${new Date().toLocaleTimeString()}</div>
        `;

            messageBox.appendChild(messageElement);
            messageBox.scrollTop = messageBox.scrollHeight;
        }

        // Send message handler
        document.getElementById('send_btn').addEventListener('click', function(e) {
            e.preventDefault();
            const messageInput = document.getElementById('message');
            const message = messageInput.value.trim();

            if (!message) return;

            axios.post('/send-message', { message })
                .then(() => {
                    // displayMessage({
                    //     user: { name: 'You' },
                    //     message: message
                    // }, true);
                    messageInput.value = '';
                })
                .catch(error => {
                    console.error('Error:', error.response?.data || error.message);
                });
        });

        // Receive messages from Pusher
        channel.bind('stream_event', function(data) {
            displayMessage(data);
        });

        // Typing indicator (optional)
        document.getElementById('message').addEventListener('input', _.debounce(() => {
            channel.trigger('client-typing', {
                userId: @json($userId),
                isTyping: true
            });
        }, 300));

        channel.bind('client-typing', (data) => {
            console.log(`${data.userId} is typing...`);
            // You can add a typing indicator UI here if needed
        });

    </script>

</div>
