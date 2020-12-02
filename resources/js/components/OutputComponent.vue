<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div v-if="hasError">
                    <div class="alert alert-dismissible alert-danger">
                        <p>
                            <strong>Error! </strong><span v-text="error.message"></span>
                        </p>
                        <p>
                            occurred in <span v-text="error.file "></span> file on line <span
                            v-text="error.line"></span>
                        </p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="">
                            <span>Reactive X output</span>
                        </div>
                        <div class="">
                            <button @click="clear" class="btn btn-secondary mx-1">CLEAR</button>
                            <button @click="run" class="btn btn-outline-danger mx-1">UPDATE</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <ol>
                            <li v-for="item in stack">{{ item }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            Echo.channel('output').listen('.data', (data) => {
                this.stack.push(data['data']);
            });
        },
        data: () => {
            return {
                stack: [],
                error: {
                    message: null,
                    line: null,
                    file: null
                },
                hasError: false
            }
        },
        methods: {
            'run': function () {
                this.stack = [];
                window.axios.post('/run', {}).then(() => {
                    this.hasError = false;
                }).catch((e) => {
                    this.error['message'] = e.response.data.message;
                    this.error['line'] = e.response.data.line;
                    this.error['file'] = e.response.data.file;
                    this.hasError = true;
                });
            },
            'clear': function () {
                this.stack = [];
                this.hasError = false;
            }
        }
    }
</script>
