app.component('login', {
    props: ['connected'],
    data() {
        return {
            loginVisible: true,
            loginUser: {username: null, password: null},
            jwt: null,
        };
    },
    methods: {
        getToken() {
            fetch('//' + window.location.host + '/sessions', {
                method: 'POST',
                cache: 'no-cache',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(this.loginUser)
            })
                .then((response) => {
                    if (!response.ok) {
                        throw Error(response.message || 'mmmm')
                    }
                    return response.json()
                })
                .then((data) => {
                    this.jwt = data.token;
                    this.$emit('loggedIn', this.jwt);
                })
                .catch((error) => {
                    this.$toast.error(error);
                    console.info('Error:', error);
                });
        },
        showSignUp() {
            this.loginVisible = false;
        },
        showLogin() {
            this.loginVisible = true;
        },
        registered() {
            this.loginVisible = true;
            this.loginUser = {username: null, password: null};
        },
    },
    template:
        `<div>
         <div v-if="loginVisible">
            <div class="box" style="max-width: 500px">
                <div class="title">Login</div>
                <div class="field">
                    <label class="label" for="username">Please enter your username</label>
                    <div class="control is-expanded has-icons-left">
                        <input class="input is-medium" type="text" placeholder="e.g. Oppalalà"
                               v-model="loginUser.username" id="username" autocomplete="off"/>
                        <span class="icon is-medium is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                    <p class="help is-danger"></p>
                </div>
                <div class="field">
                    <label class="label" for="password">Enter your password</label>
                    <div class="control is-expanded has-icons-left">
                        <input class="input is-medium" type="password" placeholder="e.g. V3ry $tronG Passw0rd!!!"
                               v-model="loginUser.password" id="password"/>
                        <span class="icon is-medium is-left">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                    <p class="help is-danger"></p>
                </div>
                <div class="field">
                    <div class="control">
                        <button class="button is-medium is-primary" @click="getToken">
                            Join chat
                        </button>
                    </div>
                </div>
            </div>
            <div  class="box" style="max-width: 500px">
                <div class="field">
                    Don't have account? <a href="#" @click="showSignUp">Signup now</a>
                </div>
            </div>
        </div>
        <div v-else>
            <signup @registered="registered" @showLogin="showLogin"></signup>        
        </div>
    </div>`
})