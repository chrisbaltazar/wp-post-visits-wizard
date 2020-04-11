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
                    this.update(item, target);
                },
                error => {
                    alert((error.bodyText || error.body || error));
                }
            )
        },
        update(item, target) {
            let update = null;
            switch (target) {
                case 'types':
                    update = this.types.filter(type => type.id == item.id);
                    break;
                case 'categories':
                    update = this.categories.filter(cat => cat.id == item.id);
                    break;
                case 'tags':
                    update = this.tags.filter(tag => tag.id == item.id);
                    break;
            }

            update.active = ! item.active;

        }
    },
    created() {
    }
})