export default function notificationsBell(config) {
    return {
        unreadCount: 0,
        hover: false,
        init() {
            this.fetchCount();

            const bindEvents = () => {
                console.log('NativePHP detected (notificationsBell). Listening for events...');

                Native.on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (e) => {
                    console.log('Notification received:', e);
                    this.unreadCount++;
                });

                Native.on('NotificationsMarkedAsRead', () => {
                    this.unreadCount = 0;
                });

                Native.on('BackupCreated', () => {
                    console.log('BackupCreated event received');
                    this.fetchCount();
                });

                Native.on('App\\Events\\BackupCreated', () => {
                    console.log('App\\Events\\BackupCreated event received');
                    this.fetchCount();
                });
            };

            if (window.Native) {
                bindEvents();
            } else {
                window.addEventListener('native:init', bindEvents);
            }
        },
        fetchCount() {
            fetch(config.feedUrl)
                .then(response => response.json())
                .then(data => {
                    this.unreadCount = data.meta ? data.meta.unread_count : 0;
                })
                .catch(error => {
                    console.error('Error fetching notifications count:', error);
                });
        }
    }
}
