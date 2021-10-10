const app = Vue.createApp({
    data() {
        return {
            connected: false,
            currentUser: {id: null, username: '', avatar: ''},
            message: '',
            loadedMessages: [],
            messages: [],
            onlineUsersCount: 0,
            socket: null,
            jwt: null,
            users: new Map(),
            newMessage: false,
        }
    },
    methods: {
        connect(jwt) {
            console.log('connect', jwt);
            this.jwt = jwt;
            const userRaw = this.jwt.split('.')[1];
            this.currentUser = JSON.parse(decodeURIComponent(escape(window.atob(userRaw))));

            this.socket = new WebSocket('wss://' + window.location.host + '?token=' + this.jwt);

            this.socket.addEventListener('open', (e) => {
                console.log(e);
                this.connected = true;
                this.socket.send(JSON.stringify({
                    event: 'join-channel',
                    data: { channel: 'C000000' }
                }));
                fetch('//' + window.location.host + '/me', {
                    method: 'GET',
                    cache: 'no-cache',
                    headers: {'Content-Type': 'application/json', 'x-auth-token': this.jwt}
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw Error(response.message || 'mmmm')
                        }
                        return response.json()
                    })
                    .then((data) => {
                        this.currentUser.username = data.username;
                        this.currentUser.id = data.id;
                        this.currentUser.avatar = data.avatar;
                    })
                    .catch((error) => {
                        this.$toast.error(error);
                        console.info('Error:', error);
                    });
            });

            this.socket.addEventListener('close', (e) => {
                this.connected = false;
                console.log(e);
            });

            this.socket.addEventListener('error', (e) => {
                console.log(e);
            });

            this.socket.addEventListener('message', (event) => {
                console.log(event);
                const eventMessage = JSON.parse(event.data);
                if (eventMessage.event === 'message') {
                    this.readMessage(eventMessage.data);
                }
                if (eventMessage.event === 'online-users') {
                    this.onlineUsersCount = eventMessage.data.count;
                }
                if (eventMessage.event === 'user-info') {
                    this.users.set(eventMessage.data.id, eventMessage.data);
                }
            });
        },
        readMessage(data) {
            if (this.loadedMessages.indexOf(data.id) >= 0) {
                return;
            }
            this.messages.push(data);
            this.loadedMessages.push(data.id);
            this.addUser(data.senderId);
            this.$nextTick(() => {
                console.log('next tick');
                const div = this.$refs.messages;
                div.scrollTop = div.scrollHeight - div.clientHeight;
            });
        },
        sendMessage() {
            this.socket.send(JSON.stringify({
                event: 'send-message',
                data: { message: this.message }
            }));
            this.message = '';
        },
        convertToLink: function (text) {
            const URLMatcher =
                /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/gim;
            return text.replace(
                URLMatcher,
                (text) =>
                    `<a class="text-primary" rel="noreferrer" target="_blank" href="${text}">${text}</a>`,
            );
        },
        createdAt(timestamp) {
            let date = new Date(timestamp);
            return date.getHours() + ':' + date.getMinutes();
        },
        scrolla() {
            console.log('new message');
            const div = this.$refs.messages;
            div.scrollTop = div.scrollHeight - div.clientHeight;
        },
        avatar(userId) {
            return 'https://avatars.dicebear.com/api/bottts/' + userId + '.svg'
        },
        username(userId) {
            if (!this.users.has(userId)) {
                return 'n/a';
            }

            return this.users.get(userId).username || 'n/a';
        },
        addUser(userId) {
            if (this.users.has(userId)) {
                return;
            }

            this.socket.send(JSON.stringify({
                event: 'user-info',
                data: { userId: userId }
            }));
        }
    }
});

app.use(VueToast);