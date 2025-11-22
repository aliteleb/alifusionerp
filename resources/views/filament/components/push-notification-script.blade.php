<script>
document.addEventListener('DOMContentLoaded', () => {
    const isAdminPanel = /^\/admin(\/|$)/.test(window.location.pathname);

    if (!isAdminPanel) {
        return;
    }

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || Notification.permission === 'denied') {
        return;
    }

    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
        return;
    }

    navigator.serviceWorker.register('/sw.js', { scope: '/admin/' })
        .then(() => navigator.serviceWorker.ready)
        .then((registration) => Notification.requestPermission().then((permission) => ({ permission, registration })))
        .then(({ permission, registration }) => {
            if (permission === 'granted') {
                subscribeUserToPush(registration);
            }
        })
        .catch(() => {});
});

function subscribeUserToPush(registration) {
    const publicKey = '{{ config('webpush.vapid.public_key') }}';

    if (!publicKey) {
        return;
    }

    registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
    }).then(savePushSubscription).catch(() => {});
}

function savePushSubscription(subscription) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        ?? document.querySelector('input[name="_token"]')?.value
        ?? '';

    fetch('/api/push-subscriptions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'include',
        body: JSON.stringify(subscription),
    }).catch(() => {});
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}
</script>

