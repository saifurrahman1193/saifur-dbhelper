
let template =  `
        <v-dialog v-model="log_dialog" persistent fullscreen scrollable @keydown.esc="close_log" >
            <v-card>

                <v-card-title>
                    <v-spacer></v-spacer>
                    <span class="headline">{{log_file_data?.file_name}}</span>
                    <v-chip small color="primary" class="ml-2">{{ log_info?.day }}</v-chip>
                    <v-spacer></v-spacer>
                    <v-btn text @click="close_log"><v-icon color="pink">cancel</v-icon></v-btn>
                </v-card-title>

                <v-card-text>


                    <v-row class="mb-2">
                        <v-tooltip top >
                            <template v-slot:activator="{ on }">
                                <v-btn  text v-on="on" style="text-decoration:none;" icon class="ml-2 mt-2" @click="file_data_loading(log_file_data?.file_path)">
                                    <v-icon color="primary" v-on="on">cached</v-icon>
                                </v-btn>
                            </template>
                            <span>Refresh log data</span>
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

                    <v-data-table :headers="headers" :items="log_lines" :search="search"
                        :sort-by="['datetime']"
                        :sort-desc="[true]"
                        :footer-props="{ 'items-per-page-options': [100, 500, -1] }">
                        <template v-slot:item="{item, index}">
                            <tr>
                                <td>
                                    <span v-text="index+1"></span>
                                </td>
                                <td>
                                    <v-chip small color="light">{{ item?.datetime?.split(" ")?.[1] }}</v-chip>
                                </td>
                                <td>
                                    <v-chip outlined x-small :color="item?.environment =='production' ? 'green' : ''" >{{ item?.environment }}</v-chip>
                                    <v-chip outlined x-small :color="getLevelData(item?.level)?.color" >
                                        {{ item?.level }}
                                    </v-chip>

                                    <v-chip color="pink" outlined  @click="log_line_details_show_handle(index)" v-if="log_line_details_show?.includes(index)" small
                                        class="mt-2"
                                    >
                                        <v-icon  color="pink" x-small>unfold_less</v-icon>
                                        <span>Collapse</span>
                                    </v-chip>
                                </td>
                                <td>
                                    <span v-text="item?.log" @click="item?.details?.length ? log_line_details_show_handle(index) : null" :class="item?.details?.length ? 'pointer' : ''"
                                        style="font-family: consolas, Menlo, Courier, monospace; font-size: 0.8rem;"
                                    ></span>

                                    <v-chip color="primary" outlined  @click="log_line_details_show_handle(index)" v-if="item?.details?.length && !log_line_details_show?.includes(index)" x-small>
                                        <v-icon  color="primary" x-small>unfold_more</v-icon> &nbsp;
                                        <span>Expand</span>
                                    </v-chip>

                                    <v-simple-table  dense v-if="log_line_details_show?.includes(index)" >
                                        <template v-slot:default >
                                            <tbody>
                                                <tr v-for="(detail, j) in item?.details" :key="index+'-'+j">
                                                    <td>
                                                        <span style="color:#AE0E0E; font-family: consolas, Menlo, Courier, monospace; font-size: 11px;">
                                                            {{detail}}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </template>
                                    </v-simple-table>
                                </td>
                            </tr>
                        </template>

                    </v-data-table>



                </v-card-text>

            </v-card>
        </v-dialog>
`;


Vue.component('single_log_file_c', {
    props:[
        'log_file', 'login_details'
    ],
    data() {
        return {
            log_file_data_initial: {
                log_dialog: false,
                file_name: '',
                file_path: '',
            },
            log_file_data: this.log_file_data_initial,
            log_dialog: false,
            log_lines:[],
            log_info:{},
            log_line_details_show:[],
            isLoading: false,
            headers: [
                {
                    text: 'Id',
                    sortable: false,
                },
                {
                    text: 'Time',
                    value: 'datetime',
                },
                {
                    text: 'Info',
                    sortable: false,
                },
                {
                    text: 'Log',
                    value: 'log',
                    sortable: false,
                },
            ],
            search: '',
            level: [ 'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'success' ]

        }
    },
    mixins:[],
    components: {  },
    template: template,
    methods: {

        file_data_loading(file_path='') {
            var _this = this
            this.isLoading = true

            axios.post('/saifur/log-viewer/single-log-file-data', {file_path:file_path, ...this.login_details})
                .then(function(response) {
                    _this.isLoading = false
                    _this.log_lines = response?.data?.data?.data
                    _this.log_info = response?.data?.data?.info
                })
                .catch(function(error) {
                    _this.log_lines = []
                    _this.isLoading = false
                })
        },

        close_log() {
            this.log_lines = []
            this.log_file_data= this.log_file_data_initial
            this.log_dialog = false
            this.log_line_details_show=[]
        },
        log_line_details_show_handle(index){
            console.log(index);
            if (this.log_line_details_show?.includes(index)) {
                this.log_line_details_show = this.log_line_details_show?.filter((value)=>value!=index);
            }
            else{
                this.log_line_details_show = [...this.log_line_details_show, index]
            }
        },
        getLevelData(level=''){
            let data = {color:'', icon:''};
            switch (level?.toLowerCase()) {
                case 'success':
                    data = {color:'success', icon:'dinosaur-pixel'};
                    break;
                case 'debug':
                    data = {color:'orange', icon:'dinosaur-pixel'};
                    break;
                case 'error':
                    data = {color:'pink', icon:'dinosaur-pixel'};
                    break;
                default:
                    break;
            }
            return data;
        }

    },
    computed: {

    },
    watch:{
        log_file(data){
            this.log_file_data= data
            this.log_dialog = data?.log_dialog
            this.file_data_loading(data?.file_path)
        }
    }
});












