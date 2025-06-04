import axios from 'axios';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;


window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    wsHost: `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: 80,
    wssPort: 443,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});


window.Echo.private('chat')
    .listen('MessageSent', (data) => {
        alert(22)
        const box = document.getElementById('chat-box');
        box.innerHTML += `<p><strong>${data.user}:</strong> ${data.message}</p>`;
        box.scrollTop = box.scrollHeight;
    });

