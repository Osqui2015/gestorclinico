import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echo = null;

if (typeof window !== 'undefined') {
    window.Pusher = Pusher;

    const appKey = import.meta.env.VITE_REVERB_APP_KEY;
    const host = import.meta.env.VITE_REVERB_HOST ?? 'localhost';
    const port = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

    if (appKey) {
        echo = new Echo({
            broadcaster: 'reverb',
            key: appKey,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo = echo;
    } else {
        console.warn('Echo/Reverb no configurado. La app seguirá sin tiempo real.');
    }
}

export { echo };
export default echo;