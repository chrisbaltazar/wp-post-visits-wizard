new Vue({
    el: '#wp-post-visits-wizard-main',
    http: {
        emulateJSON: true,
        emulateHTTP: true
    },
    data: {
        types: pvwData.types,
        categories: pvwData.categories,
        tags: pvwData.tags,
        endpoint: pvwEndpoint
    },
    filters: {},
    computed: {},
    methods: {
        save(item, target) {

            const data = {
                'target': target,
                'id': item.id,
                'stat': item.active
            }

            this.$http.post(this.endpoint.save, data).then(
                response => {

                },
                error => {
                    alert((error.bodyText || error.body || error));
                }
            )
        }
    },
    created() {
    }
})