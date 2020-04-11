new Vue({
    el: '#wp-post-visits-wizard-main',
    http: {
        emulateJSON: true,
        emulateHTTP: true
    },
    data: {
        cpt: pvwData.cpt,
        categories: pvwData.categories,
        tags: pvwData.tags,
    },
    filters: {},
    computed: {},
    methods: {},
    created() {
    }
})