let template2 =  `
        <v-sheet
            class="mx-auto pa-12 ma-12"
            elevation="0"
            color="grey lighten-5"
        >
            <v-layout row wrap align-center>
                <v-flex>
                    <v-card width="400" class="mx-auto" floating>


                        <v-card-title>
                            <h1 class="display-1 mx-auto font-weight-light">Login</h1>
                        </v-card-title>

                        <v-card-text>
                            <v-form  @submit.prevent="loginPost()" >
                                <v-text-field
                                    name="email"
                                    label="Email"
                                    id="email"
                                    v-model="loginDetails.email"
                                    prepend-icon="person"
                                    :rules="emailRules"
                                ></v-text-field>
                                <v-text-field
                                    name="password"
                                    label="Password"
                                    id="password"
                                    v-model="loginDetails.password"
                                    prepend-icon="lock"
                                    :append-icon="showPassword ? 'visibility' : 'visibility_off'"
                                    :type="showPassword ? 'text' : 'password'"
                                    @click:append="showPassword= !showPassword"
                                    :rules="passwordRules"

                                ></v-text-field>

                                <v-divider></v-divider>
                                <p v-if="loginDetailsError.error" class="text-danger mt-1 red--text lighten-1 text-center">{{ loginDetailsError.loginError }}</p>
                                <p v-if="loginDetailsValid.valid" class="text-success mt-1 green--text lighten-1 text-center">{{ loginDetailsValid.validMessage }}</p>

                                <v-card-actions>
                                    <!-- <v-btn color="info" text @click="gotorouterview('/register')">
                                        <v-icon>person_add</v-icon>
                                        Sign Up
                                    </v-btn> -->


                                    <v-spacer></v-spacer>
                                    <v-btn color="success" type="submit" text @click="loading=true">
                                        <v-icon>verified_user</v-icon>
                                        Sign In
                                    </v-btn>
                                </v-card-actions>
                            </v-form>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-sheet>

`;


Vue.component('login_c', {

        template: template2,
        mounted() {
            this.loading=false;
        },
        data() {
            return {
                showPassword:false,
                loginDetails : {  email: '', password: '' },
                loginDetailsError : { error:false, loginError: '' },
                loginDetailsValid : { valid:false, validMessage: '' },
                emailRules: [
                    v => !!v || 'E-mail is required',
                    v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
                ],
                passwordRules: [
                    v => !!v || 'Password is required',
                ],
                loading:true,
            }
        },
        methods: {
            loginPost(){
                    var _this = this;
                    axios.post('/saifur/log-viewer/login',  this.loginDetails)
                    .then(function (response) {
                        if (response?.data?.code==200)
                        {
                            _this.loading=false;
                            _this.loginDetailsError = { error:false, loginError: "" };
                            _this.loginDetailsValid = { valid:true, validMessage: 'Successful! Please wait..' }
                            _this.$emit('logindetailssetter', _this.loginDetails|| {})
                        }
                        else
                        {
                            _this.loginDetailsError = { error:true, loginError: "Invalid email or password" };
                            _this.loading=false;
                        }
                    })
                    .catch(function (error) {
                        _this.loginDetailsError = { error:true, loginError: "Invalid email or password" };
                        _this.loading=false;
                    })
            },
        },
        computed: {

        }
    });
