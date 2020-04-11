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
    },
    filters: {},
    computed: {},
    methods: {},
    created() {
    }
})