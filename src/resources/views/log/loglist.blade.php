<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saifur Log Viewer</title>

    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    @if (preg_match('/127.0/', Request::ip()))
        <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    @else
        <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.min.js"></script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="{{ asset('vendor/saifur/logviewer/assets/js/vue/axios.min.js') }}"></script>

    <script src="{{ asset('vendor/saifur/logviewer/assets/js/vue/mixins/common.js') }}"></script>
    <script src="{{ asset('vendor/saifur/logviewer/assets/js/vue/components/singlelogfile.js') }}"></script>
    <script src="{{ asset('vendor/saifur/logviewer/assets/js/vue/components/login.js') }}"></script>
</head>

<body>

    <div class="content-wrapper" style="min-height: 0px;" id="app">

        <v-app style="background-color: #fafafa !important;">
            <v-sheet class=" ma-2" elevation="0" color="grey lighten-5">

                <v-main>

                    <v-card elevation="0" v-if="!authorization_required || (authorization_required && authorized)">
                        <v-card-title primary-title>
                            <v-spacer></v-spacer>
                            Log List Management
                            <v-spacer></v-spacer>
                        </v-card-title>

                        <v-card-text>
                            <v-row class="mb-2">

                                <v-col  v-if="isSelected" cols="auto">
                                    <v-chip color="pink" outlined
                                        @click="removeFiles(selected)"
                                    >
                                        <v-icon  color="pink" >delete</v-icon> &nbsp;
                                        <span v-text="`Delete ${selected?.length} Selected Files`"></span>
                                    </v-chip>
                                </v-col>

                                <v-tooltip top >
                                    <template v-slot:activator="{ on }">
                                        <v-btn  text v-on="on" style="text-decoration:none;" icon class="ml-2 mt-2" @click="logFilesListData">
                                            <v-icon color="primary" v-on="on">cached</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Refresh log list</span>
                                </v-tooltip>

                                <v-spacer></v-spacer>
                                <v-text-field
                                    v-model="search"
                                    append-icon="mdi-magnify"
                                    label="Search"
                                    single-line
                                    hide-details
                                ></v-text-field>

                            </v-row>


                            <v-toolbar-title class="subtitle text-center mx-auto" v-if="isLoading">
                                <v-spacer /><v-progress-circular indeterminate color="primary" size="70" class="mx-auto mt-6" ></v-progress-circular><v-spacer />
                            </v-toolbar-title>


                            <v-data-table :headers="headers" :items="files" :search="search"
                                :footer-props="{ 'items-per-page-options': [10, 50, 500, -1] }">
                                <template v-slot:item="{item, index}">
                                    <tr>
                                        <td>
                                            <v-checkbox @click="addRemoveCheckBoxSelectItem(item, index)" :id="`select-${index+1}`"></v-checkbox>
                                        </td>
                                        <td>
                                            <span v-text="index+1"></span>
                                        </td>
                                        <td>

                                            {{-- single log file view --}}
                                            <v-tooltip top >
                                                <template v-slot:activator="{ on }">
                                                    <v-btn  text v-on="on" style="text-decoration:none;" icon
                                                    @click="viewLogFile(item?.file_name, item?.file_path)"
                                                    >
                                                        <v-icon color="light-blue" v-on="on">preview</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>View Log?</span>
                                            </v-tooltip>


                                            {{-- download --}}
                                            <v-tooltip top>
                                                <template v-slot:activator="{ on }">
                                                    <v-btn text v-on="on" style="text-decoration:none;" icon
                                                        @click="downloadLogFile(item?.file_path, item?.file_name)"
                                                        >
                                                        <v-icon color="success darken-1" v-on="on">save_alt
                                                        </v-icon>
                                                    </v-btn>
                                                </template>
                                                <span v-text="`Download`"></span>
                                            </v-tooltip>

                                            {{-- single delete --}}
                                            <v-tooltip top >
                                                <template v-slot:activator="{ on }">
                                                    <v-btn  text v-on="on" style="text-decoration:none;" icon
                                                    @click="deleteLogFile(item?.file_path)">
                                                        <v-icon color="pink" v-on="on">delete</v-icon>
                                                    </v-btn>
                                                </template>
                                                <span>Delete File?</span>
                                            </v-tooltip>





                                        </td>
                                        <td>
                                            <span v-text="item?.file_name"></span> <br>
                                            <v-chip color="red" outlined  small
                                                v-text="`Memory Limit ${getFileSizeKBMB(info?.memory_limit)} Exceeded`"
                                                v-if="item?.memory_limit_exceeds"
                                            ></v-chip>
                                        </td>
                                        <td>
                                            <span v-text="item?.date"></span>
                                        </td>
                                        <td>
                                            <span v-text="getFileSizeKBMB(item?.size)"></span>

                                        </td>
                                    </tr>
                                </template>

                            </v-data-table>




                        </v-card-text>
                    </v-card>
                    <login_c @logindetailssetter="logindetailssetter" v-if="authorization_required && !authorized" />

                </v-main>

            </v-sheet>

            <single_log_file_c :log_file="log_file" :login_details="login_details"/>

        </v-app>
    </div>


    <script>
        var _this = this
        var app = new Vue({
            vuetify: new Vuetify(),
            el: '#app',
            mixins: [commonMixin],
            components: {  },
            mounted() {
                this.authorizationHandler()
            },
            data: {
                info: {},
                files: [],
                search: '',
                selected: [],
                log_file: {},
                isLoading: false,
                authorization_required: {{config('app.saifur_logviewer_middleware')}} || 0,
                authorized: 0,
                login_details:{},
                headers: [
                    {
                        text: 'Select',
                        value: 'select',
                        width: '90px',
                        sortable: false,
                    },
                    {
                        text: 'Id',
                        value: 'index',
                        width: '70px',
                        sortable: false,
                    },
                    {
                        text: 'Action',
                        value: 'action',
                        sortable: false,
                    },
                    {
                        text: 'File',
                        value: 'file_name'
                    },
                    {
                        text: 'Date',
                        value: 'date'
                    },
                    {
                        text: 'Size',
                        value: 'size'
                    },
                ],
            },
            methods: {
                authorizationHandler(){
                    if (this.authorization_required==0)
                    {
                        this.logFilesListData();
                    }
                },
                logFilesListData() {
                    this.selected=[]
                    this.isLoading=true
                    this.files=[]
                    var _this = this

                    axios.post('/saifur/log-viewer/log-files-list-data', {...this.login_details})
                        .then(function(response) {
                            _this.files = response?.data?.data?.files
                            _this.info = response?.data?.data?.info
                            _this.isLoading=false
                        })
                        .catch(function(error) {
                            _this.files = []
                            _this.isLoading=false
                        })
                },
                downloadLogFile(file_path = '', file_name = '') {
                    var _this = this;
                    axios.post('/saifur/log-viewer/log-file-download', {
                            'file_path': file_path,
                            ...this.login_details
                        })
                        .then((response) => {
                            let data = (JSON.stringify(response)).replace(/\\n/g,'\r\n');
                            const blob = new Blob ( [data], { type: "octet-stream" });
                            const href = URL.createObjectURL(blob);
                            const a = Object.assign(document.createElement("a"), {
                                href,
                                style: "display:none",
                                download: file_name,
                            });
                            document.body.appendChild(a);
                            a.click();
                            URL.revokeObjectURL(href);
                            a.remove();
                        })
                        .catch((error) => {})
                },
                deleteLogFile(file_path = '') {
                    if(confirm("Do you really want to delete?"))
                    {
                        var _this = this;
                        axios.post('/saifur/log-viewer/log-file-delete', {
                                'file_path': file_path,
                                ...this.login_details
                            })
                            .then((response) => {
                                _this.files=[]
                                _this.logFilesListData()
                            })
                            .catch((error) => {
                                console.log(error);
                            })
                    }
                },
                addRemoveCheckBoxSelectItem(item={}, index=0){
                    if (this.selected.includes(item.file_path)) {
                        this.selected= (this.selected).filter((i)=>{
                            return i != item.file_path
                        })
                    }
                    else{
                        this.selected.push(item.file_path)
                    }
                },
                removeFiles(file_paths=[]){
                    var length = file_paths.length

                    for (let i = 0; i < length; i++) {
                        file_paths[i] = file_paths[i];
                    }

                    if (length>0) {
                        if (confirm("Do you really want to delete selected files?")) {
                            var _this = this;
                            axios.post('/saifur/log-viewer/log-file-delete-multiple',
                                {
                                    'file_paths' : file_paths,
                                    ...this.login_details
                                }
                            )
                            .then(function (response) {
                                _this.logFilesListData()
                                _this.selected=[]
                            })
                            .catch(function (error) {
                            })
                        }
                    }

                },
                viewLogFile(file_name='', file_path=''){
                    this.log_file = {
                        log_dialog: true,
                        file_name: file_name,
                        file_path: file_path,
                    }
                },
                logindetailssetter(login_data){
                    this.login_details={...login_data}
                    this.authorized=1
                    this.logFilesListData();
                },
            },
            computed: {
                isSelected:function(){
                    return this.selected.length>0
                },
            },
            watch: {
            },
        })
    </script>
    <style>
        .pointer {cursor: pointer;}
    </style>
</body>
</html>
