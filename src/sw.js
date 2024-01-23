self.addEventListener("push", (event) => {
    var json = event.data.json();

    switch(json['type']) {
        case 'msg':
            // Notify user about personal messages
            self.registration.showNotification(json['userName'], {
                body: json['content'],
                icon: '/favicon.ico',
                data: json
            });
            break;
        case 'cupdate':
            // Notifications when subscribed course is updated
            self.registration.showNotification('Course updated!', {
                body: 'New ' + json['utype'] + ' in "' + json['name'] + '"',
                icon: '/favicon.ico',
                data: json
            });
            break;
        case 'general':
            self.registration.showNotification(json['title'], {
                body: json['content']
            });
            break;
    }
 });

 self.addEventListener("notificationclick", (event) => {
    event.waitUntil(async function () {
        const allClients = await clients.matchAll({
            includeUncontrolled: true
        });
        let chatClient;
        let appUrl;

        switch(event.notification.data['type']) {

            case 'msg':
                appUrl = '/messages/chat?i=' + event.notification.data['chatID'];
                break;
            case 'cupdate':
                appUrl = event.notification.data['url'];

        }
        
        for (const client of allClients) {
        //here appUrl is the application url, we are checking it application tab is open
            if(client['url'].indexOf(appUrl) >= 0) 
            {
                client.focus();
                chatClient = client;
                break;
            }
        }
        if (!chatClient) {
            chatClient = await clients.openWindow(appUrl);
        }
    }());
});