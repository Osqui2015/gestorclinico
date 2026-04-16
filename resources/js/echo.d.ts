import type Echo from 'laravel-echo';

declare const echo: Echo | null;

export { echo };
export default echo;

declare global {
    interface Window {
        Pusher: typeof import('pusher-js');
        Echo: Echo | null;
    }
}