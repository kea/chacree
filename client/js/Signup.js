app.component('signup', {
    data() {
        return {
            user: {username: null, password: null}
        };
    },
    methods: {
        signUp() {
            fetch('//' + window.location.host + '/users', {
                method: 'POST',
                cache: 'no-cache',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(this.user)
            })
                .then((response) => {
                    if (!response.ok) {
                        throw Error(response.message || 'mmmm')
                    }
                    return response.json()
                })
                .then((data) => {
                    this.$emit('registered');
                    this.$toast.success("Hooray!!! Registration complete, you can now log in!");
                })
                .catch((error) => {
                    this.$toast.error(error);
                    console.info('Error:', error);
                });
        },
        showLogin() {
            this.$emit('showLogin');
        },
    },
    template:
        `<div>
        <div class="box" style="max-width: 500px">
            <div class="title">Signup!</div>
            <div class="field">
                <label class="label" for="username">Please enter your username</label>
                <div class="control is-expanded has-icons-left">
                    <input class="input is-medium" type="text" placeholder="e.g. Oppalalà"
                           v-model="user.username" id="username" autocomplete="off"/>
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
                           v-model="user.password" id="password"/>
                    <span class="icon is-medium is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
                <p class="help is-danger"></p>
            </div>
            <div class="field">
                <div class="control">
                    <button class="button is-medium is-link" @click="signUp">
                        Sign up
                    </button>
                </div>
            </div>
        </div>
        <div  class="box" style="max-width: 500px">
            <div class="field">
                Already have account? <a href="#" @click="showLogin">Login</a>
            </div>
        </div>
    </div>`
})