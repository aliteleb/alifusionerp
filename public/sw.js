// Service Worker for Browser Push Notifications

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};

    if (event.data) {
        try {
            data = event.data.json();
        } catch (error) {
            data = {
                title: 'New Notification',
                body: event.data.text(),
            };
        }
    }

    const title = data.title || 'Ali Fusion ERP';
    let notificationUrl = '/';

    if (data.data && data.data.url) {
        notificationUrl = data.data.url;
    } else if (data.url) {
        notificationUrl = data.url;
    }

    const options = {
        body: data.body || 'You have a new notification',
        icon: data.icon || '/images/logo.png',
        badge: data.badge || '/images/logo.png',
        tag: data.tag || `notification-${Date.now()}`,
        data: {
            url: notificationUrl,
            notificationId: data.id,
        },
        requireInteraction: true,
        vibrate: [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification(title, options),
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        }).then(function (clientList) {
            for (const client of clientList) {
                try {
                    if (client.url.includes(new URL(urlToOpen).pathname) && 'focus' in client) {
                        return client.focus();
                    }
                } catch (error) {
                    // ignore malformed URL parsing
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        }),
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(clients.claim());
});

